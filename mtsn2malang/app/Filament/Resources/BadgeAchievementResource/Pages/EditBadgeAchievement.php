<?php

namespace App\Filament\Resources\BadgeAchievementResource\Pages;

use App\Filament\Resources\BadgeAchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBadgeAchievement extends EditRecord
{
    protected static string $resource = BadgeAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
