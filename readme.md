
# XIV Apparel - Minimalist E-Commerce Architecture (`xiv-apparel-theme`)

[![WordPress](https://img.shields.io/badge/WordPress-6.x%2B-blue.svg)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-8.x%2B-purple.svg)](https://woocommerce.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v3.x-38B2AC.svg)](https://tailwindcss.com)

An ultra-minimalist, high-performance WooCommerce custom theme architecture for **XIV Apparel** ("XIV COLLECTIONS 23-24").

Inspired by editorial fashion UI/UX, this project features grain-texture canvas backgrounds, bold typography headers, interactive multi-facet filtering (Size, Category, Price, Availability), side-drawer shopping bag, and streamlined guest/user checkout flows.

---

## 🎨 Visual Identity & Key Features

- **Design Philosophy:** Avant-garde editorial minimalism, high-contrast typography, noise/grain textured background `#f4f4f2`.
- **Responsive Layouts:** Full desktop editorial grid and touch-optimized mobile web interface.
- **Core Views:**
  1. **Home / Collection Hero:** Featured collections ("NEW THIS WEEK", "SUMMER 2024"), category quick links (Men, Women, Kids).
  2. **Products Catalog (PLP):** Multi-attribute sidebar filter (Size: XS-2X, Availability, Color, Category, Price Range) + Category Pills.
  3. **Product Detail Page (PDP):** Multi-angle gallery thumbnails, color swatches, size picker with "Find Your Size" guide, and sticky Add-to-Cart drawer.
  4. **Shopping Bag & Checkout:** Sliding cart drawer, step-by-step Checkout (Information -> Shipping -> Payment), and order summary preview.

---

## 📁 Repository Documentation Matrix

| File                                | Scope                   | Description                                                                                    |
| :---------------------------------- | :---------------------- | :--------------------------------------------------------------------------------------------- |
| [`README.md`](./README.md)         | **Overview**      | Project identity, tech stack, installation, and folder structure.                              |
| [`guidelines.md`](./guidelines.md) | **Engineering**   | UI component rules, WooCommerce hook overrides, responsive breakpoints, and WCAG a11y.         |
| [`styles.md`](./styles.md)         | **Design System** | Color tokens, grain background texture, typography hierarchy, and Tailwind configuration.      |
| [`tables.md`](./tables.md)         | **Database**      | Custom database tables for size recommendations, stock reservations, and PostgreSQL readiness. |
| [`forms.md`](./forms.md)           | **Forms & AJAX**  | Checkout step-form markup, AJAX add-to-cart, filter queries, and security Nonces.              |
| [`goals.md`](./goals.md)           | **SLA & Roadmap** | Core Web Vitals SLA (LCP < 1.8s), mobile conversion roadmap, and speed benchmarks.             |
| [`xiv-apparel-theme/`](./xiv-apparel-theme) | **Theme Code** | Implementasi penuh theme WordPress/WooCommerce (Tailwind + vanilla JS).                |

---

## 🛠️ Tech Stack

- **CMS & Core Engine:** WordPress 6.x+ / WooCommerce 8.x+
- **Backend Language:** PHP 8.2+
- **Database:** MySQL 8.0+ / MariaDB 10.6+ *(PostgreSQL / PG4WP Ready)*
- **Frontend Assets:** Tailwind CSS v3.x, Vanilla JavaScript (ES6+, zero jQuery dependency), esbuild
- **Icons & Graphics:** Geometric custom SVG icons (minimalist diamond logo, search glass, sleek bag, user circle)

---

## 📦 Theme Structure (`xiv-apparel-theme/`)

```
xiv-apparel-theme/
├── style.css               # Theme header + fallback styles
├── functions.php           # Bootstrap (memuat semua modul inc/)
├── header.php / footer.php # Navbar, mobile menu, search, cart drawer
├── front-page.php          # Home / Collection Hero (NEW THIS WEEK)
├── index.php / page.php    # Template umum
├── woocommerce.php         # Wrapper untuk halaman Cart/Checkout/Account
├── woocommerce/
│   ├── archive-product.php # PLP + sidebar filter + AJAX grid
│   ├── single-product.php  # PDP + gallery + "Find Your Size" modal
│   └── content-product.php # Kartu produk (rasio 3:4)
├── inc/
│   ├── setup.php           # Theme supports, menu, image sizes
│   ├── enqueue.php         # Font, CSS, JS + lokalization (XIV global)
│   ├── helpers.php         # Logo, canvas, helpers
│   ├── woocommerce-hooks.php # Override gallery, add-to-cart, pills
│   ├── cart.php            # AJAX add-to-cart + cart drawer fragments
│   ├── ajax.php            # AJAX filter, size guide, newsletter
│   ├── size-guides.php     # Tabel wp_xiv_size_guides + seeder
│   └── admin-crud.php      # Admin panel CRUD produk (menu "XIV")
├── assets/
│   ├── src/css/app.css     # Sumber Tailwind (grain texture, drawer)
│   ├── src/js/             # app / filters / cart / checkout (vanilla JS)
│   ├── admin/              # Asset panel admin (media uploader, CSS)
│   └── dist/               # Hasil build (di-generate)
├── tailwind.config.js      # Prefix xiv- + design tokens (frontend)
├── tailwind.admin.config.js # Build Tailwind khusus admin (tanpa preflight)
├── postcss.config.js
├── package.json            # Scripts dev/build via tailwind + esbuild
└── .gitignore
```

---

## 🚀 Quick Start for Developers

```bash
# 1. Copy/symlink theme ke direktori themes WordPress
cp -r xiv-apparel-theme wp-content/themes/

# 2. Install Node dependencies
cd wp-content/themes/xiv-apparel-theme
npm install

# 3. Development Watcher (Hot Reloading CSS + JS)
npm run dev:all

# 4. Production Build
npm run build:all
```

> **Aktivasi theme** → Appearance › Themes › **XIV Apparel**. Tabel `wp_xiv_size_guides` + seed data dibuat otomatis saat theme diaktifkan. Pastikan WooCommerce sudah terpasang & aktif, dan set halaman Shop/Checkout/Cart/My Account.

---

## 🛍️ Admin Panel CRUD Produk

Dashboard WordPress → menu **XIV** menyediakan panel CRUD produk tanpa membuka WooCommerce:

- **Produk** — daftar produk (cari, filter kategori, pagination, Edit/Hapus/Lihat)
- **Tambah Produk** — nama, deskripsi, harga (reguler & promo), SKU, stok, kategori (pilih atau buat baru), gambar utama & galeri (via media library)
- **Tipe produk:** *Simple* (harga tunggal) atau *Variable* (ukuran XS–2X dengan harga & stok per ukuran → otomatis jadi variasi WooCommerce)
- Semua operasi memakai API resmi `WC_Product`/`WC_Product_Variation` dan nonce check, jadi hasilnya langsung tampil & bisa dibeli di toko (termasuk selector ukuran di halaman produk).

---

## 💳 Pembayaran QRIS

Checkout wizard (INFORMATION → SHIPPING → PAYMENT) memindahkan elemen `#payment` WooCommerce asli ke step **PAYMENT**, jadi metode dari plugin gateway mana pun otomatis muncul di sana tanpa perubahan tema.

Tersedia dua jalur QRIS:

### A. Plugin gateway dinamis (rekomendasi untuk produksi)

Verifikasi pembayaran **otomatis** oleh provider. Install salah satu plugin:
- **Midtrans WooCommerce** (metode *QRIS* tersedia)
- **Xendit WooCommerce**
- **Duitku** atau **Tripay**

Setup: **WooCommerce → Settings → Payments** → aktifkan gateway → isi **Merchant ID / Server Key / API Key** (pakai *Sandbox* untuk testing) → aktifkan metode QRIS.

### B. Plugin pendamping `xiv-qris-gateway` (QRIS statis, tanpa provider)

Sudah disertakan di repo: [`xiv-qris-gateway/`](./xiv-qris-gateway) — gateway WooCommerce custom dengan **QRIS statis** (kode QR di-scan, verifikasi pembayaran **manual** oleh admin).

```
xiv-qris-gateway/
├── xiv-qris-gateway.php                 # Bootstrap + pendaftaran gateway
├── includes/class-xiv-qris-gateway.php  # Gateway: setting, upload QR, thankyou
└── assets/js/admin.js                   # Media library picker untuk gambar QR
```

Setup:

1. Salin folder ke plugin WordPress:
   ```bash
   cp -r xiv-qris-gateway wp-content/plugins/
   ```
2. **Plugins → Installed Plugins** → aktifkan **XIV QRIS Payment Gateway**.
3. **WooCommerce → Settings → Payments** → aktifkan **QRIS**.
4. Isi setting:
   - **Merchant Name** — nama yang tampil saat QR di-scan
   - **QRIS Merchant ID (PAN)** — PAN QRIS statis dari bank/penyedia
   - **QR Code Image** — unggah gambar QR statis via media library
   - **Payment Instruction** — langkah bayar (bisa pakai `{merchant_name}`, `{order_total}`, `{order_id}`)

Setelah aktif: metode QRIS muncul di step **PAYMENT** (dengan gambar QR), order dibuat **on-hold**, dan kartu QRIS (gambar + instruksi + total) tampil di halaman terima kasih. Admin menandai order **Completed** setelah pembayaran masuk.

> Catatan: QRIS statis **tidak** ter-verifikasi otomatis. Untuk auto-confirm gunakan jalur A (Midtrans/Xendit/Duitku/Tripay).
