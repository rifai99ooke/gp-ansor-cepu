<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\RegistrationForm;
use App\Services\GoogleFormUrlNormalizer;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @property-read Schema $form
 */
class RegistrationPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Pendaftaran';

    protected static ?string $title = 'Formulir Pendaftaran';

    protected static ?string $slug = 'pendaftaran';

    protected static ?int $navigationSort = 7;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $record = $this->getRegistrationForm();

        if ($record) {
            $this->form->fill([
                'title' => $record->title,
                'cover_image' => $record->cover_image,
                'description' => $record->description,
                'form_url' => $record->form_url,
                'responses_url' => $record->responses_url,
                'opens_at' => $record->opens_at?->toDateTimeString(),
                'closes_at' => $record->closes_at?->toDateTimeString(),
                'is_active' => $record->is_active,
            ]);
        } else {
            $this->form->fill([
                'title' => 'Pendaftaran Kader GP Ansor',
                'is_active' => true,
            ]);
        }
    }

    protected function getRegistrationForm(): ?RegistrationForm
    {
        return once(static fn (): ?RegistrationForm => RegistrationForm::query()->ordered()->first());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_responses')
                ->label('Lihat Tanggapan (Google Sheets)')
                ->icon(Heroicon::OutlinedTableCells)
                ->color('gray')
                ->url(fn (): ?string => $this->getRegistrationForm()?->responses_url, shouldOpenInNewTab: true)
                ->visible(fn (): bool => filled($this->getRegistrationForm()?->responses_url)),

            Action::make('view_public')
                ->label('Lihat Halaman Publik')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('primary')
                ->outlined()
                ->url(fn (): string => route('pendaftaran.index'), shouldOpenInNewTab: true),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Konfigurasi Formulir Pendaftaran')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Pendaftaran')
                            ->placeholder('Contoh: Pendaftaran Kader GP Ansor')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Buka Pendaftaran (Aktif)')
                            ->default(true)
                            ->inline(false),

                        DateTimePicker::make('opens_at')
                            ->label('Tanggal Mulai Dibuka (Opsional)'),

                        DateTimePicker::make('closes_at')
                            ->label('Tanggal Ditutup (Opsional)'),

                        FileUpload::make('cover_image')
                            ->label('Gambar Sampul (Opsional)')
                            ->disk('public')
                            ->directory('forms')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(4096)
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('Deskripsi & Ketentuan Pendaftaran')
                            ->columnSpanFull(),

                        Textarea::make('form_url')
                            ->label('Tautan Google Form / Kode Sematan <iframe>')
                            ->required()
                            ->rows(3)
                            ->rules([
                                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                    try {
                                        app(GoogleFormUrlNormalizer::class)->normalize((string) $value);
                                    } catch (ValidationException $e) {
                                        $fail($e->validator->errors()->first() ?: 'Tautan Google Form tidak valid.');
                                    }
                                },
                            ])
                            ->columnSpanFull(),

                        TextInput::make('responses_url')
                            ->label('Tautan Google Sheets Tanggapan (Pintasan Admin)')
                            ->url()
                            ->placeholder('https://docs.google.com/spreadsheets/d/...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Simpan Formulir Pendaftaran')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        DB::transaction(function () use ($state): void {
            $record = RegistrationForm::query()->ordered()->first() ?? new RegistrationForm;
            $record->fill([
                'title' => $state['title'],
                'cover_image' => $state['cover_image'] ?? null,
                'description' => $state['description'] ?? null,
                'form_url' => $state['form_url'],
                'responses_url' => $state['responses_url'] ?? null,
                'opens_at' => $state['opens_at'] ?? null,
                'closes_at' => $state['closes_at'] ?? null,
                'is_active' => (bool) ($state['is_active'] ?? true),
                'sort_order' => 1,
            ]);
            $record->save();

            RegistrationForm::query()->whereKeyNot($record->id)->eachById(function (RegistrationForm $redundantForm): void {
                $redundantForm->delete();
            });
        });

        Notification::make()
            ->title('Formulir pendaftaran berhasil disimpan.')
            ->success()
            ->send();
    }
}
