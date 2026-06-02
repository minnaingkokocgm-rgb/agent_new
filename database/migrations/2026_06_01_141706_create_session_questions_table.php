<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('visitor_sessions')->cascadeOnDelete();
            $table->text('question_text');
            $table->integer('question_order');
            $table->timestamp('asked_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_questions');
    }
};
