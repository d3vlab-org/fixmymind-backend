<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('voice_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voice_session_id')->constrained()->onDelete('cascade');
            $table->enum('sender', ['user', 'ai']);
            $table->text('text')->nullable();
            $table->string('audio_url')->nullable();
            $table->timestamp('timestamp');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('voice_messages');
    }
};
