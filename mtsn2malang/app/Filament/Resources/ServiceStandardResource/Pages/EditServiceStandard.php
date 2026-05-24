<?php
namespace App\Filament\Resources\ServiceStandardResource\Pages;
use App\Filament\Resources\ServiceStandardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditServiceStandard extends EditRecord {
    protected static string $resource = ServiceStandardResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
