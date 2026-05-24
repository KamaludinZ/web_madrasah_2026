<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProgramResource extends Resource {
    protected static ?string $model = Program::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Program';
    protected static ?string $navigationGroup = 'Program';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Select::make('type')->label('Jenis Program')->options(['unggulan' => 'Program Unggulan', 'mahad' => "Program Ma'had"])->required(),
            Forms\Components\FileUpload::make('cover')->label('Gambar Cover')->image()->directory('programs')->imageEditor(),
            Forms\Components\TextInput::make('name')->label('Nama Program')->required(),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(4),
            Forms\Components\TextInput::make('order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('cover')->label('Cover')->width(80)->height(60),
            Tables\Columns\TextColumn::make('name')->label('Nama Program')->searchable(),
            Tables\Columns\BadgeColumn::make('type')->label('Jenis')->colors(['success' => 'unggulan', 'info' => 'mahad']),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
        ])
        ->reorderable('order')
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array {
        return ['index' => Pages\ListPrograms::route('/'), 'create' => Pages\CreateProgram::route('/create'), 'edit' => Pages\EditProgram::route('/{record}/edit')];
    }
}
