<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ExtracurricularResource\Pages;
use App\Models\Extracurricular;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExtracurricularResource extends Resource {
    protected static ?string $model = Extracurricular::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Ekstrakurikuler';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\FileUpload::make('photo')->label('Foto')->image()->directory('ekskul')->imageEditor(),
            Forms\Components\TextInput::make('name')->label('Nama Ekstrakurikuler')->required(),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(3),
            Forms\Components\TextInput::make('schedule')->label('Jadwal'),
            Forms\Components\TextInput::make('coach')->label('Pembina/Pelatih'),
            Forms\Components\TextInput::make('order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('photo')->label('Foto')->width(60)->height(60),
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('schedule')->label('Jadwal'),
            Tables\Columns\TextColumn::make('coach')->label('Pembina'),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
        ])
        ->reorderable('order')
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array {
        return ['index' => Pages\ListExtracurriculars::route('/'), 'create' => Pages\CreateExtracurricular::route('/create'), 'edit' => Pages\EditExtracurricular::route('/{record}/edit')];
    }
}
