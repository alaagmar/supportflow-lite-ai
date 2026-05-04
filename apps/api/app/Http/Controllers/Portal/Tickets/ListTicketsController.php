<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Tickets;

use App\Domain\Ticketing\UseCases\ListTickets;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tickets\TicketResource;
use App\Http\Responses\ApiResponse;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ListTicketsController extends Controller
{
    use ResolvesPortalContext;

    public function __invoke(Request $request, ListTickets $listTickets, int $workspace): JsonResponse
    {
        $this->authorizePortalAccess($request);
        Gate::authorize('viewAny', Ticket::class);

        /** @var User $user */
        $user = $request->user();

        return ApiResponse::resource(TicketResource::collection($listTickets->handle(
            user: $user,
            workspaceId: $workspace,
            perPage: min(max($request->integer('per_page', 25), 1), 100),
            roles: $this->portalRoles($request),
        )));
    }
}
