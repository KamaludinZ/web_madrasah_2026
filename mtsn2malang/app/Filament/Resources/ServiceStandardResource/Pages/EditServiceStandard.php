<?php
namespace App\Filament\Resources\ServiceStandardResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ServiceStandardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditServiceStandard extends EditRecord {
    protected static string $resource = ServiceStandardResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
