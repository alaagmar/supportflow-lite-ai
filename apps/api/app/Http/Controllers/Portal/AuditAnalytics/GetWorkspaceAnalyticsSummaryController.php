<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\AuditAnalytics;

use App\Domain\AuditAnalytics\Services\BuildWorkspaceAnalyticsSummary;
use App\Domain\AuditAnalytics\Support\ResolvesWorkspaceAuditAccess;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\AuditAnalytics\Concerns\ResolvesWorkspaceAuditAnalyticsContext;
use App\Http\Requests\AuditAnalytics\GetWorkspaceAnalyticsSummaryRequest;
use App\Http\Resources\AuditAnalytics\WorkspaceAnalyticsSummaryResource;
use App\Http\Responses\ApiResponse;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class GetWorkspaceAnalyticsSummaryController extends Controller
{
    use ResolvesWorkspaceAuditAnalyticsContext;

    public function __invoke(
        GetWorkspaceAnalyticsSummaryRequest $request,
        BuildWorkspaceAnalyticsSummary $buildWorkspaceAnalyticsSummary,
        ResolvesWorkspaceAuditAccess $workspaceAccess,
        int $workspace,
    ): JsonResponse {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $this->assertWorkspaceAccess($user, $workspace, $request, $workspaceAccess);

        Gate::authorize('viewWorkspaceAnalytics', [AuditLog::class, $workspace]);

        $windowStart = $request->filled('start_at')
            ? CarbonImmutable::parse((string) $request->query('start_at'))
            : CarbonImmutable::now()->subDays(30)->startOfDay();

        $windowEnd = $request->filled('end_at')
            ? CarbonImmutable::parse((string) $request->query('end_at'))
            : CarbonImmutable::now()->endOfDay();

        $summary = $buildWorkspaceAnalyticsSummary->handle(
            user: $user,
            workspaceId: $workspace,
            roles: $this->portalRoles($request),
            windowStart: $windowStart,
            windowEnd: $windowEnd,
        );

        return ApiResponse::resource(WorkspaceAnalyticsSummaryResource::make($summary));
    }
}
