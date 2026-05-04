<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Tickets;

use App\Domain\AiProcessing\UseCases\QueueTicketAiProcessing;
use App\Domain\Ticketing\UseCases\ShowTicket;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProcessTicketAiController extends Controller
{
    use ResolvesPortalContext;

    public function __invoke(
        Request $request,
        ShowTicket $showTicket,
        QueueTicketAiProcessing $queueTicketAiProcessing,
        int $workspace,
        int $ticket,
    ): JsonResponse {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $ticketModel = $showTicket->handle($user, $workspace, $ticket, $this->portalRoles($request));

        Gate::authorize('processAi', $ticketModel);

        $queued = $queueTicketAiProcessing->handle($ticketModel);
        $ticketModel->refresh();

        return ApiResponse::success([
            'data' => [
                'ticket_id' => $ticketModel->id,
                'workspace_id' => $ticketModel->workspace_id,
                'status' => $ticketModel->status,
                'queued' => $queued,
            ],
        ], Response::HTTP_ACCEPTED);
    }
}
