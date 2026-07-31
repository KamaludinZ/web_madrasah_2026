<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortalAppResource\Pages;
use App\Models\PortalApp;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon  = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Portal Aplikasi';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $modelLabel      = 'Portal Aplikasi';
    protected static ?string $pluralModelLabel = 'Portal Aplikasi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Logo Aplikasi')
                    ->schema([
                        UploadImageHelper::webpFileUpload('logo', 'Upload Logo', 'portal-apps')
                            ->helperText('PNG/SVG transparan direkomendasikan · Maks 2MB'),
                    ]),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])->columns(2),

            ])->columnSpan(1),

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Informasi Aplikasi')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Aplikasi')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: SIMPATIKA, e-Rapor, PPDB Online'),

                        Forms\Components\TextInput::make('link')
                            ->label('URL Aplikasi')
                            ->required()
                            ->url()
                            ->placeholder('https://...')
                            ->helperText('URL lengkap termasuk https://'),

                        Forms\Components\Textarea::make('description')
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
                Tables\Columns\ImageColumn::make('logo')->label('Logo')->width(48)->height(48),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('link')->label('URL')->limit(40)->url(fn ($record) => $record->link)->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->reorderable('order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPortalApps::route('/'),
            'create' => Pages\CreatePortalApp::route('/create'),
            'edit'   => Pages\EditPortalApp::route('/{record}/edit'),
        ];
    }
}
