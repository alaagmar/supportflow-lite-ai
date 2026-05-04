<?php

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
use App\Http\Controllers\Staff\Auth\CurrentSessionController as StaffCurrentSessionController;
use App\Http\Controllers\Staff\Auth\LoginController as StaffLoginController;
use App\Http\Controllers\Staff\Auth\LogoutController as StaffLogoutController;
use App\Http\Controllers\Staff\Workspaces\ListWorkspacesController as StaffListWorkspacesController;
use App\Http\Controllers\Staff\Workspaces\ShowWorkspaceController as StaffShowWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::prefix('owner')->group(function (): void {
    Route::post('/auth/register', OwnerRegisterController::class)->middleware('throttle:auth-register-owner');
    Route::post('/auth/login', OwnerLoginController::class)->middleware('throttle:auth-login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', OwnerCurrentSessionController::class);
        Route::post('/auth/logout', OwnerLogoutController::class);

        Route::get('/workspaces', OwnerListWorkspacesController::class);
        Route::post('/workspaces', OwnerCreateWorkspaceController::class);
        Route::get('/workspaces/{workspace}', OwnerShowWorkspaceController::class)->whereNumber('workspace');
    });
});

Route::prefix('admin')->group(function (): void {
    Route::post('/auth/login', AdminLoginController::class)->middleware('throttle:auth-login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', AdminCurrentSessionController::class);
        Route::post('/auth/logout', AdminLogoutController::class);

        Route::get('/workspaces', AdminListWorkspacesController::class);
        Route::get('/workspaces/{workspace}', AdminShowWorkspaceController::class)->whereNumber('workspace');
    });
});

Route::prefix('staff')->group(function (): void {
    Route::post('/auth/login', StaffLoginController::class)->middleware('throttle:auth-login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', StaffCurrentSessionController::class);
        Route::post('/auth/logout', StaffLogoutController::class);

        Route::get('/workspaces', StaffListWorkspacesController::class);
        Route::get('/workspaces/{workspace}', StaffShowWorkspaceController::class)->whereNumber('workspace');
    });
});
