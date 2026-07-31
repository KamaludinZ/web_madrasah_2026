<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveiResource\Pages;
use App\Models\SurveyResponse;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Hasil Survei';
    protected static ?string $navigationGroup = 'Pengaduan & Survei';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Respons Survei';
    protected static ?string $pluralModelLabel = 'Hasil Survei';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Responden')->schema([
                Forms\Components\Select::make('type')
                    ->label('Jenis Survei')
                    ->options(['skm' => 'SKM (Survei Kepuasan Masyarakat)', 'spk' => 'SPK (Survei Persepsi Korupsi)'])
                    ->disabled(),
                Forms\Components\TextInput::make('respondent_name')
                    ->label('Nama Responden')
                    ->default('Anonim')
                    ->disabled(),
                Forms\Components\TextInput::make('respondent_type')
                    ->label('Jenis Responden')
                    ->disabled(),
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->disabled(),
            ])->columns(2),
            Forms\Components\Section::make('Jawaban Survei')->schema([
                Forms\Components\KeyValue::make('answers')
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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis Survei')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'skm' => 'SKM',
                        'spk' => 'SPK',
                        default => strtoupper($state),
                    }),
                Tables\Columns\TextColumn::make('respondent_name')
                    ->label('Responden')
                    ->default('Anonim')
                    ->searchable(),
                Tables\Columns\TextColumn::make('respondent_type')
                    ->label('Jenis Responden')
                    ->default('-'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Survei')
                    ->options(['skm' => 'SKM', 'spk' => 'SPK']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
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
            'index' => Pages\ListSurveis::route('/'),
            'view'  => Pages\ViewSurvei::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
