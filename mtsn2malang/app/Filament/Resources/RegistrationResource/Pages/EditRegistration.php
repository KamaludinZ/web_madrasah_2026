<?php
namespace App\Filament\Resources\RegistrationResource\Pages;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\RegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditRegistration extends EditRecord {
    protected static string $resource = RegistrationResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
