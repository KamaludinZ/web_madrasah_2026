<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PortalAppResource\Pages\ListPortalApps;
use App\Filament\Resources\PortalAppResource\Pages\CreatePortalApp;
use App\Filament\Resources\PortalAppResource\Pages\EditPortalApp;
use App\Filament\Resources\PortalAppResource\Pages;
use App\Models\PortalApp;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class PortalAppResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief']);
    }

    protected static ?string $model           = PortalApp::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Portal Aplikasi';
    protected static string|\UnitEnum|null $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $modelLabel      = 'Portal Aplikasi';
    protected static ?string $pluralModelLabel = 'Portal Aplikasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Logo Aplikasi')
                    ->schema([
                        UploadImageHelper::webpFileUpload('logo', 'Upload Logo', 'portal-apps')
                            ->helperText('PNG/SVG transparan direkomendasikan · Maks 2MB'),
                    ]),

                Section::make('Pengaturan')
                    ->schema([
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Group::make()->schema([

                Section::make('Informasi Aplikasi')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Aplikasi')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: SIMPATIKA, e-Rapor, PPDB Online'),

                        TextInput::make('link')
                            ->label('URL Aplikasi')
                            ->required()
                            ->url()
                            ->placeholder('https://...')
                            ->helperText('URL lengkap termasuk https://'),

                        Textarea::make('description')
                            ->label('Deskripsi Singkat')
                            ->rows(3)
                            ->maxLength(300)
                            ->placeholder('Deskripsi singkat fungsi aplikasi ini...'),
                    ]),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')->label('Logo')->width(48)->height(48),
                TextColumn::make('name')->label('Nama')->searchable()->weight('semibold'),
                TextColumn::make('link')->label('URL')->limit(40)->url(fn ($record) => $record->link)->openUrlInNewTab(),
                TextColumn::make('order')->label('Urutan')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->reorderable('order')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPortalApps::route('/'),
            'create' => CreatePortalApp::route('/create'),
            'edit'   => EditPortalApp::route('/{record}/edit'),
        ];
    }
}
