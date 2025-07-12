<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('psychometric_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_id')->constrained('psychometric_tests')->onDelete('cascade');
            $table->json('answers');
            $table->integer('score')->nullable();
            $table->text('interpretation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('psychometric_scores');
    }
};
