<?php

use App\Domain\Identity\Portal;
use App\Http\Controllers\Admin\Auth\CurrentSessionController as AdminCurrentSessionController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\Auth\LogoutController as AdminLogoutController;
use App\Http\Controllers\Admin\Workspaces\ListWorkspacesController as AdminListWorkspacesController;
use App\Http\Controllers\Admin\Workspaces\ShowWorkspaceController as AdminShowWorkspaceController;
use App\Http\Controllers\Owner\Auth\CurrentSessionController as OwnerCurrentSessionController;
use App\Http\Controllers\Owner\Auth\LoginController as OwnerLoginController;
use App\Http\Controllers\Owner\Auth\LogoutController as OwnerLogoutController;
use App\Http\Controllers\Owner\Auth\RegisterController as OwnerRegisterController;
use App\Http\Controllers\Owner\Workspaces\CreateWorkspaceController as OwnerCreateWorkspaceController;
use App\Http\Controllers\Owner\Workspaces\ListWorkspacesController as OwnerListWorkspacesController;
use App\Http\Controllers\Owner\Workspaces\ShowWorkspaceController as OwnerShowWorkspaceController;
use App\Http\Controllers\Portal\Policies\ArchivePolicyDocumentController;
use App\Http\Controllers\Portal\Policies\ListCreatePolicyDocumentController;
use App\Http\Controllers\Portal\Policies\RetrievePolicyGuidanceController;
use App\Http\Controllers\Portal\Policies\UnarchivePolicyDocumentController;
use App\Http\Controllers\Portal\Policies\UpdatePolicyDocumentController;
use App\Http\Controllers\Portal\AuditAnalytics\GetWorkspaceAnalyticsSummaryController;
use App\Http\Controllers\Portal\AuditAnalytics\ListTicketAuditLogsController;
use App\Http\Controllers\Portal\AuditAnalytics\ListWorkspaceAuditLogsController;
use App\Http\Controllers\Portal\Team\AcceptWorkspaceInvitationController;
use App\Http\Controllers\Portal\Team\DeclineWorkspaceInvitationController;
use App\Http\Controllers\Portal\Team\ListCreateWorkspaceInvitationController;
use App\Http\Controllers\Portal\Team\ListWorkspaceMembersController;
use App\Http\Controllers\Portal\Team\RemoveWorkspaceMemberController;
use App\Http\Controllers\Portal\Team\RevokeWorkspaceInvitationController;
use App\Http\Controllers\Portal\Team\UpdateWorkspaceMemberRoleController;
use App\Http\Controllers\Portal\Tickets\CreateTicketController;
use App\Http\Controllers\Portal\Tickets\DeleteTicketController;
use App\Http\Controllers\Portal\Tickets\ListTicketsController;
use App\Http\Controllers\Portal\Tickets\ProcessTicketAiController;
use App\Http\Controllers\Portal\Tickets\ShowTicketAiOutputController;
use App\Http\Controllers\Portal\Tickets\ShowTicketController;
use App\Http\Controllers\Portal\Tickets\UpdateTicketController;
use App\Http\Controllers\Portal\Tickets\UpdateTicketStatusController;
use App\Http\Controllers\Staff\Auth\CompleteInvitationActivationController;
use App\Http\Controllers\Staff\Auth\CurrentSessionController as StaffCurrentSessionController;
use App\Http\Controllers\Staff\Auth\LoginController as StaffLoginController;
use App\Http\Controllers\Staff\Auth\LogoutController as StaffLogoutController;
use App\Http\Controllers\Staff\Auth\ResendInvitationActivationController;
use App\Http\Controllers\Staff\Workspaces\ListWorkspacesController as StaffListWorkspacesController;
use App\Http\Controllers\Staff\Workspaces\ShowWorkspaceController as StaffShowWorkspaceController;
use Illuminate\Support\Facades\Route;

