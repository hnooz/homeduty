<?php

use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DutySwapRequestController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\GroupDutyController;
use App\Http\Controllers\Api\V1\GroupInvitationController;
use App\Http\Controllers\Api\V1\GroupMemberController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function (): void {
    Route::post('auth/login', LoginController::class)->name('api.v1.auth.login');
    Route::post('auth/register', RegisterController::class)->name('api.v1.auth.register');
    Route::post('auth/forgot-password', ForgotPasswordController::class)->name('api.v1.auth.forgot-password');
    Route::post('auth/reset-password', ResetPasswordController::class)->name('api.v1.auth.reset-password');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', LogoutController::class)->name('api.v1.auth.logout');

        Route::get('user', [UserController::class, 'show'])->name('api.v1.user.show');
        Route::patch('user', [UserController::class, 'update'])->name('api.v1.user.update');

        Route::get('dashboard', DashboardController::class)->name('api.v1.dashboard');

        Route::get('groups', [GroupController::class, 'index'])->name('api.v1.groups.index');
        Route::post('groups', [GroupController::class, 'store'])->name('api.v1.groups.store');
        Route::get('groups/{group}', [GroupController::class, 'show'])->name('api.v1.groups.show');
        Route::patch('groups/{group}', [GroupController::class, 'update'])->name('api.v1.groups.update');
        Route::delete('groups/{group}', [GroupController::class, 'destroy'])->name('api.v1.groups.destroy');

        Route::get('groups/{group}/duties', [GroupDutyController::class, 'index'])->name('api.v1.groups.duties.index');
        Route::post('groups/{group}/duties', [GroupDutyController::class, 'store'])->name('api.v1.groups.duties.store');
        Route::get('groups/{group}/duties/{duty}', [GroupDutyController::class, 'show'])->name('api.v1.groups.duties.show');
        Route::patch('groups/{group}/duties/{duty}', [GroupDutyController::class, 'update'])->name('api.v1.groups.duties.update');
        Route::delete('groups/{group}/duties/{duty}', [GroupDutyController::class, 'destroy'])->name('api.v1.groups.duties.destroy');

        Route::get('groups/{group}/members', [GroupMemberController::class, 'index'])->name('api.v1.groups.members.index');
        Route::patch('groups/{group}/members/{member}', [GroupMemberController::class, 'update'])->name('api.v1.groups.members.update');
        Route::delete('groups/{group}/members/{member}', [GroupMemberController::class, 'destroy'])->name('api.v1.groups.members.destroy');

        Route::post('groups/{group}/invitations', [GroupInvitationController::class, 'store'])->name('api.v1.groups.invitations.store');
        Route::post('groups/{group}/invitations/{invitation}/accept-direct', [GroupInvitationController::class, 'acceptDirect'])->name('api.v1.groups.invitations.accept-direct');
        Route::delete('groups/{group}/invitations/{invitation}', [GroupInvitationController::class, 'destroy'])->name('api.v1.groups.invitations.destroy');

        Route::get('invitations', [InvitationController::class, 'index'])->name('api.v1.invitations.index');
        Route::get('invitations/{invitation}', [InvitationController::class, 'show'])->name('api.v1.invitations.show');
        Route::post('invitations/{invitation}/accept', [InvitationController::class, 'accept'])->name('api.v1.invitations.accept');
        Route::post('invitations/{invitation}/decline', [InvitationController::class, 'decline'])->name('api.v1.invitations.decline');

        Route::post('groups/{group}/duty-swap-requests', [DutySwapRequestController::class, 'store'])->name('api.v1.groups.duty-swap-requests.store');
        Route::post('groups/{group}/duty-swap-requests/{dutySwapRequest}/accept', [DutySwapRequestController::class, 'accept'])->name('api.v1.groups.duty-swap-requests.accept');
        Route::post('groups/{group}/duty-swap-requests/{dutySwapRequest}/reject', [DutySwapRequestController::class, 'reject'])->name('api.v1.groups.duty-swap-requests.reject');
    });
});
