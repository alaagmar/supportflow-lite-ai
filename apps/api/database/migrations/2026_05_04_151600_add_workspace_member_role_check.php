<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM pg_constraint
                    WHERE conname = 'workspace_members_role_check'
                    AND conrelid = 'workspace_members'::regclass
                ) THEN
                    ALTER TABLE workspace_members
                    ADD CONSTRAINT workspace_members_role_check
                    CHECK (role IN ('owner', 'admin', 'agent', 'viewer'));
                END IF;
            END
            $$;
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE workspace_members DROP CONSTRAINT IF EXISTS workspace_members_role_check');
    }
};
