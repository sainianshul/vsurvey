<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider_call_id')->nullable()->unique(); // Twilio CallSid or Telecmi uuid
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Jisne call kiya
            $table->string('phone_number'); // Jise call kiya gaya
            $table->string('name')->nullable()->default(null);
            $table->dateTime('call_timing')->nullable();
            $table->string('call_duration')->nullable(); // Can be in seconds or mm:ss
            $table->longText('audio_text')->nullable(); // Transcription
            $table->string('sentiment')->nullable();
            $table->json('ai_response')->nullable();
            $table->string('model_used')->nullable();
            $table->boolean('is_success')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
