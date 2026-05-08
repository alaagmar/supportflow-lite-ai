<?php

use App\Models\WorkspaceInvitation;
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
        Schema::create('workspace_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('invited_email');
            $table->string('invited_role', 20);
            $table->string('status', 20)->default(WorkspaceInvitation::STATUS_PENDING);
            $table->foreignId('invited_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'invited_email']);
        });

        DB::statement("CREATE UNIQUE INDEX workspace_invitations_unique_pending_email ON workspace_invitations (workspace_id, invited_email) WHERE status = 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS workspace_invitations_unique_pending_email');

        Schema::dropIfExists('workspace_invitations');
    }
};
