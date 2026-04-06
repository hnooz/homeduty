<?php

namespace App\Filament\Widgets;

use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\Setting;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $mailer = Setting::get('mail_mailer', config('mail.default'));

        return [
            Stat::make('Total Groups', Group::query()->count()),
            Stat::make('Total Members', User::query()->count()),
            Stat::make('Pending Invitations', GroupInvitation::query()->pending()->count()),
            Stat::make('Mailer', strtoupper($mailer ?? 'not configured'))
                ->description($mailer ? 'Active' : 'Not configured')
                ->descriptionIcon($mailer ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle')
                ->color($mailer ? 'success' : 'danger'),
        ];
    }
}
