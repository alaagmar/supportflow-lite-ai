<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\AuditAnalytics;

use App\Domain\AuditAnalytics\Services\QueryAuditTimeline;
use App\Domain\AuditAnalytics\Support\ResolvesWorkspaceAuditAccess;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\AuditAnalytics\Concerns\ResolvesWorkspaceAuditAnalyticsContext;
use App\Http\Requests\AuditAnalytics\ListTicketAuditLogsRequest;
use App\Http\Resources\AuditAnalytics\AuditLogCollectionResource;
use App\Http\Responses\ApiResponse;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ListTicketAuditLogsController extends Controller
{
    use ResolvesWorkspaceAuditAnalyticsContext;

    public function __invoke(
        ListTicketAuditLogsRequest $request,
        QueryAuditTimeline $queryAuditTimeline,
        ResolvesWorkspaceAuditAccess $workspaceAccess,
        int $workspace,
        int $ticket,
    ): JsonResponse {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $this->assertWorkspaceAccess($user, $workspace, $request, $workspaceAccess);

        Gate::authorize('viewTicketAudit', [AuditLog::class, $workspace]);

        $auditLogs = $queryAuditTimeline->forTicket(
            user: $user,
            workspaceId: $workspace,
            ticketId: $ticket,
            perPage: min(max($request->integer('per_page', 25), 1), 100),
            roles: $this->portalRoles($request),
        );

        return ApiResponse::resource(AuditLogCollectionResource::make($auditLogs));
    }
}
