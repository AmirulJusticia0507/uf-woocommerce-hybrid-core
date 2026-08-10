
# XIV Apparel - Development Guidelines & Architecture

This document dictates code quality, UI layout consistency, and WooCommerce hook overrides for the **XIV Apparel** theme.

---

## 1. Minimalist UI & Layout Rules

1. **Grain Canvas Background:** All main template wrappers must apply the global texture container `.xiv-canvas` with background `#f4f4f2`.
2. **Typography Rules:**
   - Section Titles: High-impact bold sans-serif, uppercase (`NEW COLLECTION`, `PRODUCTS`, `XIV COLLECTIONS 23-24`).
   - Body & Meta: Clean monospaced or light sans-serif (`Cotton T-Shirt`, `$199`, `MRP incl. of all taxes`).
3. **Card Ratio:** Product card images must maintain a strict 3:4 portrait aspect ratio (`aspect-[3/4]`) with subtle border or frameless aesthetic.

---

## 2. WooCommerce Template Override Standards

Custom logic must leverage WooCommerce hooks instead of editing core templates directly:

### A. Custom Product Gallery (PDP)

```php
/**
 * Render XIV Minimalist Gallery Thumbnails on Single Product Page
 */
add_action('woocommerce_before_single_product_summary', function() {
    global $product;
    $attachment_ids = $product->get_gallery_image_ids();
    $main_image_id  = $product->get_image_id();
  
    echo '<div class="xiv-gallery-wrapper uf-flex uf-gap-4">';
    echo '<div class="xiv-main-image uf-w-3/4 aspect-[3/4]">' . wp_get_attachment_image($main_image_id, 'full') . '</div>';
  
    echo '<div class="xiv-thumbnails uf-w-1/4 uf-flex uf-flex-col uf-gap-2">';
    foreach ($attachment_ids as $attachment_id) {
        echo '<div class="xiv-thumb-item aspect-[3/4] uf-cursor-pointer">' . wp_get_attachment_image($attachment_id, 'thumbnail') . '</div>';
    }
    echo '</div>';
    echo '</div>';
}, 20);
```

### B. AJAX Filter Handler for Catalog (PLP)

- Filters for **Size** (XS, S, M, L, XL, 2X), **Availability**, **Category**, and **Price Range** must trigger instantaneous REST/AJAX re-renders without full page reloads.

---

## 3. Responsive Breakpoints

| Breakpoint              | Target Screen | Layout Behavior                                                                   |
| :---------------------- | :------------ | :-------------------------------------------------------------------------------- |
| `sm` (< 640px)        | Mobile        | Single/Two column product grid, full-screen filter modal, sticky Add-to-Cart bar. |
| `md` (640px - 1024px) | Tablet        | Two/Three column grid, collapsible sidebar filter.                                |
| `lg` (> 1024px)       | Desktop       | Three/Four column grid, persistent left-side sticky filter panel.                 |
