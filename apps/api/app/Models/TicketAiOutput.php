<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TicketAiOutputFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAiOutput extends Model
{
    /** @use HasFactory<TicketAiOutputFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'workspace_id',
        'ticket_id',
        'classification_run_id',
        'draft_run_id',
        'summary',
        'category',
        'urgency',
        'sentiment',
        'language',
        'draft_reply',
        'recommended_action',
        'requires_human_approval',
        'confidence',
        'evidence_json',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_human_approval' => 'boolean',
            'confidence' => 'decimal:4',
            'evidence_json' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo<AiRun, $this>
     */
    public function classificationRun(): BelongsTo
    {
        return $this->belongsTo(AiRun::class, 'classification_run_id');
    }

    /**
     * @return BelongsTo<AiRun, $this>
     */
    public function draftRun(): BelongsTo
    {
        return $this->belongsTo(AiRun::class, 'draft_run_id');
    }
}