$registerPortalTicketRoutes = static function (string $portal, string $portalAbility): void {
    Route::get('/workspaces/{workspace}/tickets', ListTicketsController::class)
        ->whereNumber('workspace')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::post('/workspaces/{workspace}/tickets', CreateTicketController::class)
        ->whereNumber('workspace')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::get('/workspaces/{workspace}/tickets/{ticket}', ShowTicketController::class)
        ->whereNumber('workspace')
        ->whereNumber('ticket')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::patch('/workspaces/{workspace}/tickets/{ticket}', UpdateTicketController::class)
        ->whereNumber('workspace')
        ->whereNumber('ticket')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::patch('/workspaces/{workspace}/tickets/{ticket}/status', UpdateTicketStatusController::class)
        ->whereNumber('workspace')
        ->whereNumber('ticket')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::post('/workspaces/{workspace}/tickets/{ticket}/ai/process', ProcessTicketAiController::class)
        ->whereNumber('workspace')
        ->whereNumber('ticket')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::get('/workspaces/{workspace}/tickets/{ticket}/ai-output', ShowTicketAiOutputController::class)
        ->whereNumber('workspace')
        ->whereNumber('ticket')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::delete('/workspaces/{workspace}/tickets/{ticket}', DeleteTicketController::class)
        ->whereNumber('workspace')
        ->whereNumber('ticket')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);
};

$registerPortalPolicyRoutes = static function (string $portal, string $portalAbility, bool $allowMutations, bool $allowRetrieval): void {
    Route::get('/workspaces/{workspace}/policies', ListCreatePolicyDocumentController::class)
        ->whereNumber('workspace')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    if ($allowMutations) {
        Route::post('/workspaces/{workspace}/policies', ListCreatePolicyDocumentController::class)
            ->whereNumber('workspace')
            ->defaults('portal', $portal)
            ->defaults('portal_ability', $portalAbility);

        Route::patch('/workspaces/{workspace}/policies/{policy}', UpdatePolicyDocumentController::class)
            ->whereNumber('workspace')
            ->whereNumber('policy')
            ->defaults('portal', $portal)
            ->defaults('portal_ability', $portalAbility);

        Route::post('/workspaces/{workspace}/policies/{policy}/archive', ArchivePolicyDocumentController::class)
            ->whereNumber('workspace')
            ->whereNumber('policy')
            ->defaults('portal', $portal)
            ->defaults('portal_ability', $portalAbility);

        Route::post('/workspaces/{workspace}/policies/{policy}/unarchive', UnarchivePolicyDocumentController::class)
            ->whereNumber('workspace')
            ->whereNumber('policy')
            ->defaults('portal', $portal)
            ->defaults('portal_ability', $portalAbility);
    }

    if ($allowRetrieval) {
        Route::post('/workspaces/{workspace}/policies/retrieve', RetrievePolicyGuidanceController::class)
            ->whereNumber('workspace')
            ->defaults('portal', $portal)
            ->defaults('portal_ability', $portalAbility);
    }
};

$registerPortalTeamManagementRoutes = static function (string $portal, string $portalAbility): void {
    Route::get('/workspaces/{workspace}/invitations', ListCreateWorkspaceInvitationController::class)
        ->whereNumber('workspace')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::post('/workspaces/{workspace}/invitations', ListCreateWorkspaceInvitationController::class)
        ->whereNumber('workspace')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::post('/workspaces/{workspace}/invitations/{invitation}/revoke', RevokeWorkspaceInvitationController::class)
        ->whereNumber('workspace')
        ->whereNumber('invitation')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::get('/workspaces/{workspace}/members', ListWorkspaceMembersController::class)
        ->whereNumber('workspace')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::patch('/workspaces/{workspace}/members/{member}', UpdateWorkspaceMemberRoleController::class)
        ->whereNumber('workspace')
        ->whereNumber('member')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::delete('/workspaces/{workspace}/members/{member}', RemoveWorkspaceMemberController::class)
        ->whereNumber('workspace')
        ->whereNumber('member')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);
};

$registerInvitationResponseRoutes = static function (): void {
    Route::post('/workspaces/{workspace}/invitations/{invitation}/accept', AcceptWorkspaceInvitationController::class)
        ->whereNumber('workspace')
        ->whereNumber('invitation')
        ->defaults('portal', Portal::STAFF)
        ->defaults('portal_ability', 'accessStaffPortal');

    Route::post('/workspaces/{workspace}/invitations/{invitation}/decline', DeclineWorkspaceInvitationController::class)
        ->whereNumber('workspace')
        ->whereNumber('invitation')
        ->defaults('portal', Portal::STAFF)
        ->defaults('portal_ability', 'accessStaffPortal');
};

$registerPortalAuditAnalyticsRoutes = static function (string $portal, string $portalAbility): void {
    Route::get('/workspaces/{workspace}/audit-logs', ListWorkspaceAuditLogsController::class)
        ->whereNumber('workspace')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::get('/workspaces/{workspace}/tickets/{ticket}/audit-logs', ListTicketAuditLogsController::class)
        ->whereNumber('workspace')
        ->whereNumber('ticket')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);

    Route::get('/workspaces/{workspace}/analytics/summary', GetWorkspaceAnalyticsSummaryController::class)
        ->whereNumber('workspace')
        ->defaults('portal', $portal)
        ->defaults('portal_ability', $portalAbility);
};

