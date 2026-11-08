# GP Ansor — Organization Profile Website & Admin Portal

A complete, production-ready organization profile website and content management system built for **Gerakan Pemuda Ansor (GP Ansor)** using **Laravel 13**, **Filament v5**, **Tailwind CSS v4**, **Alpine.js**, **Swiper**, and **SQLite**.

All public-facing UI and admin labels are localized in **Indonesian** (`APP_LOCALE=id`, `APP_TIMEZONE=Asia/Jakarta`), while the source code, comments, and documentation are in **English**.

---

## Tech Stack

- **Backend**: Laravel 13 (PHP 8.3+), server-rendered Blade views
- **Database**: SQLite (`database/database.sqlite`) with foreign key constraints (`PRAGMA foreign_keys = ON`), Write-Ahead Logging (`PRAGMA journal_mode = WAL`), and a 5000ms busy timeout (`PRAGMA busy_timeout = 5000`)
- **Admin Panel**: Filament v5 at `/admin`
- **Frontend**: Tailwind CSS v4 (`@tailwindcss/typography`), Alpine.js v3, Swiper v14, self-hosted **Inter** font (`@fontsource/inter` weights 400–800), custom Lucide outline SVG icon system + brand glyphs
- **Media & Uploads**: Stored on the `public` disk (`storage/app/public`) with responsive WebP variant generation (`480w`, `800w`, `1280w`) and local SVG/WebP placeholders generated during seeding

---

## Quick Start

On a clean checkout, install dependencies and initialize the environment and SQLite database:

```bash
composer install && cp .env.example .env && php artisan key:generate && touch database/database.sqlite
```

Set `ADMIN_PASSWORD` in `.env` to a unique password with at least 12 characters, including uppercase and lowercase letters, a number, and a symbol. Then run migrations and seeders, link public storage, build frontend assets, and start the server:

```bash
php artisan migrate --seed && php artisan storage:link && npm install && npm run build && php artisan serve
```

Then visit:

