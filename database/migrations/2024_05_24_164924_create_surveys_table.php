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
        Schema::create('surveys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('type', ['PERSONALITY', 'FINANCIAL']);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('survey_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('survey_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->enum('type', ['STRING', 'INTEGER', 'LIKERT5', 'LIKERT7']);
            $table->timestamps();
        });

        Schema::create('survey_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('survey_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('survey_result_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('result_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained()->cascadeOnDelete();
            $table->string('answer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('survey_results');
        Schema::dropIfExists('survey_result_answers');
    }
};
