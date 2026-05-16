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
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity_type', 60);
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 120);
            $table->json('metadata_json')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['workspace_id', 'created_at']);
            $table->index(['workspace_id', 'action', 'created_at']);
            $table->index(['workspace_id', 'entity_type', 'entity_id']);
            $table->index(['workspace_id', 'user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
