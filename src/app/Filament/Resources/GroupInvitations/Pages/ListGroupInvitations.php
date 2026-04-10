<?php

namespace App\Filament\Resources\GroupInvitations\Pages;

use App\Filament\Resources\GroupInvitations\GroupInvitationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGroupInvitations extends ListRecords
{
    protected static string $resource = GroupInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
