<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\SurveiResource\Pages\ListSurveis;
use App\Filament\Resources\SurveiResource\Pages\ViewSurvei;
use App\Filament\Resources\SurveiResource\Pages;
use App\Models\SurveyResponse;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class SurveiResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'moderator']);
    }

    protected static ?string $model = SurveyResponse::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Hasil Survei';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaduan & Survei';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Respons Survei';
    protected static ?string $pluralModelLabel = 'Hasil Survei';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Responden')->schema([
                Select::make('type')
                    ->label('Jenis Survei')
                    ->options(['skm' => 'SKM (Survei Kepuasan Masyarakat)', 'spk' => 'SPK (Survei Persepsi Korupsi)'])
                    ->disabled(),
                TextInput::make('respondent_name')
                    ->label('Nama Responden')
                    ->default('Anonim')
                    ->disabled(),
                TextInput::make('respondent_type')
                    ->label('Jenis Responden')
                    ->disabled(),
                TextInput::make('ip_address')
                    ->label('IP Address')
                    ->disabled(),
            ])->columns(2),
            Section::make('Jawaban Survei')->schema([
                KeyValue::make('answers')
                    ->label('Jawaban')
                    ->keyLabel('Pertanyaan')
                    ->valueLabel('Jawaban')
                    ->disabled(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Jenis Survei')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'skm' => 'SKM',
                        'spk' => 'SPK',
                        default => strtoupper($state),
                    }),
                TextColumn::make('respondent_name')
                    ->label('Responden')
                    ->default('Anonim')
                    ->searchable(),
                TextColumn::make('respondent_type')
                    ->label('Jenis Responden')
                    ->default('-'),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis Survei')
                    ->options(['skm' => 'SKM', 'spk' => 'SPK']),
            ])
            ->actions([
                ViewAction::make()->label('Lihat'),
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
            'index' => ListSurveis::route('/'),
            'view'  => ViewSurvei::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
