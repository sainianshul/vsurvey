from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.responses import JSONResponse
import wave
import json
import os
import tempfile
import urllib.request
import zipfile
from vosk import Model, SpkModel, KaldiRecognizer
from pydub import AudioSegment
from groq import Groq
import numpy as np
from sklearn.cluster import KMeans

app = FastAPI(title="VSurvey Speech Service", version="1.0.0")

# Initialize Groq client
groq_api_key = os.environ.get("GROQ_API_KEY", "")
groq_client = Groq(api_key=groq_api_key) if groq_api_key else None

# List of free models on Groq to use for fallback (Maximizing limits)
GROQ_MODELS = [
    "openai/gpt-oss-20b", 
    "qwen/qwen3.8-27b", 
    "allam-2-7b"
]
STATE_FILE = "active_model.json"

def get_active_model_index() -> int:
    if os.path.exists(STATE_FILE):
        try:
            with open(STATE_FILE, "r") as f:
                data = json.load(f)
                return data.get("current_index", 0)
        except:
            return 0
    return 0

def update_active_model_index(index: int):
    try:
        with open(STATE_FILE, "w") as f:
            json.dump({"current_index": index}, f)
    except Exception as e:
        print("Failed to save state:", e)

# ─── Configuration ────────────────────────────────
MODEL_URL = "https://alphacephei.com/vosk/models/vosk-model-small-hi-0.22.zip"
MODEL_DIR = "/app/models/vosk-model-small-hi-0.22"

SPK_MODEL_URL = "https://alphacephei.com/vosk/models/vosk-model-spk-0.4.zip"
SPK_MODEL_DIR = "/app/models/vosk-model-spk-0.4"

model = None
spk_model = None


def download_extract(url, target_dir):
    if not os.path.exists(target_dir):
        os.makedirs("/app/models", exist_ok=True)
        print(f"⬇️ Downloading {os.path.basename(url)}...")
        zip_path = f"/app/models/{os.path.basename(url)}"
        urllib.request.urlretrieve(url, zip_path)
        with zipfile.ZipFile(zip_path, "r") as zip_ref:
            zip_ref.extractall("/app/models")
        os.remove(zip_path)
        print(f"✅ Downloaded and extracted {target_dir}.")


@app.on_event("startup")
def startup():
    """Load model once when the server starts."""
    global model, spk_model
    download_extract(MODEL_URL, MODEL_DIR)
    download_extract(SPK_MODEL_URL, SPK_MODEL_DIR)
    
    model = Model(MODEL_DIR)
    spk_model = SpkModel(SPK_MODEL_DIR)
    print("✅ Vosk ASR and Speaker models loaded and ready!")


def convert_to_wav(input_path: str) -> str:
    """Converts any audio file to 16kHz mono WAV format required by Vosk."""
    wav_path = input_path + ".wav"
    audio = AudioSegment.from_file(input_path)
    audio = audio.set_channels(1).set_frame_rate(16000)
    audio.export(wav_path, format="wav")
    return wav_path


def transcribe_and_diarize(wav_path: str) -> str:
    """Transcribes a WAV file and groups by speakers."""
    wf = wave.open(wav_path, "rb")
    rec = KaldiRecognizer(model, wf.getframerate(), spk_model)
    rec.SetWords(True)

    results = []
    
    while True:
        data = wf.readframes(4000)
        if len(data) == 0:
            break
        if rec.AcceptWaveform(data):
            res = json.loads(rec.Result())
            if "spk" in res and "text" in res and res["text"].strip():
                results.append({"text": res["text"], "spk": res["spk"]})

    final_res = json.loads(rec.FinalResult())
    if "spk" in final_res and "text" in final_res and final_res["text"].strip():
        results.append({"text": final_res["text"], "spk": final_res["spk"]})
    wf.close()

    if not results:
        return ""

    # Cluster the speaker vectors into 2 clusters (Speaker A and Speaker B)
    # If there's only 1 valid sentence, clustering will fail with n_clusters=2
    if len(results) < 2:
        return f"Speaker 1: {results[0]['text']}"

    vectors = np.array([r["spk"] for r in results])
    kmeans = KMeans(n_clusters=2, random_state=0, n_init=10).fit(vectors)
    labels = kmeans.labels_

    dialogue = ""
    for i, res in enumerate(results):
        speaker_id = labels[i] + 1  # 1 or 2
        dialogue += f"Speaker {speaker_id}: {res['text']}\n"

    return dialogue.strip()


