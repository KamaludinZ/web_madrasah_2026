<?php
namespace App\Filament\Resources;
use App\Filament\Resources\FacilityResource\Pages;
use App\Models\Facility;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FacilityResource extends Resource {
    protected static ?string $model = Facility::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Sarpras';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\FileUpload::make('image')->label('Foto')->image()->required()->directory('facilities')->imageEditor(),
            Forms\Components\TextInput::make('name')->label('Nama Sarpras')->required(),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(2),
            Forms\Components\Select::make('category')->label('Kategori')->options(['madrasah' => 'Madrasah', 'mahad' => "Ma'had", 'olahraga' => 'Olahraga', 'laboratorium' => 'Laboratorium', 'lainnya' => 'Lainnya'])->default('madrasah'),
            Forms\Components\TextInput::make('order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->label('Foto')->width(80)->height(60),
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category')->label('Kategori'),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
        ])
        ->reorderable('order')
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array {
        return ['index' => Pages\ListFacilities::route('/'), 'create' => Pages\CreateFacility::route('/create'), 'edit' => Pages\EditFacility::route('/{record}/edit')];
    }
}
