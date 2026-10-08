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
        print(f"Downloading {os.path.basename(url)}...")
        zip_path = f"/app/models/{os.path.basename(url)}"
        urllib.request.urlretrieve(url, zip_path)
        with zipfile.ZipFile(zip_path, "r") as zip_ref:
            zip_ref.extractall("/app/models")
        os.remove(zip_path)
        print(f"Downloaded and extracted {target_dir}.")


@app.on_event("startup")
def startup():
    """Load model once when the server starts."""
    global model, spk_model
    download_extract(MODEL_URL, MODEL_DIR)
    download_extract(SPK_MODEL_URL, SPK_MODEL_DIR)
    
    model = Model(MODEL_DIR)
    spk_model = SpkModel(SPK_MODEL_DIR)
    print("Vosk ASR and Speaker models loaded and ready!")


def convert_to_wav(input_path: str) -> str:
    """Converts any audio file to 16kHz mono WAV format required by Vosk."""
    wav_path = input_path + ".wav"
    audio = AudioSegment.from_file(input_path)
    audio = audio.set_channels(1).set_frame_rate(16000)
    audio.export(wav_path, format="wav")
    return wav_path


def transcribe_and_diarize(wav_path: str) -> str:
    """Transcribes a WAV file and groups by speakers using robust diarization."""
    from scipy.spatial.distance import cosine
    from scipy.cluster.hierarchy import linkage, fcluster

    wf = wave.open(wav_path, "rb")
    framerate = wf.getframerate()
    rec = KaldiRecognizer(model, framerate, spk_model)
    rec.SetWords(True)

    # ── Step 1: Transcribe with larger chunks for better speaker vectors ──
    # 8000 frames at 16kHz = 0.5 sec chunks (double the original)
    # Larger chunks = more audio data per speaker vector = better accuracy
    raw_results = []

    while True:
        data = wf.readframes(8000)
        if len(data) == 0:
            break
        if rec.AcceptWaveform(data):
            res = json.loads(rec.Result())
            if "spk" in res and "text" in res and res["text"].strip():
                raw_results.append({"text": res["text"], "spk": res["spk"]})

    final_res = json.loads(rec.FinalResult())
    if "spk" in final_res and "text" in final_res and final_res["text"].strip():
        raw_results.append({"text": final_res["text"], "spk": final_res["spk"]})
    wf.close()

    if not raw_results:
        return ""

    if len(raw_results) == 1:
        return f"Speaker 1: {raw_results[0]['text']}"

    # ── Step 2: Merge very short consecutive segments to get longer, more stable vectors ──
    # If a segment has very few words, merge it with the next one for a more reliable speaker vector
    merged_results = []
    buffer_text = ""
    buffer_vectors = []

    for r in raw_results:
        word_count = len(r["text"].split())
        buffer_text += (" " if buffer_text else "") + r["text"]
        buffer_vectors.append(r["spk"])

        # Flush when we have enough words (at least 3 words per segment)
        if word_count >= 3 or len(buffer_vectors) >= 2:
            # Average the speaker vectors of merged segments
            avg_vector = np.mean(buffer_vectors, axis=0).tolist()
            merged_results.append({"text": buffer_text.strip(), "spk": avg_vector})
            buffer_text = ""
            buffer_vectors = []

    # Flush any remaining buffer
    if buffer_text.strip():
        avg_vector = np.mean(buffer_vectors, axis=0).tolist() if buffer_vectors else raw_results[-1]["spk"]
        merged_results.append({"text": buffer_text.strip(), "spk": avg_vector})

    if len(merged_results) < 2:
        return f"Speaker 1: {merged_results[0]['text']}"

    # ── Step 3: Cluster using Hierarchical Clustering with Cosine Distance ──
    # This is far more robust than KMeans for speaker diarization because:
    # - Cosine distance measures angle between vectors (direction), not magnitude
    # - Speaker embeddings are best compared by direction, not Euclidean distance
    vectors = np.array([r["spk"] for r in merged_results])

    # Compute pairwise cosine distance matrix
    n = len(vectors)
    condensed_dist = []
    for i in range(n):
        for j in range(i + 1, n):
            condensed_dist.append(cosine(vectors[i], vectors[j]))
    condensed_dist = np.array(condensed_dist)

    # Handle edge case: all distances are 0 (identical vectors)
    if np.all(condensed_dist < 0.01):
        # All segments sound like the same speaker
        dialogue = ""
        for r in merged_results:
            dialogue += f"Speaker 1: {r['text']}\n"
        return dialogue.strip()

    # Hierarchical clustering with average linkage
    Z = linkage(condensed_dist, method='average')

    # Cut the dendrogram at 2 clusters (we know there are 2 speakers in a call)
    labels = fcluster(Z, t=2, criterion='maxclust')

    # ── Step 4: Build the dialogue with speaker labels ──
    dialogue = ""
    for i, res in enumerate(merged_results):
        speaker_id = int(labels[i])
        dialogue += f"Speaker {speaker_id}: {res['text']}\n"

    return dialogue.strip()



