<?php
namespace App\Filament\Resources\ArchiveResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditArchive extends EditRecord {
    protected static string $resource = ArchiveResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
