<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DutySwapRequestController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupDutyController;
use App\Http\Controllers\GroupInvitationController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Landing', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::inertia('/how-it-works', 'HowItWorks')->name('how-it-works');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('groups/{group}/duties', [GroupDutyController::class, 'index'])->name('groups.duties.index');
    Route::post('groups/{group}/duties', [GroupDutyController::class, 'store'])->name('groups.duties.store');
    Route::patch('groups/{group}/duties/{duty}', [GroupDutyController::class, 'update'])->name('groups.duties.update');
    Route::delete('groups/{group}/duties/{duty}', [GroupDutyController::class, 'destroy'])->name('groups.duties.destroy');
    Route::get('groups/{group}/members', [GroupMemberController::class, 'index'])->name('groups.members.index');
    Route::patch('groups/{group}/members/{groupMember}', [GroupMemberController::class, 'update'])->name('groups.members.update');
    Route::delete('groups/{group}/members/{groupMember}', [GroupMemberController::class, 'destroy'])->name('groups.members.destroy');
    Route::post('groups/{group}/invite-link/regenerate', [GroupController::class, 'regenerateInviteLink'])->name('groups.invite-link.regenerate');
    Route::get('join/{token}', [GroupController::class, 'joinViaLink'])->name('groups.join');
    Route::post('groups/{group}/invitations', [GroupInvitationController::class, 'store'])->name('groups.invitations.store');
    Route::post('groups/{group}/invitations/{groupInvitation}/accept-direct', [GroupInvitationController::class, 'acceptDirect'])->name('groups.invitations.accept-direct');
    Route::delete('groups/{group}/invitations/{groupInvitation}', [GroupInvitationController::class, 'destroy'])->name('groups.invitations.destroy');
    Route::get('group-invitations/{groupInvitation}', [GroupInvitationController::class, 'show'])->name('group-invitations.show');
    Route::post('group-invitations/{groupInvitation}/accept', [GroupInvitationController::class, 'accept'])->name('group-invitations.accept');
    Route::post('groups/{group}/duty-swap-requests', [DutySwapRequestController::class, 'store'])->name('groups.duty-swap-requests.store');
    Route::post('groups/{group}/duty-swap-requests/{dutySwapRequest}/accept', [DutySwapRequestController::class, 'accept'])->name('groups.duty-swap-requests.accept');
    Route::post('groups/{group}/duty-swap-requests/{dutySwapRequest}/reject', [DutySwapRequestController::class, 'reject'])->name('groups.duty-swap-requests.reject');
});

Route::impersonate();

require __DIR__.'/settings.php';
