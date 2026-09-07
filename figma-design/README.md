# 🎨 GymPulse — Figma Design System & Mockups Package (Clean Athletic)

Paket desain Figma resmi yang **100% disesuaikan dengan arsitektur visual, palet warna, dan tata letak sebenarnya dari project GymPulse** (`welcome.blade.php`, `admin/dashboard.blade.php`, `cashier/pos/index.blade.php`, dan `landing.css`).

---

## 📂 Struktur Berkas & Folder

```
figma-design/
├── README.md                           # Panduan penggunaan & cara import ke Figma
├── FIGMA_DESIGN_SYSTEM.md              # Spesifikasi lengkap UI Kit & Design Tokens
├── figma_tokens.json                   # Token Desain standar JSON (Volt Lime & Slate)
├── svg-mockups/                        # Vector Mockup siap drag-and-drop ke Figma
│   ├── 01_landing_page_desktop.svg     # Landing Page Web (welcome.blade.php) - 1440 × 3200 px
│   ├── 02_admin_dashboard.svg          # Admin Dashboard & Live RFID Monitor - 1440 × 960 px
│   ├── 03_cashier_pos_terminal.svg     # Kasir POS Store Workstation - 1440 × 960 px
│   ├── 04_member_mobile_portal.svg     # Member Mobile Experience & RFID Card - 390 × 844 px
│   └── 05_ui_kit_components.svg        # Clean Athletic UI Kit & Components - 1440 × 1200 px
└── interactive-preview/                # Interactive Viewer di Browser
    ├── index.html                      # Halaman showcase tab interaktif
    └── style.css                       # Styling Clean Athletic Slate & Volt Lime
```

---

## 🎯 Penyesuaian dengan Project Sebenarnya

1. **Palet Warna**:
   - Background Utama: `#F8FAFC` (*Slate-50 Clean Athletic*)
   - Warna Brand Utama: `#84CC16` (*Volt Lime*) & `#65A30D` (*Lime Dark*)
   - Badge Soft: `#ECFCCB` (*Lime-100*)
   - Panel & Tombol Gelap: `#0F172A` (*Slate-900*) & `#1E293B`
   - Border Standar: `#E2E8F0` (*Slate-200*)
2. **Tipografi Resmi**:
   - Heading & Display: `'Space Grotesk'`
   - Body UI: `'Inter'`
   - UID / Barcode: `'JetBrains Mono'`
3. **Komponen Real**:
   - Status bar atas: *"BUKA HARI INI 06:00 - 22:00 WIB"* dengan pulse dot
   - Monitor Check-in RFID Real-time dengan avatar member, UID, dan ribbon konfirmasi
   - POS Kasir dengan filter kategori (*Semua, 🥤 Minuman, ⚡ Suplemen, 🍫 Snack*) dan kalkulator kembalian
   - Status kepadatan gym: *"Sepi & Segar" / "Jam Sibuk"*

---

## 🚀 3 Cara Cepat Menggunakan di Figma

### Cara 1: Drag & Drop ke Canvas Figma
- Buka folder [`figma-design/svg-mockups/`](svg-mockups/) lalu **tarik (*drag & drop*)** file `.svg` langsung ke kanvas Figma.

### Cara 2: Salin via Interactive Showcase
- Buka file [`figma-design/interactive-preview/index.html`](interactive-preview/index.html) di browser, klik tombol **"📋 Copy Vector ke Figma"**, lalu tekan **`Ctrl + V`** di Figma.

### Cara 3: Import Variable Tokens JSON
- Gunakan fitur *Figma Variables* / plugin *Tokens Studio* dan impor berkas [`figma_tokens.json`](figma_tokens.json).