- **Public Website**: [http://localhost:8000](http://localhost:8000)
- **Admin Panel**: [http://localhost:8000/admin](http://localhost:8000/admin)

### Initial Admin Credentials

Configured via `.env` (used by `DatabaseSeeder`), signing in with **Username** and **Password**:

```dotenv
ADMIN_NAME="Admin GP Ansor"
ADMIN_USERNAME="admin"
ADMIN_EMAIL="admin@ansor.id"
ADMIN_PASSWORD=
```

There is no default admin password. Set `ADMIN_PASSWORD` before the initial seed; a missing or weak password stops seeding before any accounts or sample data are created. Rerunning the seeder preserves the existing account's credentials, including any password or username changed through the admin panel. Once the account exists, `ADMIN_PASSWORD` is no longer required for reseeding and can be removed from the environment.

---

## Environment Variables

Key environment variables in `.env`:

| Variable | Default | Description |
| :--- | :--- | :--- |
| `APP_NAME` | `"GP Ansor"` | Application name |
| `APP_LOCALE` | `id` | Default Indonesian locale for translations and Carbon dates |
| `APP_TIMEZONE` | `Asia/Jakarta` | Default timezone (WIB) |
| `DB_CONNECTION` | `sqlite` | Uses `database/database.sqlite` by default |
| `FILESYSTEM_DISK` | `public` | Stores uploads in `storage/app/public` |
| `ADMIN_NAME` | `"Admin GP Ansor"` | Initial administrator display name |
| `ADMIN_USERNAME` | `"admin"` | Initial administrator login username |
| `ADMIN_EMAIL` | `"admin@ansor.id"` | Initial administrator email |
| `ADMIN_PASSWORD` | None | Required for initial administrator creation; minimum 12 characters with mixed case, numbers, and symbols |
| `PHP_CLI_SERVER_WORKERS` | `4` | Worker count for `php artisan serve` |

---

## Public Pages & Features

- **`/` (Beranda)**:
  - 440px (260px mobile) Hero Carousel of up to 10 featured published news items (`type = berita`), with 5s autoplay, hover pause, touch swipe, and animated pill pagination dots.
  - **Berita Terkini**: 3-column grid (6 per page) with search input (`?q=`) and custom numbered pagination.
  - **Berita Terpopuler**: Sidebar displaying the 6 most viewed news items with 64px square thumbnails and short Indonesian dates.
  - **Bergabung Bersama Kami**: Call-to-action banner linking to `/pendaftaran` when an active registration form exists.
- **`/berita` (Berita)**:
  - 360px Hero Carousel.
  - **Berita Terpopuler**: 3-slide Swiper carousel with prev/next navigation buttons and pagination dots.
  - **Berita Terkini**: Full-width `#f9fafb` band with search (`?q=&page=`) and 9 cards per page.
- **`/berita/{slug}`**:
  - Breadcrumb, H1 title, author, long Indonesian date, estimated reading time, view counter (incremented once per session), 16:9 cover image, sanitized `prose` article body, social share buttons (WhatsApp, Facebook, X, Copy Link), and 3 related posts.
- **`/profil/visi-misi`, `/program`, `/dokumen`**:
  - Static pages managed from the `pages` table in the admin panel.
- **`/profil/struktur-organisasi` & `/profil/struktur-organisasi/{slug}`**:
  - Officer directory with instant client-side Alpine.js filtering and server-side `?q=` fallback by name or position, plus individual officer profile pages.
- **`/badan-lembaga` & `/badan-lembaga/{slug}`**:
  - Autonomous and semi-autonomous bodies (MDS Rijalul Ansor, Baritim, Banser, Banaar, Balantas) with instant client-side search, server-side `?q=` fallback, and detail views.
- **`/galeri`**:
  - Searchable (`?q=`) and paginated (9 per page) photo documentation gallery with an accessible keyboard-navigable lightbox modal.
- **`/hubungi-kami`**:
  - Secretariat address card with embedded Google Maps (`https://`), social media cards (TikTok, Facebook, Instagram, YouTube), and direct contact cards (Phone 1, Phone 2, Email).
- **`/pendaftaran`**:
  - Native online registration portal powered by Google Forms (`GoogleFormSchemaService`). Automatically extracts form fields from the configured Google Form and submits validated entries to Google's `/formResponse` endpoint (rate-limited with `throttle:10,1`).
- **SEO**:
  - Per-page `<title>`, meta description, canonical URL, Open Graph & Twitter Card tags, `Organization` and `NewsArticle` JSON-LD structured data, `/sitemap.xml`, and `/robots.txt`.

---

## Managing Content in Filament Admin (`/admin`)

Log in at `/admin/login` using your `username` and `password`:

1. **Berita (`PostResource`)**: Create and edit news articles (`berita`), upload cover images (automatically generating `480w`, `800w`, and `1280w` WebP variants), toggle `is_featured` for the hero carousel, and manage publication status (`draft` / `published`).
2. **Struktur Organisasi (`OfficerResource`)**: Manage officers, photos, positions, bios, and drag-and-drop display order (`sort_order`).
3. **Badan & Lembaga (`BodyResource`)**: Manage organizational bodies, emblems/logos, descriptions, and drag-and-drop display order.
4. **Halaman (`PageResource`)**: Edit static pages (`visi-misi`, `program`, `dokumen`). Core pages are protected from accidental creation or deletion.
5. **Galeri (`GalleryResource`)**: Manage documentation photos, event dates, descriptions, and drag-and-drop display order.
6. **Pendaftaran (`RegistrationPage`)**: Configure the active Google Form registration campaign, cover image, description/instructions, open/close schedule (`opens_at`, `closes_at`), and Google Sheets responses shortcut link.
7. **Pengguna (`UserResource`)**: Manage administrator accounts (with self-deletion and last-admin deletion protection).
8. **Pengaturan (`SettingsPage`)**: Manage site name, full name, tagline, logo, favicon, address, Google Maps embed URL, phone numbers, email, social media handles/URLs, and footer copyright text. Saving settings immediately clears the settings cache.

---

## Configuring Google Form URLs

When configuring **Formulir Pendaftaran** in `/admin/pendaftaran`, the **Tautan Google Form / Kode Sematan `<iframe>`** field accepts any of the following formats:

1. **Standard Google Form URL**:
   ```text
   https://docs.google.com/forms/d/e/1FAIpQLSe.../viewform
   ```
2. **Full `<iframe>` Embed Code**:
   ```html
   <iframe src="https://docs.google.com/forms/d/e/1FAIpQLSe.../viewform?embedded=true" width="640" height="800">Memuat…</iframe>
   ```
   *(The `src` URL is automatically extracted and validated.)*
3. **Short Link (`forms.gle`)**:
   ```text
   https://forms.gle/abc123xyz
   ```
   *(The application attempts to resolve the redirect with a 5-second timeout.)*

---

## SQLite Configuration & Backup

### SQLite Tuning

Configured in `config/database.php` and enforced at runtime in `AppServiceProvider`:

- `foreign_key_constraints => true` (`PRAGMA foreign_keys = ON`)
- `busy_timeout => 5000` (`PRAGMA busy_timeout = 5000`)
- `journal_mode => 'WAL'` (`PRAGMA journal_mode = WAL`)

### Backing Up Data

All application state lives in two locations:

1. **Database**: `database/database.sqlite` (plus `-wal` / `-shm` files if active)
2. **Uploaded Media**: `storage/app/public/`

To create a consistent backup:

```bash
sqlite3 database/database.sqlite ".backup 'backup-database.sqlite'"
tar -czf backup-storage.tar.gz storage/app/public
```

---

## Testing

Run the automated test suite with PHPUnit:

```bash
php artisan test
```

The test suite verifies:
- All public routes (`200 OK` with seeded data), search queries, pagination, and repeated requests with the database cache store
- Unique per-session `views_count` increments, HTML sanitization (`symfony/html-sanitizer`), and automatic excerpt generation
- Google Form URL normalization (`docs.google.com`, `<iframe src>`, `forms.gle`, host whitelist validation), native form field rendering, checkbox array serialization, error handling when upstream Google Form submission fails, and registration open/closed states
- Model `is_active` / `is_published` state preservation and automatic unique slug deduplication
- Admin authentication protection (`/admin` redirects guests to `/admin/login`, authenticated admins can access all Filament resources and pages)

---

## Assumptions & Design Notes

- **Placeholder Assets**: To avoid scraping or hotlinking external photos, `App\Services\PlaceholderAssetGenerator` deterministically generates local SVG emblems (`storage/app/public/branding/logo-ansor.svg` and `storage/app/public/bodies/*.svg`) and responsive WebP illustrations (`officers/*.webp`, `posts/*.webp`, `forms/*.webp`) during `php artisan migrate --seed`. Every image is replaceable via the Filament admin panel.
- **Image Processing**: `App\Services\ImageService` generates `480w`, `800w`, and `1280w` WebP variants using PHP's `gd` extension when available, with an automatic fallback to Python 3 Pillow (`PIL`) on environments without `ext-gd`. Existing up-to-date variants are skipped automatically for fast seeding and test execution.
