<?php
namespace App\Filament\Resources\PageContentResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\PageContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditPageContent extends EditRecord {
    protected static string $resource = PageContentResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
