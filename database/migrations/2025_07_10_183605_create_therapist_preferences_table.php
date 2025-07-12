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
        Schema::create('therapist_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');

            $table->string('name', 100)->default('Alex');
            $table->enum('style', ['warm', 'calm', 'analytical', 'motivating', 'deep'])->default('warm');
            $table->enum('tempo', ['slow', 'medium', 'fast'])->default('slow');
            $table->enum('tone', ['soft', 'neutral', 'firm'])->default('soft');
            $table->boolean('add_markers')->default(true);
            $table->boolean('ask_questions')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('therapist_preferences');
    }
};
