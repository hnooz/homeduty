<?php

namespace App\Filament\Widgets;

use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Groups', Group::query()->count()),
            Stat::make('Total Members', User::query()->count()),
            Stat::make('Pending Invitations', GroupInvitation::query()->pending()->count()),
        ];
    }
}
