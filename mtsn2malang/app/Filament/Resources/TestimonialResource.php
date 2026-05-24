<?php
namespace App\Filament\Resources;
use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource {
    protected static ?string $model = Testimonial::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationLabel = 'Testimoni Alumni';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\FileUpload::make('photo')->label('Foto')->image()->directory('testimonials')->circular(),
            Forms\Components\TextInput::make('name')->label('Nama')->required(),
            Forms\Components\TextInput::make('graduation_year')->label('Tahun Lulus')->required()->maxLength(4),
            Forms\Components\Textarea::make('content')->label('Testimoni')->required()->rows(4),
            Forms\Components\TextInput::make('current_school')->label('Sekolah/Kampus Saat Ini'),
            Forms\Components\TextInput::make('order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('photo')->label('Foto')->circular(),
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
            Tables\Columns\TextColumn::make('graduation_year')->label('Lulus'),
            Tables\Columns\TextColumn::make('content')->label('Testimoni')->limit(60),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
        ])
        ->reorderable('order')
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array {
        return ['index' => Pages\ListTestimonials::route('/'), 'create' => Pages\CreateTestimonial::route('/create'), 'edit' => Pages\EditTestimonial::route('/{record}/edit')];
    }
}
