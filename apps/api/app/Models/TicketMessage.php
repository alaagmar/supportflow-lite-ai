<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TicketMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    /** @use HasFactory<TicketMessageFactory> */
    use HasFactory;

    public const SENDER_CUSTOMER = 'customer';

    public const SENDER_AGENT = 'agent';

    public const SENDER_SYSTEM = 'system';

    /**
     * @var list<string>
     */
    public const SENDER_TYPES = [
        self::SENDER_CUSTOMER,
        self::SENDER_AGENT,
        self::SENDER_SYSTEM,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'sender_type',
        'sender_name',
        'body',
    ];

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
