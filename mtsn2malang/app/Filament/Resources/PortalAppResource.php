<?php
namespace App\Filament\Resources;
use App\Filament\Resources\PortalAppResource\Pages;
use App\Models\PortalApp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PortalAppResource extends Resource {
    protected static ?string $model = PortalApp::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Portal Aplikasi';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\FileUpload::make('logo')->label('Logo')->image()->directory('portal-apps'),
            Forms\Components\TextInput::make('name')->label('Nama Aplikasi')->required(),
            Forms\Components\TextInput::make('link')->label('Link Aplikasi')->url()->required(),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(2),
            Forms\Components\TextInput::make('order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('logo')->label('Logo')->width(50)->height(50),
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
            Tables\Columns\TextColumn::make('link')->label('Link')->limit(40),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
        ])
        ->reorderable('order')
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array {
        return ['index' => Pages\ListPortalApps::route('/'), 'create' => Pages\CreatePortalApp::route('/create'), 'edit' => Pages\EditPortalApp::route('/{record}/edit')];
    }
}
