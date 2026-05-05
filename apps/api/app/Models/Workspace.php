<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WorkspaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workspace extends Model
{
    /** @use HasFactory<WorkspaceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    public static function uniqueSlugForName(string $name): string
    {
        $base = Str::slug($name) ?: 'workspace';
        $slug = $base;
        $counter = 2;

        while (self::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * @return HasMany<WorkspaceMember, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * @return HasMany<AiRun, $this>
     */
    public function aiRuns(): HasMany
    {
        return $this->hasMany(AiRun::class);
    }

    /**
     * @return HasMany<TicketAiOutput, $this>
     */
    public function ticketAiOutputs(): HasMany
    {
        return $this->hasMany(TicketAiOutput::class);
    }

    /**
     * @return HasMany<PolicyDocument, $this>
     */
    public function policyDocuments(): HasMany
    {
        return $this->hasMany(PolicyDocument::class);
    }

    /**
     * @return HasMany<PolicyChunk, $this>
     */
    public function policyChunks(): HasMany
    {
        return $this->hasMany(PolicyChunk::class);
    }
}
