<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Tickets;

use App\Domain\Ticketing\UseCases\DeleteTicket;
use App\Domain\Ticketing\UseCases\ShowTicket;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeleteTicketController extends Controller
{
    use ResolvesPortalContext;

    public function __invoke(Request $request, ShowTicket $showTicket, DeleteTicket $deleteTicket, int $workspace, int $ticket): JsonResponse
    {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $ticketModel = $showTicket->handle($user, $workspace, $ticket, $this->portalRoles($request));

        Gate::authorize('delete', $ticketModel);

        $deleteTicket->handle($ticketModel);

        return ApiResponse::noContent();
    }
}
