<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booth_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('chunk_text');
            $table->vector('embedding', 1536);
            $table->integer('chunk_order')->default(0);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        // HNSW index for fast approximate nearest-neighbor search
        DB::statement('CREATE INDEX IF NOT EXISTS idx_knowledge_chunks_embedding ON knowledge_chunks USING hnsw (embedding vector_cosine_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_chunks');
    }
};
