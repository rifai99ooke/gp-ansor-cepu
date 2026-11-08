<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Body;
use App\Models\Gallery;
use App\Models\Officer;
use App\Models\Page;
use App\Models\Post;
use App\Models\RegistrationForm;
use App\Models\Setting;
use App\Models\User;
use App\Services\PlaceholderAssetGenerator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the initial admin only when the configured account is absent
        $admin = User::query()->firstOrCreate(
            ['email' => (string) config('app.admin_email', 'admin@ansor.id')],
            function (): array {
                $validated = Validator::make(
                    ['ADMIN_PASSWORD' => config('app.admin_password')],
                    ['ADMIN_PASSWORD' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()]],
                    [],
                    ['ADMIN_PASSWORD' => 'ADMIN_PASSWORD'],
                )->validate();

                return [
                    'name' => (string) config('app.admin_name', 'Admin GP Ansor'),
                    'username' => (string) config('app.admin_username', 'admin'),
                    'password' => Hash::make($validated['ADMIN_PASSWORD']),
                    'email_verified_at' => now(),
                ];
            }
        );

        // 2. Generate local SVG emblems & WebP images with responsive variants
        app(PlaceholderAssetGenerator::class)->generateAll();

        // 3. Seed Settings
        foreach (Setting::defaults() as $key => $value) {
            Setting::query()->firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        Setting::clearCache();

        // 4. Seed 30 News Posts (dated 2025-06 to 2026-09; older posts have higher views for Terpopuler)
        $newsItems = [
            // Latest 2026 posts (appear first in Berita Terkini & Hero Carousel)
            [
                'title' => 'GP Ansor Gelar Liga Olahraga Santri Antar Pesantren Tingkat Kota',
                'date' => '2026-09-28 09:00:00',
                'cover' => 'posts/extraliga-santri.webp',
                'views' => 420,
                'featured' => true,
                'excerpt' => 'SUKABUMI — Sebanyak 40 pesantren dari delapan zona di wilayah kota dan kabupaten ambil bagian dalam ajang silaturahmi olahraga santri tahunan.',
            ],
            [
                'title' => 'Satkorwil Banser Siagakan Personel Kawal Pelaksanaan Muktamar dan Agenda Nasional',
                'date' => '2026-08-05 14:30:00',
                'cover' => 'posts/rakor-banser.webp',
                'views' => 530,
                'featured' => true,
                'excerpt' => 'SURABAYA — Satuan Koordinasi Wilayah (Satkorwil) Banser menggelar rapat koordinasi pengamanan terpadu guna mendukung kelancaran kegiatan organisasi.',
            ],
            [
                'title' => 'Pimpinan Pusat GP Ansor Resmikan Gerai Koperasi dan Ritel Kemandirian Ekonomi Kader',
                'date' => '2026-08-04 11:15:00',
                'cover' => 'posts/koperasi-mart.webp',
                'views' => 610,
                'featured' => true,
                'excerpt' => 'BOLAANG MONGONDOW — Pengurus Pusat GP Ansor meresmikan unit usaha ritel dan koperasi sentra ekonomi kader sebagai langkah penguatan kemandirian.',
            ],
            [
                'title' => 'Pengurus Wilayah GP Ansor Sulawesi Utara Masa Khidmat Baru Resmi Dilantik',
                'date' => '2026-08-04 08:45:00',
                'cover' => 'posts/pelantikan-wilayah.webp',
                'views' => 390,
                'featured' => true,
                'excerpt' => 'MANADO — Jajaran pengurus Pimpinan Wilayah Gerakan Pemuda Ansor Sulawesi Utara resmi dilantik untuk menjalankan amanah organisasi periode terbaru.',
            ],
            [
                'title' => 'PW GP Ansor Jalin Kerja Sama Literasi Perpajakan dan Tata Kelola Kelembagaan',
                'date' => '2026-07-14 13:20:00',
                'cover' => 'posts/kerjasama-lembaga.webp',
                'views' => 340,
                'featured' => true,
                'excerpt' => 'SURABAYA — Pimpinan Wilayah Gerakan Pemuda Ansor menandatangani nota kesepahaman edukasi perpajakan dan pendampingan UMKM binaan kader.',
            ],
            [
                'title' => 'GP Ansor Tutup Pelatihan Kepemimpinan Nasional dengan Doa Bersama dan Ziarah Pendiri',
                'date' => '2026-07-13 19:00:00',
                'cover' => 'posts/pkn-kaderisasi.webp',
                'views' => 480,
                'featured' => true,
                'excerpt' => 'JOMBANG — Pimpinan Pusat Gerakan Pemuda Ansor secara resmi menutup rangkaian Pelatihan Kepemimpinan Nasional (PKN) setelah berlangsung lima hari.',
            ],
            [
                'title' => 'Ansor Buka Ratusan Posko Pelayanan Ramadhan dan Mudik di Berbagai Daerah',
                'date' => '2026-02-28 16:00:00',
                'cover' => 'posts/posko-ramadhan.webp',
                'views' => 890,
                'featured' => true,
                'excerpt' => 'SIDOARJO — Pimpinan Wilayah Gerakan Pemuda Ansor resmi meluncurkan 400 Posko Ramadhan yang tersebar di berbagai titik strategis jalur masyarakat.',
            ],
            [
                'title' => 'Ratusan Kader Ikuti Pelatihan Kepemimpinan Dasar Serentak di Tiga Kecamatan',
                'date' => '2026-06-22 10:00:00',
                'cover' => 'posts/konferancab-kader.webp',
                'views' => 310,
                'featured' => true,
                'excerpt' => 'MALANG — Pengurus Cabang GP Ansor terus mengakselerasi pengkaderan formal melalui Pelatihan Kepemimpinan Dasar (PKD) bagi generasi muda.',
            ],
            [
                'title' => 'Tim Balantas Banser Berikan Edukasi Keselamatan Berkendara bagi Pelajar dan Santri',
                'date' => '2026-06-10 09:30:00',
                'cover' => 'posts/rakor-banser.webp',
                'views' => 275,
                'featured' => true,
                'excerpt' => 'SEMARANG — Barisan Ansor Serbaguna Lalu Lintas (Balantas) mengadakan sosialisasi tertib berlalu lintas di lingkungan pondok pesantren.',
            ],
            [
                'title' => 'MDS Rijalul Ansor Gelar Majelis Dzikir dan Kajian Kitab Turats Rutin Bulanan',
                'date' => '2026-05-25 20:00:00',
                'cover' => 'posts/pkn-kaderisasi.webp',
                'views' => 360,
                'featured' => true,
                'excerpt' => 'YOGYAKARTA — Majelis Dzikir dan Shalawat Rijalul Ansor kembali menyelenggarakan pengajian rutin guna merawat tradisi keilmuan pesantren.',
            ],
            [
                'title' => 'Lembaga Bantuan Hukum Ansor Buka Layanan Konsultasi Hukum Gratis untuk Warga',
                'date' => '2026-05-12 11:00:00',
                'cover' => 'posts/advokasi-pekerja.webp',
                'views' => 290,
                'featured' => false,
                'excerpt' => 'JAKARTA — Pos pelayanan pendampingan hukum masyarakat dibuka setiap pekan untuk membantu warga yang membutuhkan edukasi dan advokasi keadilan.',
            ],
            [
                'title' => 'Banaar Ansor Gencarkan Penyuluhan Pencegahan Bahaya Narkoba di Kalangan Remaja',
                'date' => '2026-04-28 14:00:00',
                'cover' => 'posts/kerjasama-lembaga.webp',
                'views' => 245,
                'featured' => false,
                'excerpt' => 'BANDUNG — Badan Ansor Anti Narkoba (Banaar) bersinergi dengan sekolah dan madrasah menyelenggarakan penyuluhan generasi muda sehat tanpa narkoba.',
            ],
            [
                'title' => 'Baritim GP Ansor Lakukan Aksi Bersih Pantai dan Penanaman Bibit Mangrove',
                'date' => '2026-04-15 08:30:00',
                'cover' => 'posts/ketahanan-pangan.webp',
                'views' => 330,
                'featured' => false,
                'excerpt' => 'GRESIK — Barisan Ansorjek Maritim (Baritim) bersama masyarakat pesisir menanam ribuan bibit mangrove untuk mencegah abrasi pantai.',
            ],
            [
                'title' => 'Kader Muda Ansor Raih Penghargaan Inovasi Wirausaha Sosial Tingkat Nasional',
                'date' => '2026-03-30 15:00:00',
                'cover' => 'posts/koperasi-mart.webp',
                'views' => 410,
                'featured' => false,
                'excerpt' => 'JAKARTA — Program pemberdayaan pengolahan hasil pertanian desa yang digagas kader muda GP Ansor berhasil meraih apresiasi inovasi wirausaha.',
            ],
            [
                'title' => 'Pengumuman Jadwal Pendaftaran Diklatsar Banser dan PKD Gelombang Kedua',
                'date' => '2026-03-18 10:00:00',
                'cover' => 'posts/pelantikan-wilayah.webp',
                'views' => 520,
                'featured' => false,
                'excerpt' => 'JAKARTA — Panitia kaderisasi membuka pendaftaran peserta Pendidikan dan Pelatihan Dasar (Diklatsar) Banser serta PKD secara daring.',
            ],
            [
                'title' => 'Bakti Sosial Pelayanan Kesehatan Gratis GP Ansor Jangkau Warga Desa Terpencil',
                'date' => '2026-02-14 09:00:00',
                'cover' => 'posts/donasi-sosial.webp',
                'views' => 280,
                'featured' => false,
                'excerpt' => 'BANYUMAS — Tim relawan kesehatan GP Ansor dan Banser memberikan layanan pemeriksaan kesehatan serta obat-obatan gratis bagi lansia.',
            ],
            [
                'title' => 'Rapat Kerja Nasional GP Ansor Rumuskan Peta Jalan Transformasi Digital Organisasi',
                'date' => '2026-01-29 13:00:00',
                'cover' => 'posts/pelantikan-wilayah.webp',
                'views' => 375,
                'featured' => false,
                'excerpt' => 'BOGOR — Rapat Kerja Nasional membahas penguatan sistem administrasi kaderisasi berbasis digital untuk mempercepat layanan organisasi.',
            ],
            [
                'title' => 'Bagana Banser Gelar Simulasi Tanggap Darurat Bencana Banjir dan Tanah Longsor',
                'date' => '2026-01-12 08:00:00',
                'cover' => 'posts/donasi-sosial.webp',
                'views' => 305,
                'featured' => false,
                'excerpt' => 'MAGELANG — Banser Tanggap Bencana (Bagana) meningkatkan kapasitas personel relawan melalui pelatihan evakuasi dan dapur umum lapangan.',
            ],
            [
                'title' => 'Pelatihan Jurnalistik dan Media Kreatif bagi Pengurus Cabang GP Ansor',
                'date' => '2025-12-19 10:30:00',
                'cover' => 'posts/kerjasama-lembaga.webp',
                'views' => 450,
                'featured' => false,
                'excerpt' => 'SOLO — Guna memperkuat literasi digital dan publikasi kegiatan positif organisasi, puluhan kader mengikuti kelas jurnalistik dan fotografi.',
            ],
            [
                'title' => 'Kontingen Santri Binaan GP Ansor Juara Umum Festival Hadrah dan Shalawat',
                'date' => '2025-12-04 16:00:00',
                'cover' => 'posts/extraliga-santri.webp',
                'views' => 495,
                'featured' => false,
                'excerpt' => 'KUDUS — Grup seni hadrah binaan PAC GP Ansor berhasil meraih predikat terbaik dalam ajang festival seni budaya Islam tingkat provinsi.',
            ],
            [
                'title' => 'Diskusi Publik Kepemudaan Bahas Peran Kader dalam Menjaga Harmoni Sosial',
                'date' => '2025-11-20 14:00:00',
                'cover' => 'posts/konferancab-kader.webp',
                'views' => 540,
                'featured' => false,
                'excerpt' => 'MAKASSAR — Forum dialog kepemudaan lintas elemen masyarakat digelar untuk mempererat persaudaraan kebangsaan dan toleransi antarwarga.',
            ],
            [
                'title' => 'Program Beasiswa Pendidikan Kader Muda GP Ansor Kembali Dibuka Tahun Ini',
                'date' => '2025-11-08 09:00:00',
                'cover' => 'posts/kerjasama-lembaga.webp',
                'views' => 680,
                'featured' => false,
                'excerpt' => 'JAKARTA — Kerja sama perguruan tinggi dengan GP Ansor menyediakan fasilitas beasiswa studi sarjana dan pascasarjana bagi kader berprestasi.',
            ],
            [
                'title' => 'Festival UMKM Kader Ansor Pamerkan Produk Kuliner dan Kerajinan Lokal',
                'date' => '2025-10-15 11:00:00',
                'cover' => 'posts/koperasi-mart.webp',
                'views' => 620,
                'featured' => false,
                'excerpt' => 'CIREBON — Puluhan pelaku usaha mikro binaan GP Ansor memamerkan produk unggulan daerah dalam bazar ekonomi kreatif akhir pekan.',
            ],
            [
                'title' => ' Apel Kebangsaan Ribuan Kader Banser Teguhkan Semangat Bela Negara',
                'date' => '2025-09-22 08:00:00',
                'cover' => 'posts/rakor-banser.webp',
                'views' => 740,
                'featured' => false,
                'excerpt' => 'LAMPUNG — Ribuan anggota Banser mengikuti apel kebangsaan sebagai wujud kesiapsiagaan menjaga keutuhan Negara Kesatuan Republik Indonesia.',
            ],

            // 6 Most Viewed Posts from mid-2025 (appear in "Berita Terpopuler" matching Screenshots 1 & 2!)
            [
                'title' => 'GP Ansor Siman Gelar Konferancab, Teguhkan Komitmen Kaderisasi',
                'date' => '2025-06-26 10:00:00',
                'cover' => 'posts/konferancab-kader.webp',
                'views' => 4850,
                'featured' => false,
                'excerpt' => 'PONOROGO — Pimpinan Anak Cabang (PAC) Gerakan Pemuda Ansor menyelenggarakan Konferensi Anak Cabang (Konferancab) guna menyusun program kerja kaderisasi.',
            ],
            [
                'title' => 'LBH Ansor Dampingi Penyelesaian Hak Pekerja dan Mediasi Hubungan Industrial',
                'date' => '2025-07-10 11:30:00',
                'cover' => 'posts/advokasi-pekerja.webp',
                'views' => 4420,
                'featured' => false,
                'excerpt' => 'SURABAYA — Tim Lembaga Bantuan Hukum (LBH) GP Ansor memberikan pendampingan hukum kepada para pekerja agar hak-hak normatif terpenuhi sesuai regulasi.',
            ],
            [
                'title' => 'Ratusan Peserta Semarakkan Gowes Kemerdekaan GP Ansor Bersama Masyarakat',
                'date' => '2025-08-19 07:30:00',
                'cover' => 'posts/gowes-kemerdekaan.webp',
                'views' => 3980,
                'featured' => false,
                'excerpt' => 'MOJOKERTO — Ratusan warga dan kader antusias mengikuti kegiatan olahraga bersepeda bersama dalam rangka memeriahkan Hari Ulang Tahun Kemerdekaan RI.',
            ],
            [
                'title' => 'Banser Dukung Ketahanan Pangan Nasional Lewat Gerakan 100 Ribu Petani Muda',
                'date' => '2025-07-03 09:15:00',
                'cover' => 'posts/ketahanan-pangan.webp',
                'views' => 3650,
                'featured' => false,
                'excerpt' => 'SUBANG — Program pendampingan pertanian terpadu dan modernisasi alat tanam digerakkan oleh kader muda untuk menopang kemandirian pangan desa.',
            ],
            [
                'title' => 'GP Ansor Imbau Kader Perkuat Literasi Digital dan Tangkal Informasi Hoaks',
                'date' => '2025-06-26 15:00:00',
                'cover' => 'posts/pelantikan-wilayah.webp',
                'views' => 3290,
                'featured' => false,
                'excerpt' => 'JAKARTA — Seluruh jajaran kader diinstruksikan menjadi pelopor ruang digital yang sejuk, santun, dan mengedepankan tabayyun informasi.',
            ],
            [
                'title' => 'Ansor Kubu Raya Galang Donasi Kemanusiaan untuk Warga Terdampak Musibah Kebakaran',
                'date' => '2025-10-23 13:45:00',
                'cover' => 'posts/donasi-sosial.webp',
                'views' => 2950,
                'featured' => false,
                'excerpt' => 'KUBU RAYA — Pengurus Cabang GP Ansor bersama satuan Banser menyalurkan bantuan logistik dan santunan kepada keluarga yang terdampak musibah.',
            ],
        ];

        foreach ($newsItems as $item) {
            $slug = Str::slug($item['title']);

            $bodyParagraphs = [
                '<p>'.e($item['excerpt']).'</p>',
                '<p>Kegiatan ini merupakan bagian dari komitmen berkelanjutan Gerakan Pemuda Ansor dalam memperkuat kapasitas organisasi, mempererat ukhuwah kebangsaan, serta menghadirkan manfaat nyata di tengah masyarakat luas.</p>',
                '<h2>Penguatan Khidmat dan Konsolidasi Kader</h2>',
                '<p>Melalui sinergi antara pengurus pusat, wilayah, cabang, hingga anak cabang dan ranting, setiap program dirancang agar berjalan secara terukur, partisipatif, dan berkesinambungan. Para peserta tampak antusias mengikuti seluruh rangkaian acara dari awal hingga penutupan.</p>',
                '<p>Selain agenda utama, kegiatan ini juga diisi dengan sesi diskusi kelompok, pemetaan potensi lokal, serta penyusunan rencana tindak lanjut di masing-masing daerah agar dampak positifnya dapat dirasakan secara luas.</p>',
                '<blockquote>Transformasi organisasi yang berkesinambungan menuntut kesiapan sumber daya kader yang berakhlak mulia, berdaya saing, dan selalu hadir menjawab kebutuhan umat serta bangsa.</blockquote>',
                '<p>Di penghujung kegiatan, seluruh peserta dan panitia melaksanakan doa bersama agar setiap ikhtiar pengabdian yang dijalankan senantiasa membawa keberkahan dan kemaslahatan bagi masyarakat.</p>',
            ];

            Post::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'type' => PostType::Berita,
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'content' => implode("\n", $bodyParagraphs),
                    'cover_image' => $item['cover'],
                    'author_id' => $admin->id,
                    'author_name' => 'Tim Redaksi GP Ansor',
                    'status' => PostStatus::Published,
                    'is_featured' => $item['featured'],
                    'views_count' => $item['views'],
                    'published_at' => Carbon::parse($item['date']),
                ]
            );
        }

        // 6. Seed 3 Officers (Ketua Umum, Sekretaris Jenderal, Bendahara Umum)
        $officersData = [
            [
                'name' => 'H. Ahmad Fauzi Pratama',
                'slug' => 'ketua-umum',
                'position' => 'Ketua Umum',
                'period' => '2024 - 2029',
                'photo' => 'officers/ketua-umum.webp',
                'sort_order' => 1,
                'bio' => '<p>Menjalankan amanah sebagai Ketua Umum Pimpinan Pusat Gerakan Pemuda Ansor dengan fokus pada penguatan sistem kaderisasi nasional, kemandirian ekonomi organisasi, serta transformasi kelembagaan berbasis teknologi digital.</p><p>Aktif dalam berbagai forum kepemudaan dan pemberdayaan masyarakat sejak tingkat cabang hingga pusat, serta mendorong kolaborasi strategis lintas sektor untuk kemaslahatan umat dan bangsa.</p>',
            ],
            [
                'name' => 'H. M. Rifqi Al-Farisi',
                'slug' => 'sekretaris-jenderal',
                'position' => 'Sekretaris Jenderal',
                'period' => '2024 - 2029',
                'photo' => 'officers/sekretaris-jenderal.webp',
                'sort_order' => 2,
                'bio' => '<p>Bertugas mengoordinasikan tata kelola kesekretariatan, administrasi organisasi, serta sinkronisasi program kerja Pimpinan Pusat hingga ke tingkat Pimpinan Wilayah dan Pimpinan Cabang di seluruh Indonesia.</p><p>Memiliki perhatian besar pada modernisasi pendataan anggota, tertib organisasi, dan peningkatan kualitas pelayanan publik kelembagaan.</p>',
            ],
            [
                'name' => 'H. Aditya Fajriansyah',
                'slug' => 'bendahara-umum',
                'position' => 'Bendahara Umum',
                'period' => '2024 - 2029',
                'photo' => 'officers/bendahara-umum.webp',
                'sort_order' => 3,
                'bio' => '<p>Mengelola perencanaan keuangan, akuntabilitas anggaran, serta pengembangan ekosistem kewirausahaan dan koperasi kader di lingkungan Gerakan Pemuda Ansor.</p><p>Berkomitmen mewujudkan tata kelola keuangan organisasi yang transparan, profesional, dan berkelanjutan guna menopang seluruh program pengabdian masyarakat.</p>',
            ],
        ];

        foreach ($officersData as $officer) {
            Officer::query()->firstOrCreate(
                ['slug' => $officer['slug']],
                $officer
            );
        }

        // 6. Seed 5 Badan & Lembaga (matching Screenshot 4 order & names)
        $bodiesData = [
            [
                'name' => 'MDS Rijalul Ansor',
                'slug' => 'mds-rijalul-ansor',
                'logo' => 'bodies/mds-rijalul-ansor.svg',
                'sort_order' => 1,
                'description' => '<p><strong>Majelis Dzikir dan Shalawat (MDS) Rijalul Ansor</strong> adalah lembaga semi-otonom di bawah naungan Gerakan Pemuda Ansor yang bertugas menghidupkan tradisi keilmuan pesantren, majelis dzikir, shalawat, serta dakwah Islam Ahlussunnah wal Jamaah yang sejuk dan merangkul masyarakat.</p><p>Melalui pengajian rutin, halaqah ulama muda, dan peringatan hari besar Islam, MDS Rijalul Ansor menjadi garda spiritual dan intelektual kader di seluruh tingkatan organisasi.</p>',
            ],
            [
                'name' => 'Baritim',
                'slug' => 'baritim',
                'logo' => 'bodies/baritim.svg',
                'sort_order' => 2,
                'description' => '<p><strong>Barisan Ansor Serbaguna Maritim (Baritim)</strong> merupakan satuan khusus yang berfokus pada pemberdayaan masyarakat pesisir, pelestarian ekosistem kelautan, keselamatan pelayaran rakyat, serta ketahanan maritim Nusantara.</p><p>Kader Baritim aktif dalam kegiatan konservasi mangrove, edukasi kebaharian, dan pendampingan nelayan tradisional di wilayah kepulauan Indonesia.</p>',
            ],
            [
                'name' => 'Banser',
                'slug' => 'banser',
                'logo' => 'bodies/banser.svg',
                'sort_order' => 3,
                'description' => '<p><strong>Barisan Ansor Serbaguna (Banser)</strong> adalah korps kader inti Gerakan Pemuda Ansor yang mengemban fungsi pengabdian masyarakat, kesiapsiagaan kemanusiaan, penanggulangan bencana, serta penjagaan ketertiban kegiatan keagamaan dan kebangsaan.</p><p>Dengan disiplin tinggi dan semangat kerelawanan, personel Banser hadir membantu warga dalam berbagai situasi darurat maupun kegiatan sosial kemasyarakatan.</p>',
            ],
            [
                'name' => 'Banaar',
                'slug' => 'banaar',
                'logo' => 'bodies/banaar.svg',
                'sort_order' => 4,
                'description' => '<p><strong>Badan Ansor Anti Narkoba (Banaar)</strong> adalah badan khusus GP Ansor yang bergerak dalam program pencegahan, penyuluhan, edukasi publik, serta pendampingan pemulihan generasi muda dari bahaya penyalahgunaan narkotika dan zat adiktif.</p><p>Banaar menjalin sinergi dengan institusi pendidikan, keluarga, dan lembaga terkait guna mewujudkan lingkungan pemuda yang sehat, produktif, dan bebas narkoba.</p>',
            ],
            [
                'name' => 'Balantas',
                'slug' => 'balantas',
                'logo' => 'bodies/balantas.svg',
                'sort_order' => 5,
                'description' => '<p><strong>Barisan Ansor Serbaguna Lalu Lintas (Balantas)</strong> merupakan satuan khusus yang bertugas membantu kelancaran arus lalu lintas pada kegiatan masyarakat, memberikan edukasi keselamatan berkendara, serta mendirikan posko pelayanan mudik.</p><p>Personel Balantas dibekali pelatihan pengaturan jalan dan pertolongan pertama guna mendukung ketertiban dan keselamatan pengguna jalan.</p>',
            ],
        ];

        foreach ($bodiesData as $body) {
            Body::query()->firstOrCreate(
                ['slug' => $body['slug']],
                $body
            );
        }

        // 7. Seed Static Pages (Visi & Misi, Program, Dokumen)
        $pagesData = [
            [
                'slug' => 'visi-misi',
                'title' => 'Visi & Misi',
                'subtitle' => 'Arah perjuangan, nilai dasar, dan cita-cita luhur Gerakan Pemuda Ansor dalam mencetak pemimpin masa depan.',
                'content' => <<<'HTML'
<h2>Visi Organisasi</h2>
<blockquote>Terwujudnya Gerakan Pemuda Ansor sebagai organisasi kepemudaan yang mandiri, berkarakter Ahlussunnah wal Jamaah, pelopor kemajuan masyarakat, dan penjaga keutuhan Negara Kesatuan Republik Indonesia.</blockquote>

<h2>Misi Organisasi</h2>
<ol>
  <li><strong>Internalisasi Nilai Keislaman dan Kebangsaan:</strong> Membentuk generasi muda Indonesia yang beriman, bertakwa, berakhlak mulia, berwawasan kebangsaan luas, dan menjunjung tinggi nilai kemanusiaan.</li>
  <li><strong>Penguatan Sistem Kaderisasi:</strong> Menyelenggarakan pendidikan dan pelatihan kepemimpinan berjenjang yang sistematis guna melahirkan kader pemimpin yang cakap, berintegritas, dan responsif terhadap perubahan zaman.</li>
  <li><strong>Kemandirian Ekonomi dan Inovasi:</strong> Mengembangkan potensi kewirausahaan, koperasi, ketahanan pangan, dan kecakapan digital bagi para kader serta masyarakat luas.</li>
  <li><strong>Pengabdian Sosial dan Kemanusiaan:</strong> Menghadirkan pelayanan kemasyarakatan di bidang pendidikan, kesehatan, advokasi hukum, pelestarian lingkungan, serta penanggulangan bencana.</li>
  <li><strong>Tata Kelola Organisasi Modern:</strong> Membangun manajemen kelembagaan yang transparan, akuntabel, dan terintegrasi dari tingkat pusat hingga ranting.</li>
</ol>
HTML,
            ],
            [
                'slug' => 'program',
                'title' => 'Program Strategis',
                'subtitle' => 'Rangkaian program unggulan GP Ansor di bidang kaderisasi, kemandirian ekonomi, literasi digital, dan pengabdian masyarakat.',
                'content' => <<<'HTML'
<h2>1. Kaderisasi & Kepemimpinan Berjenjang</h2>
<p>Penyelenggaraan pelatihan formal secara konsisten mulai dari Pelatihan Kepemimpinan Dasar (PKD), Pelatihan Kepemimpinan Lanjutan (PKL), hingga Pelatihan Kepemimpinan Nasional (PKN), serta Pendidikan dan Pelatihan Dasar (Diklatsar), Kursus Banser Lanjutan (Susbalan), dan Kursus Banser Pimpinan (Susbanpim).</p>

<h2>2. Pemberdayaan Ekonomi & Ketahanan Pangan</h2>
<p>Pengembangan jaringan koperasi kader, pendampingan UMKM pemuda, serta program pertanian dan perikanan terpadu untuk memperkuat kedaulatan pangan dari tingkat desa.</p>

<h2>3. Kemanusiaan, Lingkungan & Tanggap Bencana</h2>
<p>Pelayanan sosial melalui posko kemanusiaan, aksi donor darah, penghijauan kawasan pesisir dan hulu sungai, serta kesiapsiagaan relawan bencana di berbagai daerah rawan bencana.</p>

<h2>4. Literasi Digital & Advokasi Masyarakat</h2>
<p>Peningkatan kompetensi teknologi informasi bagi pemuda, kampanye internet sehat, serta layanan bantuan dan penyuluhan hukum gratis bagi warga yang membutuhkan.</p>
HTML,
            ],
            [
                'slug' => 'dokumen',
                'title' => 'Dokumen & Pedoman Organisasi',
                'subtitle' => 'Referensi Peraturan Dasar, Peraturan Rumah Tangga, dan pedoman penyelenggaraan administrasi serta kaderisasi GP Ansor.',
                'content' => <<<'HTML'
<h2>Daftar Dokumen Resmi Organisasi</h2>
<p>Berikut adalah ringkasan dokumen pedoman utama yang menjadi rujukan tata kelola organisasi di seluruh tingkatan kepengurusan Gerakan Pemuda Ansor:</p>
<table>
  <thead>
    <tr>
      <th>Nama Dokumen</th>
      <th>Kategori</th>
      <th>Keterangan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Peraturan Dasar & Peraturan Rumah Tangga (PD/PRT)</strong></td>
      <td>Konstitusi Organisasi</td>
      <td>Landasan utama organisasi hasil Kongres GP Ansor.</td>
    </tr>
    <tr>
      <td><strong>Peraturan Organisasi (PO) Sistem Kaderisasi</strong></td>
      <td>Kaderisasi</td>
      <td>Pedoman pelaksanaan PKD, PKL, PKN, dan Diklatsar Banser.</td>
    </tr>
    <tr>
      <td><strong>Pedoman Administrasi & Kesekretariatan</strong></td>
      <td>Tata Kelola</td>
      <td>Standar surat-menyurat, atribut, dan pengarsipan organisasi.</td>
    </tr>
    <tr>
      <td><strong>Peraturan Organisasi Korps Banser</strong></td>
      <td>Kelembagaan</td>
      <td>Pedoman satuan koordinasi dan satuan khusus Banser.</td>
    </tr>
  </tbody>
</table>
<p>Untuk permohonan salinan resmi dokumen cetak maupun digital bagi jajaran pengurus wilayah dan cabang, silakan menghubungi Sekretariat Pimpinan Pusat melalui halaman <a href="/hubungi-kami">Hubungi Kami</a>.</p>
HTML,
            ],
        ];

        foreach ($pagesData as $page) {
            Page::query()->firstOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }

        // 8. Seed Sample Registration Form
        RegistrationForm::query()->firstOrCreate(
            [],
            [
                'slug' => 'pendaftaran-kader-gp-ansor',
                'title' => 'Pendaftaran Kader GP Ansor & Banser',
                'description' => '<p>Formulir pendaftaran daring bagi calon kader baru Gerakan Pemuda Ansor (PKD) dan Barisan Ansor Serbaguna (Diklatsar Banser). Pastikan seluruh data diri diisi dengan lengkap dan benar.</p>',
                'cover_image' => 'forms/pendaftaran-kader.webp',
                'form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdSamplePlaceholderFormId1934/viewform',
                'embed_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdSamplePlaceholderFormId1934/viewform?embedded=true',
                'responses_url' => 'https://docs.google.com/spreadsheets/d/1SampleResponsesSheetId1934/edit',
                'opens_at' => Carbon::parse('2025-01-01 00:00:00'),
                'closes_at' => Carbon::parse('2027-12-31 23:59:59'),
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // 9. Seed 9 Gallery Items
        $galleriesData = [
            [
                'title' => 'Liga Olahraga Santri Antar Pesantren (Extraliga Santri)',
                'slug' => 'liga-olahraga-santri-extraliga-santri',
                'description' => 'Ajang silaturahmi olahraga tahunan yang diikuti oleh 40 pesantren dari delapan zona wilayah.',
                'image_path' => 'posts/extraliga-santri.webp',
                'event_date' => '2026-09-28',
                'sort_order' => 1,
            ],
            [
                'title' => 'Rapat Koordinasi Pengamanan Terpadu Satkorwil Banser',
                'slug' => 'rapat-koordinasi-pengamanan-satkorwil-banser',
                'description' => 'Apel kesiapsiagaan dan koordinasi personel Banser dalam mengawal agenda nasional organisasi.',
                'image_path' => 'posts/rakor-banser.webp',
                'event_date' => '2026-08-05',
                'sort_order' => 2,
            ],
            [
                'title' => 'Peresmian Gerai Koperasi dan Ritel Kemandirian Ekonomi Kader',
                'slug' => 'peresmian-gerai-koperasi-dan-ritel-ansor',
                'description' => 'Peluncuran unit usaha ritel dan koperasi sentra ekonomi kader oleh Pimpinan Pusat GP Ansor.',
                'image_path' => 'posts/koperasi-mart.webp',
                'event_date' => '2026-08-04',
                'sort_order' => 3,
            ],
            [
                'title' => 'Pelantikan Pengurus Wilayah GP Ansor Masa Khidmat Baru',
                'slug' => 'pelantikan-pengurus-wilayah-gp-ansor',
                'description' => 'Prosesi pelantikan khidmat jajaran Pengurus Wilayah GP Ansor guna memperkuat konsolidasi organisasi.',
                'image_path' => 'posts/pelantikan-wilayah.webp',
                'event_date' => '2026-08-01',
                'sort_order' => 4,
            ],
            [
                'title' => 'Pelatihan Kepemimpinan Nasional (PKN) GP Ansor',
                'slug' => 'pelatihan-kepemimpinan-nasional-pkn-gp-ansor',
                'description' => 'Penutupan dan pembaiatan kader peserta Pelatihan Kepemimpinan Nasional tingkat pusat.',
                'image_path' => 'posts/pkn-kaderisasi.webp',
                'event_date' => '2026-07-13',
                'sort_order' => 5,
            ],
            [
                'title' => 'Posko Mudik & Pelayanan Masyarakat Ramadhan GP Ansor',
                'slug' => 'posko-mudik-ramadhan-gp-ansor',
                'description' => 'Ratusan posko pelayanan mudik dan masyarakat yang didirikan oleh kader Ansor dan Banser.',
                'image_path' => 'posts/posko-ramadhan.webp',
                'event_date' => '2026-02-28',
                'sort_order' => 6,
            ],
            [
                'title' => 'Gowes Kebangsaan Semarak Kemerdekaan Bersama Masyarakat',
                'slug' => 'gowes-kebangsaan-semarak-kemerdekaan',
                'description' => 'Kegiatan olahraga bersama ratusan warga dalam rangka memperingati Hari Kemerdekaan Republik Indonesia.',
                'image_path' => 'posts/gowes-kemerdekaan.webp',
                'event_date' => '2025-08-19',
                'sort_order' => 7,
            ],
            [
                'title' => 'Gerakan 100 Ribu Petani Muda Dukung Ketahanan Pangan',
                'slug' => 'gerakan-100-ribu-petani-muda-ketahanan-pangan',
                'description' => 'Pemberdayaan kader muda di sektor pertanian terpadu untuk mendukung kedaulatan pangan nasional.',
                'image_path' => 'posts/ketahanan-pangan.webp',
                'event_date' => '2025-07-03',
                'sort_order' => 8,
            ],
            [
                'title' => 'Aksi Tanggap Darurat & Donasi Kemanusiaan Banser',
                'slug' => 'aksi-tanggap-darurat-dan-donasi-kemanusiaan',
                'description' => 'Penyaluran bantuan kemanusiaan oleh BAGANA dan personel Banser bagi warga terdampak musibah.',
                'image_path' => 'posts/donasi-sosial.webp',
                'event_date' => '2025-10-23',
                'sort_order' => 9,
            ],
        ];

        foreach ($galleriesData as $gallery) {
            Gallery::query()->firstOrCreate(
                ['slug' => $gallery['slug']],
                $gallery
            );
        }
    }
}
