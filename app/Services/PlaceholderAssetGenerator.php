<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;

class PlaceholderAssetGenerator
{
    public function generateAll(bool $force = false): void
    {
        $disk = Storage::disk('public');

        foreach (['branding', 'bodies', 'officers', 'posts', 'forms', 'placeholders'] as $dir) {
            if (! $disk->exists($dir)) {
                $disk->makeDirectory($dir);
            }
        }

        if ($force || ! $disk->exists('branding/logo-ansor.svg') || ! $disk->exists('bodies/balantas.svg')) {
            $this->generateBrandAndBodySvgs();
        }

        if ($force || ! $disk->exists('officers/ketua-umum.webp') || ! $disk->exists('posts/posko-ramadhan.webp')) {
            $this->generateRasterWebpAssets();
        }
    }

    private function generateBrandAndBodySvgs(): void
    {
        $disk = Storage::disk('public');

        // 1. Main GP Ansor Emblem (Green triangle with crescent, 9 stars, and ANSOR ribbon)
        $ansorLogo = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" fill="none">
  <defs>
    <linearGradient id="ansorGreen" x1="0" y1="0" x2="120" y2="120" gradientUnits="userSpaceOnUse">
      <stop offset="0%" stop-color="#16a34a"/>
      <stop offset="100%" stop-color="#065f46"/>
    </linearGradient>
  </defs>
  <polygon points="60,8 112,96 8,96" fill="url(#ansorGreen)" stroke="#fde047" stroke-width="3" stroke-linejoin="round"/>
  <polygon points="60,17 103,91 17,91" fill="none" stroke="#ffffff" stroke-width="1.2" stroke-opacity="0.7"/>
  <!-- Crescent & Stars -->
  <path d="M64 46A13 13 0 1 0 64 70A10.5 10.5 0 1 1 64 46Z" fill="#ffffff"/>
  <polygon points="60,26 61.5,30 65.5,30 62.2,32.4 63.5,36.5 60,34 56.5,36.5 57.8,32.4 54.5,30 58.5,30" fill="#fde047"/>
  <circle cx="46" cy="40" r="2" fill="#fde047"/>
  <circle cx="74" cy="40" r="2" fill="#fde047"/>
  <circle cx="38" cy="54" r="2" fill="#fde047"/>
  <circle cx="82" cy="54" r="2" fill="#fde047"/>
  <circle cx="32" cy="70" r="2" fill="#fde047"/>
  <circle cx="88" cy="70" r="2" fill="#fde047"/>
  <circle cx="48" cy="78" r="2" fill="#fde047"/>
  <circle cx="72" cy="78" r="2" fill="#fde047"/>
  <!-- Bottom Banner -->
  <rect x="12" y="98" width="96" height="15" rx="4" fill="#15803d" stroke="#fde047" stroke-width="1.5"/>
  <text x="60" y="109" text-anchor="middle" fill="#ffffff" font-family="Inter, Arial, sans-serif" font-weight="800" font-size="10" letter-spacing="2">ANSOR</text>
</svg>
SVG;
        $disk->put('branding/logo-ansor.svg', $ansorLogo);

        // 2. MDS Rijalul Ansor (Green triangle with red/gold center & calligraphy bar)
        $mdsSvg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" fill="none">
  <polygon points="60,12 110,98 10,98" fill="#15803d" stroke="#166534" stroke-width="3" stroke-linejoin="round"/>
  <polygon points="60,22 98,88 22,88" fill="#16a34a" stroke="#bbf7d0" stroke-width="1.5"/>
  <polygon points="60,44 78,76 42,76" fill="#dc2626"/>
  <polygon points="60,30 62,36 68,36 63,40 65,46 60,42 55,46 57,40 52,36 58,36" fill="#facc15"/>
  <circle cx="46" cy="52" r="2.2" fill="#ffffff"/>
  <circle cx="74" cy="52" r="2.2" fill="#ffffff"/>
  <circle cx="38" cy="68" r="2.2" fill="#ffffff"/>
  <circle cx="82" cy="68" r="2.2" fill="#ffffff"/>
  <rect x="20" y="78" width="80" height="16" rx="2" fill="#065f46"/>
  <text x="60" y="89" text-anchor="middle" fill="#fde047" font-family="Inter, Arial, sans-serif" font-weight="800" font-size="8.5" letter-spacing="0.8">RIJALUL ANSOR</text>
</svg>
SVG;
        $disk->put('bodies/mds-rijalul-ansor.svg', $mdsSvg);

        // 3. Baritim (Pentagon maritime emblem in blue, green, gold with anchor)
        $baritimSvg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" fill="none">
  <polygon points="60,8 112,44 92,108 28,108 8,44" fill="#16a34a" stroke="#ca8a04" stroke-width="4" stroke-linejoin="round"/>
  <polygon points="60,16 103,47 86,100 34,100 17,47" fill="#0284c7" stroke="#fde047" stroke-width="2"/>
  <circle cx="60" cy="60" r="26" fill="#1d4ed8" stroke="#facc15" stroke-width="2.5"/>
  <!-- Gold Anchor -->
  <circle cx="60" cy="43" r="4" stroke="#facc15" stroke-width="2.5" fill="none"/>
  <line x1="60" y1="47" x2="60" y2="75" stroke="#facc15" stroke-width="3.5" stroke-linecap="round"/>
  <line x1="50" y1="54" x2="70" y2="54" stroke="#facc15" stroke-width="3" stroke-linecap="round"/>
  <path d="M46 65 C46 76, 74 76, 74 65" stroke="#facc15" stroke-width="3.5" fill="none" stroke-linecap="round"/>
  <polygon points="60,23 61.5,27 65.5,27 62.2,29.5 63.5,33.5 60,31 56.5,33.5 57.8,29.5 54.5,27 58.5,27" fill="#fde047"/>
  <text x="60" y="95" text-anchor="middle" fill="#fde047" font-family="Inter, Arial, sans-serif" font-weight="800" font-size="8.5" letter-spacing="1.2">BARITIM</text>
</svg>
SVG;
        $disk->put('bodies/baritim.svg', $baritimSvg);

        // 4. Banser (Red & Gold octagonal shield emblem)
        $banserSvg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" fill="none">
  <polygon points="38,10 82,10 108,36 108,76 60,110 12,76 12,36" fill="#b91c1c" stroke="#facc15" stroke-width="4" stroke-linejoin="round"/>
  <polygon points="42,18 78,18 100,40 100,72 60,100 20,72 20,40" fill="#dc2626" stroke="#fde047" stroke-width="1.8"/>
  <polygon points="60,28 84,68 36,68" fill="#15803d" stroke="#facc15" stroke-width="2"/>
  <polygon points="60,36 62,42 68,42 63,45 65,51 60,47 55,51 57,45 52,42 58,42" fill="#fde047"/>
  <rect x="30" y="73" width="60" height="13" rx="3" fill="#facc15"/>
  <text x="60" y="82.5" text-anchor="middle" fill="#7f1d1d" font-family="Inter, Arial, sans-serif" font-weight="800" font-size="9" letter-spacing="1.5">BANSER</text>
</svg>
SVG;
        $disk->put('bodies/banser.svg', $banserSvg);

        // 5. Banaar (Dark Green & Black shield with gold border & white figure)
        $banaarSvg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" fill="none">
  <path d="M16 20 L60 12 L104 20 L96 76 L60 110 L24 76 Z" fill="#052e16" stroke="#ca8a04" stroke-width="4" stroke-linejoin="round"/>
  <path d="M24 27 L60 20 L96 27 L89 72 L60 100 L31 72 Z" fill="#15803d" stroke="#fde047" stroke-width="1.5"/>
  <text x="60" y="36" text-anchor="middle" fill="#fde047" font-family="Inter, Arial, sans-serif" font-weight="800" font-size="10" letter-spacing="1.5">BANAAR</text>
  <circle cx="60" cy="52" r="6" fill="#ffffff"/>
  <path d="M42 46 Q60 60 78 46 L68 78 L60 92 L52 78 Z" fill="#ffffff"/>
  <polygon points="60,39 61.2,42 64.5,42 61.8,44 62.8,47 60,45 57.2,47 58.2,44 55.5,42 58.8,42" fill="#facc15"/>
</svg>
SVG;
        $disk->put('bodies/banaar.svg', $banaarSvg);

        // 6. Balantas (Blue & gold winged wheel emblem)
        $balantasSvg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" fill="none">
  <!-- Wings -->
  <path d="M6 44 L44 50 L38 72 L14 66 Z" fill="#1d4ed8" stroke="#93c5fd" stroke-width="1.5"/>
  <path d="M12 52 L42 56 L38 66 L18 62 Z" fill="#3b82f6"/>
  <path d="M114 44 L76 50 L82 72 L106 66 Z" fill="#1d4ed8" stroke="#93c5fd" stroke-width="1.5"/>
  <path d="M108 52 L78 56 L82 66 L102 62 Z" fill="#3b82f6"/>
  <!-- Center Shield & Wheel -->
  <circle cx="60" cy="58" r="22" fill="#1e3a8a" stroke="#facc15" stroke-width="3"/>
  <circle cx="60" cy="58" r="14" fill="#dc2626" stroke="#fde047" stroke-width="2"/>
  <polygon points="60,47 70,65 50,65" fill="#16a34a" stroke="#ffffff" stroke-width="1"/>
  <rect x="34" y="82" width="52" height="12" rx="6" fill="#eff6ff" stroke="#1d4ed8" stroke-width="1.5"/>
  <text x="60" y="90.5" text-anchor="middle" fill="#1e3a8a" font-family="Inter, Arial, sans-serif" font-weight="800" font-size="7.5" letter-spacing="1">BALANTAS</text>
</svg>
SVG;
        $disk->put('bodies/balantas.svg', $balantasSvg);
    }

