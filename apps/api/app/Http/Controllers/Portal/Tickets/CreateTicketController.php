<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Tickets;

use App\Domain\Ticketing\UseCases\CreateTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Http\Resources\Tickets\TicketResource;
use App\Http\Responses\ApiResponse;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CreateTicketController extends Controller
{
    use ResolvesPortalContext;

    public function __invoke(StoreTicketRequest $request, CreateTicket $createTicket, int $workspace): JsonResponse
    {
        $this->authorizePortalAccess($request);
        Gate::authorize('create', [Ticket::class, $workspace]);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        return ApiResponse::resource(
            new TicketResource($createTicket->handle($user, $workspace, $validated, $this->portalRoles($request))),
            Response::HTTP_CREATED,
        );
    }
}