def analyze_text_with_groq(text: str) -> dict:
    if not groq_client or not text.strip():
        return {"sentiment": "unknown", "keywords": [], "complaints": [], "feedback": "No text or API key", "key_points": [], "model_used": "none"}
        
    prompt = f"""
You are an expert political surveyor analyzing a phone call conversation in Hindi.
The transcribed text from the call is diarized (Speaker 1 and Speaker 2):
\"\"\"
{text}
\"\"\"

Analyze the conversation. Usually, one speaker is the surveyor (asking questions) and the other is the voter (answering).
Return ONLY a valid JSON object with no markdown formatting or extra text.
The JSON must have the following keys:
- "sentiment": "positive", "negative", or "neutral" (overall sentiment of the voter towards the incumbent party/government)
- "keywords": Array of strings representing important/major keywords mentioned (e.g. ["unemployment", "inflation", "roads"])
- "complaints": Array of strings summarizing any complaints generated or raised by the voter.
- "feedback": String summarizing the feedback given by the voter.
- "key_points": Array of strings representing the key points detected in the conversation.
"""

    # 1. Start with the currently active model to avoid wasting time
    start_index = get_active_model_index()
    last_error = ""
    
    for i in range(len(GROQ_MODELS)):
        # Calculate which model to try based on start_index
        current_idx = (start_index + i) % len(GROQ_MODELS)
        model_name = GROQ_MODELS[current_idx]
        
        try:
            print(f"Trying Groq model: {model_name}...")
            completion = groq_client.chat.completions.create(
                messages=[{"role": "user", "content": prompt}],
                model=model_name,
                temperature=0.1,
                response_format={"type": "json_object"}
            )
            
            # If successful, and we moved to a new model, update the state file
            if current_idx != start_index:
                print(f"Successfully shifted to new model. Saving state: {model_name}")
                update_active_model_index(current_idx)
                
            result_data = json.loads(completion.choices[0].message.content)
            result_data["model_used"] = model_name
            return result_data
            
        except Exception as e:
            error_msg = str(e)
            print(f"Failed with {model_name}: {error_msg}")
            last_error = error_msg
            # If it's a rate limit or model access error, it will naturally continue to the next model in the loop
            continue
            
    # If all Groq models fail (e.g. daily limit on all models reached)
    return {"sentiment": "error", "keywords": [], "complaints": [], "feedback": f"All Groq models failed. Last Error: {last_error}", "key_points": [], "model_used": "none"}

# ─── API Endpoints ────────────────────────────────

@app.get("/health")
def health():
    return {"status": "ok", "model": "vosk-model-small-hi-0.22", "language": "hindi", "groq": groq_client is not None}


@app.post("/transcribe")
async def transcribe_audio(file: UploadFile = File(...)):
    """
    Accepts an audio file (mp3, wav, m4a, ogg, etc.)
    and returns the diarized Hindi transcription as text + Groq analysis.
    """
    if not file.filename:
        raise HTTPException(status_code=400, detail="No file provided.")

    suffix = os.path.splitext(file.filename)[1] or ".wav"
    tmp = tempfile.NamedTemporaryFile(delete=False, suffix=suffix)
    try:
        contents = await file.read()
        tmp.write(contents)
        tmp.close()

        wav_path = convert_to_wav(tmp.name)
        text = transcribe_and_diarize(wav_path)
        analysis = analyze_text_with_groq(text)
        os.unlink(wav_path)

        return JSONResponse(content={
            "status": "success",
            "text": text,
            "filename": file.filename,
            "analysis": analysis
        })

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
    finally:
        if os.path.exists(tmp.name):
            os.unlink(tmp.name)