Route::prefix('owner')->group(function () use ($registerPortalTicketRoutes, $registerPortalPolicyRoutes, $registerPortalTeamManagementRoutes, $registerPortalAuditAnalyticsRoutes): void {
    Route::post('/auth/register', OwnerRegisterController::class)->middleware('throttle:auth-register-owner');
    Route::post('/auth/login', OwnerLoginController::class)->middleware('throttle:auth-login');

    Route::middleware('auth:sanctum')->group(function () use ($registerPortalTicketRoutes, $registerPortalPolicyRoutes, $registerPortalTeamManagementRoutes, $registerPortalAuditAnalyticsRoutes): void {
        Route::get('/auth/me', OwnerCurrentSessionController::class);
        Route::post('/auth/logout', OwnerLogoutController::class);

        Route::get('/workspaces', OwnerListWorkspacesController::class);
        Route::post('/workspaces', OwnerCreateWorkspaceController::class);
        Route::get('/workspaces/{workspace}', OwnerShowWorkspaceController::class)->whereNumber('workspace');

        $registerPortalTicketRoutes(Portal::OWNER, 'accessOwnerPortal');
        $registerPortalPolicyRoutes(Portal::OWNER, 'accessOwnerPortal', true, false);
        $registerPortalTeamManagementRoutes(Portal::OWNER, 'accessOwnerPortal');
        $registerPortalAuditAnalyticsRoutes(Portal::OWNER, 'accessOwnerPortal');
    });
});

Route::prefix('admin')->group(function () use ($registerPortalTicketRoutes, $registerPortalPolicyRoutes, $registerPortalTeamManagementRoutes, $registerPortalAuditAnalyticsRoutes): void {
    Route::post('/auth/login', AdminLoginController::class)->middleware('throttle:auth-login');

    Route::middleware('auth:sanctum')->group(function () use ($registerPortalTicketRoutes, $registerPortalPolicyRoutes, $registerPortalTeamManagementRoutes, $registerPortalAuditAnalyticsRoutes): void {
        Route::get('/auth/me', AdminCurrentSessionController::class);
        Route::post('/auth/logout', AdminLogoutController::class);

        Route::get('/workspaces', AdminListWorkspacesController::class);
        Route::get('/workspaces/{workspace}', AdminShowWorkspaceController::class)->whereNumber('workspace');

        $registerPortalTicketRoutes(Portal::ADMIN, 'accessAdminPortal');
        $registerPortalPolicyRoutes(Portal::ADMIN, 'accessAdminPortal', true, false);
        $registerPortalTeamManagementRoutes(Portal::ADMIN, 'accessAdminPortal');
        $registerPortalAuditAnalyticsRoutes(Portal::ADMIN, 'accessAdminPortal');
    });
});

Route::prefix('staff')->group(function () use ($registerPortalTicketRoutes, $registerPortalPolicyRoutes, $registerPortalTeamManagementRoutes, $registerInvitationResponseRoutes, $registerPortalAuditAnalyticsRoutes): void {
    Route::post('/auth/login', StaffLoginController::class)->middleware('throttle:auth-login');
    Route::post('/auth/activation/complete', CompleteInvitationActivationController::class);
    Route::post('/auth/activation/resend', ResendInvitationActivationController::class);

    Route::middleware('auth:sanctum')->group(function () use ($registerPortalTicketRoutes, $registerPortalPolicyRoutes, $registerPortalTeamManagementRoutes, $registerInvitationResponseRoutes, $registerPortalAuditAnalyticsRoutes): void {
        Route::get('/auth/me', StaffCurrentSessionController::class);
        Route::post('/auth/logout', StaffLogoutController::class);

        Route::get('/workspaces', StaffListWorkspacesController::class);
        Route::get('/workspaces/{workspace}', StaffShowWorkspaceController::class)->whereNumber('workspace');

        $registerPortalTicketRoutes(Portal::STAFF, 'accessStaffPortal');
        $registerPortalPolicyRoutes(Portal::STAFF, 'accessStaffPortal', false, true);
        $registerPortalTeamManagementRoutes(Portal::STAFF, 'accessStaffPortal');
        $registerInvitationResponseRoutes();
        $registerPortalAuditAnalyticsRoutes(Portal::STAFF, 'accessStaffPortal');
    });
});
