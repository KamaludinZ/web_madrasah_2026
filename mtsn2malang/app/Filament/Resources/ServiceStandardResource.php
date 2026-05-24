<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ServiceStandardResource\Pages;
use App\Models\ServiceStandard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceStandardResource extends Resource {
    protected static ?string $model = ServiceStandard::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Standar Pelayanan';
    protected static ?string $navigationGroup = 'Layanan & PPID';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama Pelayanan')->required(),
            Forms\Components\Repeater::make('requirements')->label('Persyaratan')->schema([
                Forms\Components\TextInput::make('no')->label('No.')->numeric()->required(),
                Forms\Components\TextInput::make('komponen')->label('Komponen')->required(),
                Forms\Components\Textarea::make('uraian')->label('Uraian')->rows(2)->required(),
            ])->columns(3)->addActionLabel('Tambah Persyaratan'),
            Forms\Components\Repeater::make('procedures')->label('Prosedur')->schema([
                Forms\Components\TextInput::make('no')->label('No.')->numeric()->required(),
                Forms\Components\TextInput::make('komponen')->label('Komponen')->required(),
                Forms\Components\Textarea::make('uraian')->label('Uraian')->rows(2)->required(),
            ])->columns(3)->addActionLabel('Tambah Prosedur'),
            Forms\Components\TextInput::make('service_time')->label('Waktu Pelayanan'),
            Forms\Components\TextInput::make('cost')->label('Biaya/Tarif'),
            Forms\Components\TextInput::make('product')->label('Produk Layanan'),
            Forms\Components\Textarea::make('complaint_handling')->label('Pengelolaan Pengaduan')->rows(3),
            Forms\Components\TextInput::make('order')->label('Urutan')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Nama Pelayanan')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('service_time')->label('Waktu'),
            Tables\Columns\TextColumn::make('cost')->label('Biaya'),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('order')->label('Urutan')->sortable(),
        ])
        ->reorderable('order')
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array {
        return ['index' => Pages\ListServiceStandards::route('/'), 'create' => Pages\CreateServiceStandard::route('/create'), 'edit' => Pages\EditServiceStandard::route('/{record}/edit')];
    }
}
