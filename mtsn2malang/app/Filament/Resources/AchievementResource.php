<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\AchievementResource\Pages\ListAchievements;
use App\Filament\Resources\AchievementResource\Pages\CreateAchievement;
use App\Filament\Resources\AchievementResource\Pages\EditAchievement;
use App\Filament\Resources\AchievementResource\Pages;
use App\Models\Achievement;
use App\Support\UploadImageHelper;
use Filament\Forms;
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
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Prestasi';
    protected static string|\UnitEnum|null $navigationGroup = 'Profil Sekolah';
    protected static ?int    $navigationSort  = 4;
    protected static ?string $modelLabel      = 'Prestasi';
    protected static ?string $pluralModelLabel = 'Prestasi Siswa';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([

                Section::make('Foto / Sertifikat')
                    ->schema([
                        UploadImageHelper::webpFileUpload('photo', 'Upload Foto', 'achievements')
                            ->helperText('Foto piala, sertifikat, atau dokumentasi · Maks 5MB'),
                    ]),

            ])->columnSpan(1),

            Group::make()->schema([

                Section::make('Data Prestasi')
                    ->schema([
                        TextInput::make('student_name')
                            ->label('Nama Siswa')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama lengkap siswa'),

                        TextInput::make('competition_name')
                            ->label('Nama Lomba / Kompetisi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Olimpiade Matematika Nasional'),

                        Select::make('level')
                            ->label('Tingkat')
                            ->options([
                                'regional'      => 'Regional / Kota / Kabupaten',
                                'provinsi'      => 'Provinsi',
                                'nasional'      => 'Nasional',
                                'internasional' => 'Internasional',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('rank')
                            ->label('Peringkat / Juara')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Contoh: Juara 1, Juara Harapan, Medali Emas'),

                        TextInput::make('organizer')
                            ->label('Penyelenggara')
                            ->maxLength(255)
                            ->placeholder('Nama instansi penyelenggara'),

                        TextInput::make('academic_year')
                            ->label('Tahun Pelajaran')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('Contoh: 2024/2025'),

                        DatePicker::make('achievement_date')
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
                ImageColumn::make('photo')->label('Foto')->width(60)->height(60),
                TextColumn::make('student_name')->label('Siswa')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('competition_name')->label('Lomba')->searchable()->limit(40),
                TextColumn::make('level')->label('Tingkat')->badge()->color(fn ($state): string => match($state) {
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
                TextColumn::make('rank')->label('Juara')->badge()->color('warning'),
                TextColumn::make('academic_year')->label('T.P.')->sortable(),
            ])
            ->filters([
                SelectFilter::make('level')->options([
                    'regional' => 'Regional', 'provinsi' => 'Provinsi',
                    'nasional' => 'Nasional', 'internasional' => 'Internasional',
                ]),
                SelectFilter::make('academic_year')->label('Tahun Pelajaran')
                    ->options(fn () => Achievement::distinct()->pluck('academic_year', 'academic_year')->toArray()),
            ])
            ->defaultSort('achievement_date', 'desc')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAchievements::route('/'),
            'create' => CreateAchievement::route('/create'),
            'edit'   => EditAchievement::route('/{record}/edit'),
        ];
    }
}
