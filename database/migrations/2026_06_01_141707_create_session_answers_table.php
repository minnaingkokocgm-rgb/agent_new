<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('session_questions')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('visitor_sessions')->cascadeOnDelete();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->text('answer_text');
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_answers');
    }
};