HINDI_ANALYSIS_PROMPT = """आप एक अत्यंत कुशल राजनीतिक सर्वेक्षण विश्लेषक हैं। नीचे एक फ़ोन कॉल की ट्रांसक्रिप्शन दी गई है जिसमें दो वक्ता हैं — एक सर्वेक्षक (Agent) और दूसरा मतदाता (Voter)।

कॉल ट्रांसक्रिप्ट:
\"\"\"
{transcript}
\"\"\"

इस बातचीत का गहन विश्लेषण कीजिए। आपको केवल और केवल एक valid JSON object लौटाना है — कोई markdown, कोई extra text नहीं।

अत्यंत महत्वपूर्ण नियम:
1. "sentiment" को छोड़कर, JSON में सभी values हिंदी (देवनागरी लिपि) में होनी चाहिए।
2. हर field में विस्तृत और अर्थपूर्ण जानकारी दीजिए — एक-दो शब्दों का जवाब अस्वीकार्य है।
3. यदि कोई शिकायत या feedback नहीं है, तो उस array/string को खाली छोड़ दें।
4. summary में कम से कम 2-3 पूर्ण वाक्य हों।

JSON Structure (इसी format में output दें):
{{
  "sentiment": "positive" या "negative" या "neutral" (यह English में होना चाहिए),
  "summary": "इस कॉल का 2-3 वाक्यों में सम्पूर्ण सारांश हिंदी में लिखें। बताएं कि मतदाता ने मुख्य रूप से क्या कहा, उनकी भावना कैसी थी, और बातचीत का कुल निष्कर्ष क्या रहा।",
  "key_points": ["बातचीत में उठाए गए हर महत्वपूर्ण बिंदु को अलग-अलग विस्तार से लिखें", "दूसरा बिंदु"],
  "complaints": ["मतदाता द्वारा उठाई गई हर शिकायत को विस्तार से लिखें। अगर कोई शिकायत नहीं है तो खाली array दें।"],
  "feedback": "मतदाता ने सरकार, पार्टी, नेता या व्यवस्था के बारे में जो भी राय, टिप्पणी या प्रतिक्रिया दी है उसे यहाँ विस्तार से हिंदी में लिखें।",
  "keywords": ["बेरोज़गारी", "महंगाई", "भ्रष्टाचार", "सड़क", "पानी"]
}}"""


def analyze_text_with_groq(text: str) -> dict:
    if not groq_client or not text.strip():
        return {"sentiment": "unknown", "summary": "", "keywords": [], "complaints": [], "feedback": "", "key_points": [], "model_used": "none"}
        
    prompt = HINDI_ANALYSIS_PROMPT.format(transcript=text)

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
    return {"sentiment": "error", "summary": "", "keywords": [], "complaints": [], "feedback": f"All Groq models failed. Last Error: {last_error}", "key_points": [], "model_used": "none"}

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
