<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupDutyController;
use App\Http\Controllers\GroupInvitationController;
use App\Http\Controllers\GroupMemberController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('groups/{group}/duties', [GroupDutyController::class, 'index'])->name('groups.duties.index');
    Route::post('groups/{group}/duties', [GroupDutyController::class, 'store'])->name('groups.duties.store');
    Route::get('groups/{group}/members', [GroupMemberController::class, 'index'])->name('groups.members.index');
    Route::patch('groups/{group}/members/{groupMember}', [GroupMemberController::class, 'update'])->name('groups.members.update');
    Route::delete('groups/{group}/members/{groupMember}', [GroupMemberController::class, 'destroy'])->name('groups.members.destroy');
    Route::post('groups/{group}/invitations', [GroupInvitationController::class, 'store'])->name('groups.invitations.store');
    Route::delete('groups/{group}/invitations/{groupInvitation}', [GroupInvitationController::class, 'destroy'])->name('groups.invitations.destroy');
    Route::get('group-invitations/{groupInvitation}', [GroupInvitationController::class, 'show'])->name('group-invitations.show');
    Route::post('group-invitations/{groupInvitation}/accept', [GroupInvitationController::class, 'accept'])->name('group-invitations.accept');
});

require __DIR__.'/settings.php';
