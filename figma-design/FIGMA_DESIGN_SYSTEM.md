# 📘 GymPulse — Figma Design System & UI Specification (Clean Athletic)

Dokumen ini memuat standar desain, skala visual, hierarki tipografi, palet token warna, serta aturan komponen antarmuka (*Design System*) yang **persis 100% mencerminkan implementasi pada codebase GymPulse Laravel 11** (`landing.css`, `admin.css`, `dashboard.blade.php`, dan `welcome.blade.php`).

---

## 🎨 1. Color Palette & Token System

### A. Brand & Accent Colors (Volt Lime & Slate)
| Nama Token CSS | Hex Code | Figma Style Name | Penggunaan / Role |
| :--- | :--- | :--- | :--- |
| `--gp-lime` | `#84CC16` | `Brand/Volt-Lime` | Aksen warna utama, dot logo `GymPulse.`, status aktif. |
| `--gp-lime-dark` | `#65A30D` | `Brand/Volt-Lime-Dark` | Teks highlight, hover button, outline pill. |
| `--gp-lime-soft` | `#ECFCCB` | `Brand/Volt-Lime-Soft` | Background badge `BUKA HARI INI`, badge paket PRO. |
| `--gp-primary` | `#0F172A` | `Slate/900-Primary` | Sidebar admin, tombol aksi utama (`btn-primary-action`). |
| `--gp-primary-soft`| `#1E293B` | `Slate/800-Soft` | Item sidebar aktif, kartu hover, status card. |
| `--gp-bg` | `#F8FAFC` | `Surface/Slate-50` | Background kanvas aplikasi (*Clean Athletic Light Base*). |
| `--gp-card` | `#FFFFFF` | `Surface/White-Card` | Panel kartu konten, tabel, dan formulir input. |
| `--gp-border` | `#E2E8F0` | `Border/Slate-200` | Garis batas standar container dan kartu. |
| `--gp-emerald` | `#10B981` | `Status/Emerald-Success` | Status check-in berhasil, badge status `Sepi & Segar`. |
| `--gp-coral` | `#EF4444` | `Status/Coral-Danger` | Status kadaluwarsa, badge `Jam Sibuk`, tombol batal. |
| `--gp-sky` | `#0EA5E9` | `Category/Sky-Drinks` | Kategori minuman POS kasir, status information. |

---

## 🔤 2. Typography Scale

Sistem tipografi aplikasi menggunakan kombinasi font resmi:
- **Headings & Display**: `'Space Grotesk'`, sans-serif
- **Body & UI**: `'Inter'`, sans-serif
- **Code & UID RFID**: `'JetBrains Mono'`, monospace

| Tingkat Hierarki | Font Family | Size (px) | Weight | Contoh Penggunaan di Aplikasi |
| :--- | :--- | :---: | :---: | :--- |
| **Hero Display** | `Space Grotesk` | `46px` | `700 (Bold)` | Headline Landing Page `welcome.blade.php` |
| **Heading 1 (H1)** | `Space Grotesk` | `36px` | `700 (Bold)` | Judul section paket membership & fasilitas |
| **Heading 2 (H2)** | `Space Grotesk` | `24px` | `700 (Bold)` | Judul kartu paket & metrik dashboard |
| **Heading 3 (H3)** | `Space Grotesk` | `18px` | `700 (Bold)` | Sub-judul widget RFID & kasir POS |
| **Body Large** | `Inter` | `16px` | `400 (Regular)` / `500` | Paragraf hero & deskripsi paket |
| **Body Regular** | `Inter` | `14px` | `400 (Regular)` / `500` | Teks UI, item tabel, form input |
| **Small / Badge** | `Inter` | `11px - 12px` | `700 (Bold)` | Status pill (`BUKA HARI INI`, `PRO GYM`) |
| **Monospace** | `JetBrains Mono` | `12px` | `600 (Semibold)`| RFID UID Card `4A:9C:12:F1` & SKU Produk |

---

## 📐 3. Layout Grid & Auto-Layout Rules

### A. Desktop Grid (1440px Canvas)
- **Columns**: `12 Columns`
- **Margin**: `80px` (Landing Page) / `24px` (Admin & POS)
- **Gutter**: `24px`
- **Sidebar Width (Admin/Kasir)**: `260px` (`#0F172A`)
- **Cart Drawer Width (POS Kasir)**: `470px` (`#FFFFFF`)

### B. Mobile Grid (390px Canvas - iPhone Standard)
- **Columns**: `4 Columns`
- **Margin**: `20px`
- **Gutter**: `12px`
- **Radius Card**: `20px`

---

## 🔘 4. Component Library Specifications

### A. Tombol (*Buttons*)
1. **Primary Action Button (`.btn-primary-action`)**:
   - Background: `#0F172A`
   - Text: `#FFFFFF`, Font: `Space Grotesk 14px / 700 Bold`
   - Radius: `12px`, Shadow: `0 4px 12px rgba(15, 23, 42, 0.15)`
   - Hover: `#1E293B`
2. **Volt Lime CTA Button**:
   - Background: `#84CC16`
   - Text: `#0F172A`, Font: `Inter 14px / 700 Bold`
   - Radius: `12px`
3. **Outline Clean Button (`.btn-outline`)**:
   - Background: `#FFFFFF`, Border: `1px solid #CBD5E1`
   - Text: `#1E293B`, Font: `Inter 14px / 600 Semibold`
   - Radius: `10px`

### B. Panel & Kartu (*Clean Panel*)
- Class: `.clean-panel`
- Background: `#FFFFFF`
- Border: `1px solid #E2E8F0`
- Radius: `1.25rem` (`20px`)
- Box Shadow: `0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02)`
