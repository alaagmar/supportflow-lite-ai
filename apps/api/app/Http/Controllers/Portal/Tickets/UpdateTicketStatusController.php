<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Tickets;

use App\Domain\Ticketing\UseCases\ShowTicket;
use App\Domain\Ticketing\UseCases\UpdateTicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\UpdateTicketStatusRequest;
use App\Http\Resources\Tickets\TicketResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UpdateTicketStatusController extends Controller
{
    use ResolvesPortalContext;

    public function __invoke(UpdateTicketStatusRequest $request, ShowTicket $showTicket, UpdateTicketStatus $updateTicketStatus, int $workspace, int $ticket): JsonResponse
    {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $ticketModel = $showTicket->handle($user, $workspace, $ticket, $this->portalRoles($request));

        Gate::authorize('updateStatus', $ticketModel);

        /** @var array{status: string} $validated */
        $validated = $request->validated();

        return ApiResponse::resource(new TicketResource($updateTicketStatus->handle($ticketModel, $validated['status'])));
    }
}
