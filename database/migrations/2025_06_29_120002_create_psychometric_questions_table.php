<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('psychometric_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('psychometric_tests')->onDelete('cascade');
            $table->text('question_text');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('psychometric_questions');
    }
};
