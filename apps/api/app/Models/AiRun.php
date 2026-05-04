<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AiRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiRun extends Model
{
    /** @use HasFactory<AiRunFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_RATE_LIMITED = 'rate_limited';

    public const STATUS_FALLBACK_USED = 'fallback_used';

    /**
     * @var list<string>
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_RUNNING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_RATE_LIMITED,
        self::STATUS_FALLBACK_USED,
    ];

    public const TASK_CLASSIFY_TICKET = 'classify_ticket';

    public const TASK_DRAFT_REPLY = 'draft_reply';

    public const TASK_SUMMARIZE_TICKET = 'summarize_ticket';

    /**
     * @var list<string>
     */
    public const TASK_TYPES = [
        self::TASK_CLASSIFY_TICKET,
        self::TASK_DRAFT_REPLY,
        self::TASK_SUMMARIZE_TICKET,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'workspace_id',
        'ticket_id',
        'provider',
        'model',
        'task_type',
        'status',
        'input_json',
        'output_json',
        'error_message',
        'latency_ms',
        'confidence',
        'prompt_version',
        'started_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_json' => 'array',
            'output_json' => 'array',
            'confidence' => 'decimal:4',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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
     * @return HasMany<TicketAiOutput, $this>
     */
    public function classificationOutputs(): HasMany
    {
        return $this->hasMany(TicketAiOutput::class, 'classification_run_id');
    }

    /**
     * @return HasMany<TicketAiOutput, $this>
     */
    public function draftOutputs(): HasMany
    {
        return $this->hasMany(TicketAiOutput::class, 'draft_run_id');
    }
}
