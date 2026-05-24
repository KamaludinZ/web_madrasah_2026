<?php
namespace App\Filament\Resources;
use App\Filament\Resources\AchievementResource\Pages;
use App\Models\Achievement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AchievementResource extends Resource {
    protected static ?string $model = Achievement::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Prestasi';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\TextInput::make('student_name')->label('Nama Siswa')->required(),
            Forms\Components\TextInput::make('competition_name')->label('Nama Lomba')->required(),
            Forms\Components\Select::make('level')->label('Tingkat')->options(['regional' => 'Regional', 'provinsi' => 'Provinsi', 'nasional' => 'Nasional', 'internasional' => 'Internasional'])->required(),
            Forms\Components\TextInput::make('rank')->label('Perolehan Juara')->required()->placeholder('Juara 1, Juara 2, Harapan 1, dll'),
            Forms\Components\TextInput::make('academic_year')->label('Tahun Pelajaran')->required()->placeholder('2024/2025'),
            Forms\Components\DatePicker::make('achievement_date')->label('Tanggal'),
            Forms\Components\TextInput::make('organizer')->label('Penyelenggara'),
            Forms\Components\FileUpload::make('photo')->label('Foto')->image()->directory('achievements'),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('student_name')->label('Nama Siswa')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('competition_name')->label('Nama Lomba')->limit(40),
            Tables\Columns\BadgeColumn::make('level')->label('Tingkat')->colors(['info' => 'regional', 'warning' => 'provinsi', 'success' => 'nasional', 'danger' => 'internasional']),
            Tables\Columns\TextColumn::make('rank')->label('Juara'),
            Tables\Columns\TextColumn::make('academic_year')->label('Tahun Pelajaran'),
            Tables\Columns\TextColumn::make('achievement_date')->label('Tanggal')->date('d M Y')->sortable(),
        ])
        ->filters([Tables\Filters\SelectFilter::make('level')->options(['regional' => 'Regional', 'provinsi' => 'Provinsi', 'nasional' => 'Nasional', 'internasional' => 'Internasional']), Tables\Filters\SelectFilter::make('academic_year')->options(Achievement::distinct()->pluck('academic_year', 'academic_year')->toArray())])
        ->defaultSort('achievement_date', 'desc')
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array {
        return ['index' => Pages\ListAchievements::route('/'), 'create' => Pages\CreateAchievement::route('/create'), 'edit' => Pages\EditAchievement::route('/{record}/edit')];
    }
}
