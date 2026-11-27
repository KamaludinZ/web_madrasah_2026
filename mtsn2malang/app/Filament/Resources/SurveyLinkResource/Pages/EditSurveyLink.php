<?php

namespace App\Filament\Resources\SurveyLinkResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\SurveyLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSurveyLink extends EditRecord
{
    protected static string $resource = SurveyLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
