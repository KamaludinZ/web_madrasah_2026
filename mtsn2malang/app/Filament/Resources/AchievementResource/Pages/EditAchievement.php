<?php
namespace App\Filament\Resources\AchievementResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\AchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditAchievement extends EditRecord {
    protected static string $resource = AchievementResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
