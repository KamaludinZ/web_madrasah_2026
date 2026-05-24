<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GtkResource\Pages;
use App\Models\Gtk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GtkResource extends Resource
{
    protected static ?string $model = Gtk::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Daftar GTK';
    protected static ?string $navigationGroup = 'Profil Sekolah';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('photo')->label('Foto')->image()->directory('gtk')->imageEditor()->circular(),
            Forms\Components\TextInput::make('name')->label('Nama Lengkap')->required(),
            Forms\Components\TextInput::make('nip')->label('NIP'),
            Forms\Components\TextInput::make('position')->label('Jabatan')->required(),
            Forms\Components\Select::make('type')->label('Jenis')->options(['guru' => 'Guru', 'tendik' => 'Tenaga Kependidikan'])->required(),
            Forms\Components\TextInput::make('subject')->label('Mata Pelajaran'),
            Forms\Components\TextInput::make('education')->label('Pendidikan Terakhir'),
            Forms\Components\TextInput::make('order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')->label('Foto')->circular(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('position')->label('Jabatan'),
                Tables\Columns\BadgeColumn::make('type')->label('Jenis')->colors(['success' => 'guru', 'info' => 'tendik']),
                Tables\Columns\TextColumn::make('subject')->label('Mapel'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
            ])
            ->filters([Tables\Filters\SelectFilter::make('type')->options(['guru' => 'Guru', 'tendik' => 'Tenaga Kependidikan'])])
            ->reorderable('order')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGtk::route('/'),
            'create' => Pages\CreateGtk::route('/create'),
            'edit' => Pages\EditGtk::route('/{record}/edit'),
        ];
    }
}
