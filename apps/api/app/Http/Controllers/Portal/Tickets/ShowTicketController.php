<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Tickets;

use App\Domain\Ticketing\UseCases\ShowTicket;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tickets\TicketResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShowTicketController extends Controller
{
    use ResolvesPortalContext;

    public function __invoke(Request $request, ShowTicket $showTicket, int $workspace, int $ticket): JsonResponse
    {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $ticketModel = $showTicket->handle($user, $workspace, $ticket, $this->portalRoles($request));

        Gate::authorize('view', $ticketModel);

        return ApiResponse::resource(new TicketResource($ticketModel));
    }
}
