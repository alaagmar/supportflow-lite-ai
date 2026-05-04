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
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name', 120);
            $table->string('customer_email', 255);
            $table->string('subject', 255);
            $table->text('body');
            $table->string('status', 20)->default('new');
            $table->string('category', 80)->nullable();
            $table->string('urgency', 40)->nullable();
            $table->string('sentiment', 40)->nullable();
            $table->string('language', 12)->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'assigned_to']);
            $table->index(['workspace_id', 'created_at']);
        });

        Schema::create('ticket_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('sender_type', 20);
            $table->string('sender_name', 120)->nullable();
            $table->text('body');
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_status_check
            CHECK (status IN ('new', 'processing', 'needs_review', 'approved', 'rejected', 'resolved', 'failed'))
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE ticket_messages
            ADD CONSTRAINT ticket_messages_sender_type_check
            CHECK (sender_type IN ('customer', 'agent', 'system'))
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
    }
};
