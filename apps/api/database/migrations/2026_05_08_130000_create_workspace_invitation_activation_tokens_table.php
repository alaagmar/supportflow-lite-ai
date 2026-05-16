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
        Schema::create('workspace_invitation_activation_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_invitation_id')->constrained('workspace_invitations')->cascadeOnDelete();
            $table->string('invited_email');
            $table->string('token_hash', 128)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('issued_at');
            $table->timestamp('invalidated_at')->nullable();
            $table->unsignedInteger('resend_count_window')->default(0);
            $table->timestamp('resend_window_started_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->index(['workspace_invitation_id', 'expires_at']);
            $table->index(['invited_email', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_invitation_activation_tokens');
    }
};
