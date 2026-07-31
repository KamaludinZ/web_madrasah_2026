<?php

namespace App\Filament\Resources\SurveyLinkResource\Pages;

use App\Filament\Resources\SurveyLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSurveyLinks extends ListRecords
{
    protected static string $resource = SurveyLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
