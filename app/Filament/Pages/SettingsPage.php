<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;

/**
 * @property-read Schema $form
 */
class SettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $title = 'Pengaturan Situs';

    protected static ?string $slug = 'pengaturan';

    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.settings-page';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    /**
     * @var array<string, mixed>
     */
    #[Locked]
    public array $savedData = [];

    public function mount(): void
    {
        $this->form->fill(Setting::allCached());
        $this->savedData = $this->data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Pengaturan')
                    ->tabs([
                        Tab::make('Umum')
                            ->schema([
                                Section::make('Identitas')
                                    ->contained(false)
                                    ->schema([
                                        TextInput::make('site_name')
                                            ->label('Nama Singkat Situs')
                                            ->required()
                                            ->maxLength(100),

                                        TextInput::make('site_full_name')
                                            ->label('Nama Lengkap Organisasi')
                                            ->required()
                                            ->maxLength(150),

                                        Textarea::make('site_tagline')
                                            ->label('Deskripsi Singkat Organisasi')
                                            ->rows(2)
                                            ->maxLength(300)
                                            ->helperText('Digunakan sebagai deskripsi profil di footer website, pratinjau media sosial, dan mesin pencari (SEO).')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Section::make('Logo & Ikon')
                                    ->contained(false)
                                    ->schema([
                                        FileUpload::make('site_logo')
                                            ->label('Logo Organisasi')
                                            ->disk('public')
                                            ->directory('branding')
                                            ->acceptedFileTypes(['image/png', 'image/webp', 'image/jpeg'])
                                            ->maxSize(4096)
                                            ->imagePreviewHeight('160')
                                            ->extraAttributes(['class' => 'gp-settings-logo-upload'])
                                            ->helperText('Format PNG, WebP, atau JPG (maks. 4 MB).'),

                                        FileUpload::make('site_favicon')
                                            ->label('Ikon Situs (Favicon)')
                                            ->disk('public')
                                            ->directory('branding')
                                            ->acceptedFileTypes(['image/png', 'image/webp', 'image/x-icon'])
                                            ->maxSize(2048)
                                            ->imagePreviewHeight('32')
                                            ->extraAttributes(['class' => 'gp-settings-favicon-upload'])
                                            ->helperText('Ditampilkan di tab browser. Pratinjau setinggi 32 px. Format PNG, ICO, atau WebP (maks. 2 MB).'),
                                    ])
                                    ->columns(2),

                                Section::make('Footer')
                                    ->contained(false)
                                    ->schema([
                                        TextInput::make('footer_copyright')
                                            ->label('Teks Hak Cipta')
                                            ->placeholder('GP Ansor. All rights reserved.')
                                            ->helperText('Simbol © dan tahun berjalan ditambahkan otomatis. Cukup isi nama organisasi dan keterangan hak cipta.')
                                            ->maxLength(200)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(1),

                        Tab::make('Kontak')
                            ->schema([
                                Textarea::make('contact_address')
                                    ->label('Alamat Sekretariat')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),

                                TextInput::make('address_locality')
                                    ->label('Kota / Kabupaten')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('address_region')
                                    ->label('Provinsi')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('address_postal_code')
                                    ->label('Kode Pos')
                                    ->required()
                                    ->inputMode('numeric')
                                    ->rules(['regex:/^[0-9]{5}$/'])
                                    ->validationMessages([
                                        'required' => 'Kode pos wajib diisi.',
                                        'regex' => 'Kode pos harus terdiri dari 5 digit angka.',
                                    ])
                                    ->helperText('Isi 5 digit kode pos. Kota, provinsi, dan kode pos juga digunakan dalam data alamat untuk mesin pencari.'),

                                Textarea::make('map_embed_url')
                                    ->label('Peta Lokasi Google Maps')
                                    ->rows(2)
                                    ->helperText('Masukkan tautan URL sematan https:// dari Google Maps atau kode <iframe>.')
                                    ->rules([
                                        fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                            $raw = trim((string) $value);
                                            if ($raw === '') {
                                                return;
                                            }
                                            if (! Setting::isAllowedMapEmbedUrl($raw)) {
                                                $fail('URL sematan peta harus menggunakan tautan HTTPS resmi dari Google Maps (/maps...).');
                                            }
                                        },
                                    ])
                                    ->columnSpanFull(),

                                TextInput::make('contact_phone_1')
                                    ->label('Nomor Telepon 1')
                                    ->maxLength(50),

                                TextInput::make('contact_phone_2')
                                    ->label('Nomor Telepon 2')
                                    ->maxLength(50),

                                TextInput::make('contact_email')
                                    ->label('Alamat Email Resmi')
                                    ->email()
                                    ->maxLength(150)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('Media Sosial')
                            ->schema([
                                Fieldset::make('TikTok')
                                    ->schema([
                                        TextInput::make('social_tiktok_handle')
                                            ->label('Nama Akun / Username TikTok')
                                            ->placeholder('@gpansor1934')
                                            ->helperText('Nama akun yang ditampilkan di website (misal: @gpansor1934)')
                                            ->maxLength(100),

                                        TextInput::make('social_tiktok_url')
                                            ->label('Tautan Link Profil TikTok')
                                            ->placeholder('https://www.tiktok.com/@gpansor1934')
                                            ->helperText('URL lengkap yang dituju saat ikon TikTok diklik')
                                            ->url()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Fieldset::make('Facebook')
                                    ->schema([
                                        TextInput::make('social_facebook_handle')
                                            ->label('Nama Halaman / Akun Facebook')
                                            ->placeholder('gerakanpemudaansor')
                                            ->helperText('Nama akun atau halaman yang ditampilkan di website')
                                            ->maxLength(100),

                                        TextInput::make('social_facebook_url')
                                            ->label('Tautan Link Halaman Facebook')
                                            ->placeholder('https://www.facebook.com/gerakanpemudaansor')
                                            ->helperText('URL lengkap yang dituju saat ikon Facebook diklik')
                                            ->url()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Fieldset::make('Instagram')
                                    ->schema([
                                        TextInput::make('social_instagram_handle')
                                            ->label('Username Instagram')
                                            ->placeholder('gp.ansor')
                                            ->helperText('Nama akun yang ditampilkan di website (misal: gp.ansor)')
                                            ->maxLength(100),

                                        TextInput::make('social_instagram_url')
                                            ->label('Tautan Link Profil Instagram')
                                            ->placeholder('https://www.instagram.com/gp.ansor')
                                            ->helperText('URL lengkap yang dituju saat ikon Instagram diklik')
                                            ->url()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Fieldset::make('YouTube')
                                    ->schema([
                                        TextInput::make('social_youtube_handle')
                                            ->label('Nama Channel / Handle YouTube')
                                            ->placeholder('@GerakanPemudaAnsor')
                                            ->helperText('Nama channel yang ditampilkan di website')
                                            ->maxLength(100),

                                        TextInput::make('social_youtube_url')
                                            ->label('Tautan Link Channel YouTube')
                                            ->placeholder('https://www.youtube.com/@GerakanPemudaAnsor')
                                            ->helperText('URL lengkap yang dituju saat ikon YouTube diklik')
                                            ->url()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        if (isset($state['map_embed_url']) && is_string($state['map_embed_url'])) {
            $state['map_embed_url'] = Setting::normalizeMapEmbedUrl($state['map_embed_url']);
        }

        DB::transaction(function () use ($state): void {
            foreach ($state as $key => $value) {
                Setting::setValue((string) $key, $value);
            }
        });

        $this->savedData = $this->data;

        Notification::make()
            ->title('Pengaturan situs berhasil disimpan.')
            ->success()
            ->send();
    }
}
