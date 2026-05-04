<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Tickets;

use App\Domain\Ticketing\UseCases\ShowTicket;
use App\Domain\Ticketing\UseCases\UpdateTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\UpdateTicketRequest;
use App\Http\Resources\Tickets\TicketResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UpdateTicketController extends Controller
{
    use ResolvesPortalContext;

    public function __invoke(UpdateTicketRequest $request, ShowTicket $showTicket, UpdateTicket $updateTicket, int $workspace, int $ticket): JsonResponse
    {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $ticketModel = $showTicket->handle($user, $workspace, $ticket, $this->portalRoles($request));

        Gate::authorize('update', $ticketModel);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return ApiResponse::resource(new TicketResource($updateTicket->handle($ticketModel, $validated)));
    }
}
