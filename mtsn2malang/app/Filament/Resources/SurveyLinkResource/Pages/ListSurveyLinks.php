<?php

namespace App\Filament\Resources\SurveyLinkResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\SurveyLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSurveyLinks extends ListRecords
{
    protected static string $resource = SurveyLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
