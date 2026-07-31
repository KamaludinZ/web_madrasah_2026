<?php

namespace App\Filament\Resources\BadgeAchievementResource\Pages;

use App\Filament\Resources\BadgeAchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBadgeAchievements extends ListRecords
{
    protected static string $resource = BadgeAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
