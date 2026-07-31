<?php

namespace App\Filament\Resources\AchievementResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\AchievementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAchievement extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = AchievementResource::class;
}
