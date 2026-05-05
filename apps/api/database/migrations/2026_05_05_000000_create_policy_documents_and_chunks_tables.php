<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('policy_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('content_text');
            $table->string('type', 32)->default('text');
            $table->string('status', 20)->default('active');
            $table->timestampTz('archived_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'updated_at']);
        });

        Schema::create('policy_chunks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_document_id')->constrained('policy_documents')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->text('chunk_text');
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->unique(['policy_document_id', 'chunk_index']);
            $table->index(['workspace_id', 'policy_document_id']);
            $table->index(['workspace_id', 'created_at']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE policy_documents
            ADD CONSTRAINT policy_documents_status_check
            CHECK (status IN ('active', 'archived'))
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_chunks');
        Schema::dropIfExists('policy_documents');
    }
};
