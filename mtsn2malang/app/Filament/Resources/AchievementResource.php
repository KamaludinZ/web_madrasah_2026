<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AchievementResource\Pages;
use App\Models\Achievement;
use App\Support\UploadImageHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\HasRoleAccess;

class AchievementResource extends Resource
{
    use HasRoleAccess;

    public static function canViewAny(): bool
    {
        return static::currentUserHasRole(['editor_in_chief', 'editor']);
    }

    protected static ?string $model           = Achievement::class;
    protected static ?string $navigationIcon  = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Prestasi';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $modelLabel      = 'Prestasi';
    protected static ?string $pluralModelLabel = 'Prestasi Siswa';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Foto / Sertifikat')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'achievements')
                            ->helperText('Foto piala, sertifikat, atau dokumentasi · Maks 5MB'),
                    ]),

            ])->columnSpan(1),

            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Data Prestasi')
                    ->schema([
                        Forms\Components\TextInput::make('student_name')
                            ->label('Nama Siswa')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama lengkap siswa'),

                        Forms\Components\TextInput::make('competition_name')
                            ->label('Nama Lomba / Kompetisi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Olimpiade Matematika Nasional'),

                        Forms\Components\Select::make('level')
                            ->label('Tingkat')
                            ->options([
                                'regional'      => 'Regional / Kota / Kabupaten',
                                'provinsi'      => 'Provinsi',
                                'nasional'      => 'Nasional',
                                'internasional' => 'Internasional',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('rank')
                            ->label('Peringkat / Juara')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Contoh: Juara 1, Juara Harapan, Medali Emas'),

                        Forms\Components\TextInput::make('organizer')
                            ->label('Penyelenggara')
                            ->maxLength(255)
                            ->placeholder('Nama instansi penyelenggara'),

                        Forms\Components\TextInput::make('academic_year')
                            ->label('Tahun Pelajaran')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('Contoh: 2024/2025'),

                        Forms\Components\DatePicker::make('achievement_date')
                            ->label('Tanggal Prestasi')
                            ->displayFormat('d M Y')
                            ->native(false),
                    ])->columns(2),

            ])->columnSpan(2),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->label('Foto')->width(60)->height(60),
                Tables\Columns\TextColumn::make('student_name')->label('Siswa')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('competition_name')->label('Lomba')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('level')->label('Tingkat')->badge()->color(fn ($state): string => match($state) {
                    'regional'      => 'info',
                    'provinsi'      => 'warning',
                    'nasional'      => 'success',
                    'internasional' => 'danger',
                    default         => 'gray',
                })->formatStateUsing(fn ($state) => match ($state) {
                    'regional' => 'Regional', 'provinsi' => 'Provinsi',
                    'nasional' => 'Nasional', 'internasional' => 'Internasional',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('rank')->label('Juara')->badge()->color('warning'),
                Tables\Columns\TextColumn::make('academic_year')->label('T.P.')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')->options([
                    'regional' => 'Regional', 'provinsi' => 'Provinsi',
                    'nasional' => 'Nasional', 'internasional' => 'Internasional',
                ]),
                Tables\Filters\SelectFilter::make('academic_year')->label('Tahun Pelajaran')
                    ->options(fn () => Achievement::distinct()->pluck('academic_year', 'academic_year')->toArray()),
            ])
            ->defaultSort('achievement_date', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'edit'   => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }
}
