<?php
namespace App\Filament\Resources;
use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RegistrationResource extends Resource {
    protected static ?string $model = Registration::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'PPDB & PSB';
    protected static ?string $navigationGroup = 'Program';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Select::make('type')->label('Jenis')->options(['ppdb' => 'PPDB (Penerimaan Peserta Didik Baru)', 'psb' => "PSB (Penerimaan Santri Baru Ma'had)"])->required(),
            Forms\Components\Repeater::make('schedule')->label('Jadwal Pendaftaran')->schema([
                Forms\Components\TextInput::make('kegiatan')->label('Kegiatan')->required(),
                Forms\Components\TextInput::make('tanggal')->label('Tanggal/Periode')->required(),
                Forms\Components\TextInput::make('keterangan')->label('Keterangan'),
            ])->columns(3)->addActionLabel('Tambah Jadwal'),
            Forms\Components\RichEditor::make('general_requirements')->label('Persyaratan Umum'),
            Forms\Components\RichEditor::make('specific_requirements')->label('Persyaratan Khusus'),
            Forms\Components\FileUpload::make('brochure_file')->label('Upload Brosur (PDF)')->directory('registrations')->acceptedFileTypes(['application/pdf', 'image/*']),
            Forms\Components\TextInput::make('brochure_link')->label('Link Brosur')->url(),
            Forms\Components\TextInput::make('registration_link')->label('Link Pendaftaran Online')->url(),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\BadgeColumn::make('type')->label('Jenis')->colors(['success' => 'ppdb', 'info' => 'psb']),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('updated_at')->label('Terakhir Diubah')->dateTime('d M Y H:i')->sortable(),
        ])
        ->actions([Tables\Actions\EditAction::make()])
        ->bulkActions([]);
    }

    public static function getPages(): array {
        return ['index' => Pages\ListRegistrations::route('/'), 'create' => Pages\CreateRegistration::route('/create'), 'edit' => Pages\EditRegistration::route('/{record}/edit')];
    }
}
