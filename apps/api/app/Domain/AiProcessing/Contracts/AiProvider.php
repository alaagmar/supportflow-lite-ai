<?php

declare(strict_types=1);

namespace App\Domain\AiProcessing\Contracts;

interface AiProvider
{
    public function provider(): string;

    public function model(): ?string;

    /**
     * @param  array<string, mixed>  $ticket
     * @return array<string, mixed>
     */
    public function classifyTicket(array $ticket): array;

    /**
     * @param  array<string, mixed>  $ticket
     * @param  list<array<string, mixed>>  $contextChunks
     * @return array<string, mixed>
     */
    public function draftReply(array $ticket, array $contextChunks): array;
}