    private function generateRasterWebpAssets(): void
    {
        $storagePath = Storage::disk('public')->path('');

        $pythonScript = <<<'PY'
import sys, os
from PIL import Image, ImageDraw, ImageFont

base_dir = sys.argv[1]

def get_font(size, bold=False):
    candidates = [
        "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf" if bold else "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
        "/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf" if bold else "/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf",
    ]
    for path in candidates:
        if os.path.exists(path):
            try:
                return ImageFont.truetype(path, size)
            except Exception:
                pass
    return ImageFont.load_default()

def save_with_variants(img, rel_path, widths=(480, 800, 1280)):
    full_path = os.path.join(base_dir, rel_path)
    os.makedirs(os.path.dirname(full_path), exist_ok=True)
    img.save(full_path, format="WEBP", quality=85, method=4)
    stem, _ = os.path.splitext(full_path)
    orig_w, orig_h = img.size
    for w in widths:
        tw = min(orig_w, w)
        th = max(1, int(round(orig_h * (tw / float(orig_w)))))
        resized = img.resize((tw, th), Image.Resampling.LANCZOS)
        resized.save(f"{stem}-{w}w.webp", format="WEBP", quality=82, method=4)

def make_gradient(w, h, c1, c2, diagonal=True):
    base = Image.new("RGB", (w, h), c1)
    top = Image.new("RGB", (w, h), c2)
    mask = Image.new("L", (w, h))
    mask_data = []
    for y in range(h):
        for x in range(w):
            if diagonal:
                t = (x / float(max(1, w - 1)) * 0.55) + (y / float(max(1, h - 1)) * 0.45)
            else:
                t = y / float(max(1, h - 1))
            mask_data.append(int(max(0, min(255, t * 255))))
    mask.putdata(mask_data)
    base.paste(top, (0, 0), mask)
    return base

# 1. Generate 3 Officer Portraits (800x700, 8:7 aspect ratio matching Screenshot 3)
officers_spec = [
    {
        "file": "officers/ketua-umum.webp",
        "bg1": (215, 222, 228),
        "bg2": (188, 198, 208),
        "flag": True,
        "suit": (22, 163, 74),
        "tie": None,
        "badge_text": "KETUA UMUM",
        "name_tag": "KETUA UMUM",
        "banner": None,
    },
    {
        "file": "officers/sekretaris-jenderal.webp",
        "bg1": (218, 212, 74),
        "bg2": (58, 128, 72),
        "flag": False,
        "suit": (21, 128, 61),
        "tie": None,
        "badge_text": "SEKRETARIS JENDERAL",
        "name_tag": "SEKJEN",
        "banner": None,
    },
    {
        "file": "officers/bendahara-umum.webp",
        "bg1": (36, 98, 74),
        "bg2": (18, 52, 42),
        "flag": False,
        "suit": (22, 138, 72),
        "tie": (15, 92, 52),
        "badge_text": "BENDAHARA UMUM",
        "name_tag": "BENDUM",
        "banner": "BENDAHARA UMUM",
    },
]

for spec in officers_spec:
    W, H = 800, 700
    img = make_gradient(W, H, spec["bg1"], spec["bg2"])
    draw = ImageDraw.Draw(img, "RGBA")

    if spec["flag"]:
        # Stylized red-and-white diagonal flag wave in background
        draw.polygon([(80, 0), (520, 0), (760, 420), (240, 380)], fill=(206, 28, 28, 230))
        draw.polygon([(240, 320), (760, 380), (800, 700), (140, 700)], fill=(248, 250, 252, 225))
    else:
        # Subtle organizational pattern circles
        for r in range(120, 420, 70):
            draw.ellipse([W - r - 40, 40, W + r - 40, 40 + r * 2], outline=(255, 255, 255, 28), width=3)

    # Shoulders / Green Ansor Jas Suit
    suit_color = spec["suit"]
    draw.Status = None
    draw.rounded_rectangle([130, 410, 670, 760], radius=140, fill=suit_color, outline=(10, 80, 40), width=4)

    # White inner shirt V-neck
    draw.polygon([(330, 420), (470, 420), (400, 580)], fill=(250, 252, 250))

    if spec["tie"]:
        draw.polygon([(386, 445), (414, 445), (422, 565), (400, 590), (378, 565)], fill=spec["tie"])
    else:
        # Mandarin collar buttons
        draw.line([(400, 445), (400, 575)], fill=(210, 220, 210), width=3)
        for by in (475, 515, 550):
            draw.ellipse([395, by - 5, 405, by + 5], fill=(30, 40, 35))

    # Lapels
    draw.polygon([(330, 420), (265, 500), (365, 600), (400, 575)], fill=(16, 112, 54))
    draw.polygon([(470, 420), (535, 500), (435, 600), (400, 575)], fill=(16, 112, 54))

    # Chest Pocket Name Tag & Emblem
    draw.rectangle([220, 545, 330, 572], fill=(20, 95, 48), outline=(250, 204, 21), width=2)
    draw.text((275, 558), spec["name_tag"], fill=(255, 255, 255), font=get_font(14, True), anchor="mm")
    # Gold Ansor Triangle pin on lapel
    draw.polygon([(495, 515), (515, 550), (475, 550)], fill=(250, 204, 21))

    # Neck
    draw.rounded_rectangle([352, 340, 448, 445], radius=28, fill=(224, 178, 138))

    # Head / Face
    draw.ellipse([295, 145, 505, 405], fill=(235, 192, 152), outline=(210, 165, 125), width=2)
    # Ears
    draw.ellipse([278, 250, 308, 315], fill=(228, 182, 142))
    draw.ellipse([492, 250, 522, 315], fill=(228, 182, 142))

    # Eyes, Eyebrows & Warm Smile
    draw.arc([332, 232, 382, 252], start=190, end=350, fill=(45, 35, 30), width=5)
    draw.arc([418, 232, 468, 252], start=190, end=350, fill=(45, 35, 30), width=5)
    draw.ellipse([348, 255, 368, 273], fill=(35, 28, 24))
    draw.ellipse([432, 255, 452, 273], fill=(35, 28, 24))
    draw.line([(400, 265), (394, 312), (410, 316)], fill=(195, 145, 108), width=4)
    draw.arc([362, 322, 438, 360], start=10, end=170, fill=(145, 72, 62), width=5)

    # Black Peci / Songkok Nasional
    draw.polygon([(295, 195), (312, 96), (488, 96), (505, 195)], fill=(20, 24, 28))
    draw.chord([295, 172, 505, 216], start=180, end=360, fill=(30, 35, 42))

    if spec["banner"]:
        # Stylized diagonal banner at bottom matching Screenshot 3 card 3
        banner_poly = [(0, 610), (800, 515), (800, 645), (0, 700)]
        draw.polygon(banner_poly, fill=(240, 248, 244, 225))
        draw.text((400, 610), spec["banner"], fill=(15, 45, 30), font=get_font(38, True), anchor="mm")

    save_with_variants(img, spec["file"], widths=(480, 800))

# 2. Generate 12 News & Hero 16:9 Cover Images (1280x720)
scenes = [
    ("posts/posko-ramadhan.webp", (15, 82, 52), (6, 46, 34), "POSKO RAMADHAN", "Gerakan Pemuda Ansor", "Pelayanan Mudik & Masyarakat"),
    ("posts/koperasi-mart.webp", (21, 128, 61), (13, 74, 42), "ANSOR MART & KOPERASI", "Kemandirian Ekonomi Kader", "Pimpinan Pusat GP Ansor"),
    ("posts/extraliga-santri.webp", (18, 95, 68), (15, 52, 78), "EXTRALIGA SANTRI", "40 Pesantren Berlaga", "Olahraga & Ukhuwah Santri"),
    ("posts/rakor-banser.webp", (30, 88, 58), (22, 48, 42), "SATKORWIL BANSER", "Rapat Koordinasi Pengamanan", "Siaga Kawal Kegiatan Nasional"),
    ("posts/pelantikan-wilayah.webp", (16, 112, 62), (8, 56, 44), "PELANTIKAN PENGURUS", "Pimpinan Wilayah GP Ansor", "Penguatan Struktur Organisasi"),
    ("posts/kerjasama-lembaga.webp", (20, 102, 78), (14, 58, 68), "SINERGI KELEMBAGAAN", "Penandatanganan Kerja Sama", "Edukasi & Kepatuhan Publik"),
    ("posts/pkn-kaderisasi.webp", (12, 76, 48), (6, 36, 26), "PKN & KADERISASI", "Pelatihan Kepemimpinan Nasional", "Khidmat Ulama & Bangsa"),
    ("posts/konferancab-kader.webp", (22, 120, 64), (11, 62, 38), "KONFERANCAB ANSOR", "Konsolidasi Kader Tingkat Cabang", "Komitmen Khidmat Organisasi"),
    ("posts/advokasi-pekerja.webp", (28, 78, 98), (14, 42, 62), "ADVOKASI & HUKUM", "Pendampingan Hak Pekerja", "Lembaga Bantuan Hukum Ansor"),
    ("posts/gowes-kemerdekaan.webp", (158, 38, 38), (20, 92, 54), "SEMARAK KEMERDEKAAN", "Gowes Kebangsaan Bersama Masyarakat", "Merajut Persatuan Nusantara"),
    ("posts/ketahanan-pangan.webp", (24, 118, 52), (68, 98, 24), "KETAHANAN PANGAN", "Gerakan 100 Ribu Petani Muda", "Kedaulatan Pangan Nasional"),
    ("posts/donasi-sosial.webp", (18, 104, 82), (12, 54, 48), "BAKTI SOSIAL BANSER", " Tanggap Darurat & Kemanusiaan", "Peduli Sesama Warga"),
    ("placeholders/news-default.webp", (22, 163, 74), (6, 95, 70), "GERAKAN PEMUDA ANSOR", "Berita & Informasi Terkini", "Khidmat Untuk Agama dan Bangsa"),
    ("forms/pendaftaran-kader.webp", (16, 132, 66), (5, 72, 52), "PENDAFTARAN KADER", "Gerakan Pemuda Ansor & Banser", "Bergabung Bersama Barisan Muda"),
]

for rel_path, c1, c2, badge, heading, sub in scenes:
    W, H = 1280, 720
    img = make_gradient(W, H, c1, c2)
    draw = ImageDraw.Draw(img, "RGBA")

    # Architectural / Geometric Stage Elements
    for i in range(6):
        offset = i * 110
        draw.arc([W - 550 - offset, -120, W + 150 - offset, 680], start=110, end=260, fill=(255, 255, 255, 24), width=4)

    # Pavilion / Stage Backdrop Frame in upper-middle zone
    draw.rounded_rectangle([110, 60, W - 110, H - 80], radius=24, outline=(255, 255, 255, 38), width=2)
    draw.rounded_rectangle([140, 145, W - 140, 290], radius=16, fill=(6, 48, 32, 215), outline=(74, 222, 128, 130), width=2)

    # Top Badge Pill inside stage frieze
    draw.rounded_rectangle([170, 162, 450, 202], radius=20, fill=(250, 204, 21, 240))
    draw.text((310, 182), badge, fill=(6, 78, 59), font=get_font(18, True), anchor="mm")

    # Stage Frieze Heading (sits in upper half so hero bottom text overlay never collides)
    draw.text((170, 232), heading, fill=(255, 255, 255, 235), font=get_font(30, True), anchor="lm")
    draw.text((170, 266), sub, fill=(187, 247, 208, 210), font=get_font(20, False), anchor="lm")

    # Emblem Triangle in top-right of stage frieze
    draw.polygon([(1055, 165), (1105, 250), (1005, 250)], fill=(22, 163, 74, 235), outline=(250, 204, 21, 255), width=3)
    draw.text((1055, 232), "ANSOR", fill=(255, 255, 255), font=get_font(13, True), anchor="mm")

    # Group of stylized figures in mid-ground (y=320..580)
    fig_positions = [280, 450, 640, 830, 1000]
    for idx, fx in enumerate(fig_positions):
        fy = 395 if idx % 2 == 0 else 410
        jacket = (22, 163, 74, 235) if idx != 2 else (21, 128, 61, 245)
        draw.rounded_rectangle([fx - 64, fy, fx + 64, fy + 220], radius=36, fill=jacket, outline=(255, 255, 255, 65), width=2)
        draw.polygon([(fx - 18, fy), (fx + 18, fy), (fx, fy + 48)], fill=(240, 245, 240, 235))
        draw.ellipse([fx - 32, fy - 76, fx + 32, fy - 10], fill=(226, 186, 146, 245))
        draw.rectangle([fx - 32, fy - 88, fx + 32, fy - 60], fill=(20, 25, 30, 250))

    save_with_variants(img, rel_path, widths=(480, 800, 1280))
PY;

        try {
            $process = new Process(['python3', '-c', $pythonScript, $storagePath]);
            $process->setTimeout(60);
            $process->run();
        } catch (Throwable) {
            // Ignore if python3 is unavailable; fallback SVG or GD can be used
        }
    }
}
