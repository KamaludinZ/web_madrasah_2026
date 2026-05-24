<?php
namespace App\Filament\Resources\GtkResource\Pages;
use App\Filament\Resources\GtkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditGtk extends EditRecord {
    protected static string $resource = GtkResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
