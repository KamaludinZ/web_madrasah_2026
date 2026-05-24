<?php
namespace App\Filament\Resources\GtkResource\Pages;
use App\Filament\Resources\GtkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListGtk extends ListRecords {
    protected static string $resource = GtkResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->label('Tambah GTK')]; }
}
