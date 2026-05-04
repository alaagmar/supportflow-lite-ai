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
        Schema::create('ai_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 80);
            $table->string('model', 120)->nullable();
            $table->string('task_type', 40);
            $table->string('status', 40)->default('pending');
            $table->jsonb('input_json')->nullable();
            $table->jsonb('output_json')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('latency_ms')->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->string('prompt_version', 50)->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'ticket_id', 'created_at']);
            $table->index(['workspace_id', 'task_type', 'created_at']);
        });

        Schema::create('ticket_ai_outputs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classification_run_id')->nullable()->constrained('ai_runs')->nullOnDelete();
            $table->foreignId('draft_run_id')->nullable()->constrained('ai_runs')->nullOnDelete();
            $table->text('summary')->nullable();
            $table->string('category', 80)->nullable();
            $table->string('urgency', 40)->nullable();
            $table->string('sentiment', 40)->nullable();
            $table->string('language', 12)->nullable();
            $table->text('draft_reply')->nullable();
            $table->text('recommended_action')->nullable();
            $table->boolean('requires_human_approval')->default(true);
            $table->decimal('confidence', 5, 4)->nullable();
            $table->jsonb('evidence_json')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'ticket_id']);
            $table->index(['workspace_id', 'requires_human_approval']);
            $table->index(['workspace_id', 'updated_at']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE ai_runs
            ADD CONSTRAINT ai_runs_status_check
            CHECK (status IN ('pending', 'running', 'completed', 'failed', 'rate_limited', 'fallback_used'))
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE ai_runs
            ADD CONSTRAINT ai_runs_task_type_check
            CHECK (task_type IN ('classify_ticket', 'draft_reply', 'summarize_ticket'))
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_ai_outputs');
        Schema::dropIfExists('ai_runs');
    }
};
