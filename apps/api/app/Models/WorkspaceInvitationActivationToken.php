<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WorkspaceInvitationActivationTokenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceInvitationActivationToken extends Model
{
    /** @use HasFactory<WorkspaceInvitationActivationTokenFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'workspace_invitation_id',
        'invited_email',
        'token_hash',
        'expires_at',
        'used_at',
        'issued_at',
        'invalidated_at',
        'resend_count_window',
        'resend_window_started_at',
        'last_sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'issued_at' => 'datetime',
            'invalidated_at' => 'datetime',
            'resend_window_started_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'resend_count_window' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<WorkspaceInvitation, $this>
     */
    public function workspaceInvitation(): BelongsTo
    {
        return $this->belongsTo(WorkspaceInvitation::class);
    }

    public function isActive(): bool
    {
        return $this->used_at === null
            && $this->invalidated_at === null
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }
}
