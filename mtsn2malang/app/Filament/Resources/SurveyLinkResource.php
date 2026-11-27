<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\SurveyLinkResource\Pages\ListSurveyLinks;
use App\Filament\Resources\SurveyLinkResource\Pages\CreateSurveyLink;
use App\Filament\Resources\SurveyLinkResource\Pages\EditSurveyLink;
use App\Filament\Resources\SurveyLinkResource\Pages;
use App\Models\SurveyLink;
use Filament\Forms;
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

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationLabel = 'Link Survei';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaduan & Survei';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Survei Eksternal')->schema([
                    TextInput::make('title')
                        ->label('Judul Survei')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('url')
                        ->label('Link Survei')
                        ->required()
                        ->url()
                        ->maxLength(255)
                        ->placeholder('https://...'),
                    Textarea::make('description')
                        ->label('Deskripsi (Opsional)')
                        ->rows(3)
                        ->maxLength(1000),
                    TextInput::make('order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(0)
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Survei')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url')
                    ->label('Link')
                    ->limit(50)
                    ->copyable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSurveyLinks::route('/'),
            'create' => CreateSurveyLink::route('/create'),
            'edit' => EditSurveyLink::route('/{record}/edit'),
        ];
    }
}
