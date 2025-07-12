<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('psychometric_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('related_problems')->nullable();
            $table->json('options')->nullable(); // odpowiedzi do pytan
            $table->integer('max_score')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('psychometric_tests');
    }
};
