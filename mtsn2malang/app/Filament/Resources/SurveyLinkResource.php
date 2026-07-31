<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyLinkResource\Pages;
use App\Models\SurveyLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class SurveyLinkResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'moderator']);
    }

    protected static ?string $model = SurveyLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationLabel = 'Link Survei';
    protected static ?string $navigationGroup = 'Pengaduan & Survei';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Survei Eksternal')->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Judul Survei')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('url')
                        ->label('Link Survei')
                        ->required()
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://...'),
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi (Opsional)')
                        ->rows(3)
                        ->maxLength(1000),
                    Forms\Components\TextInput::make('order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(0)
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Survei')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('Link')
                    ->limit(50)
                    ->copyable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSurveyLinks::route('/'),
            'create' => Pages\CreateSurveyLink::route('/create'),
            'edit' => Pages\EditSurveyLink::route('/{record}/edit'),
        ];
    }
}
