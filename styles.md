

# XIV Apparel - Design System & Tailwind CSS Setup

This document specifies the design tokens, color palette, typography hierarchy, and CSS classes corresponding to the **XIV Apparel** mockups.

---

## 🎨 Color Palette & Design Tokens

```javascript
// tailwind.config.js
module.exports = {
  prefix: 'xiv-',
  content: [
    './**/*.php',
    './assets/src/**/*.js'
  ],
  theme: {
    extend: {
      colors: {
        'xiv-bg': '#f4f4f2',        // Textured grain background
        'xiv-black': '#0a0a0a',     // Primary text & dark buttons
        'xiv-gray-light': '#e5e5e0',// Filter pill border & input fields
        'xiv-gray-text': '#767676', // Subtitles & tax notes
        'xiv-blue-accent': '#2541b2'// Stock counter accent (450)
      },
      fontFamily: {
        'display': ['"Syne"', '"Space Grotesk"', 'sans-serif'],
        'sans': ['"Inter"', 'sans-serif'],
        'mono': ['"JetBrains Mono"', 'monospace']
      }
    }
  }
}
```

---

## 🧱 Key UI Components (Tailwind Markup)

### 1. Filter Category Pills (Catalog PLP)

```html
<div class="xiv-flex xiv-flex-wrap xiv-gap-2 xiv-my-4">
    <button class="xiv-px-4 xiv-py-1.5 xiv-text-xs xiv-font-bold xiv-uppercase xiv-border xiv-border-xiv-black xiv-bg-xiv-black xiv-text-white">NEW</button>
    <button class="xiv-px-4 xiv-py-1.5 xiv-text-xs xiv-font-bold xiv-uppercase xiv-border xiv-border-xiv-gray-light xiv-bg-transparent hover:xiv-border-xiv-black">SHIRTS</button>
    <button class="xiv-px-4 xiv-py-1.5 xiv-text-xs xiv-font-bold xiv-uppercase xiv-border xiv-border-xiv-gray-light xiv-bg-transparent hover:xiv-border-xiv-black">POLO SHIRTS</button>
    <button class="xiv-px-4 xiv-py-1.5 xiv-text-xs xiv-font-bold xiv-uppercase xiv-border xiv-border-xiv-gray-light xiv-bg-transparent hover:xiv-border-xiv-black">T-SHIRTS</button>
    <button class="xiv-px-4 xiv-py-1.5 xiv-text-xs xiv-font-bold xiv-uppercase xiv-border xiv-border-xiv-gray-light xiv-bg-transparent hover:xiv-border-xiv-black">JEANS</button>
</div>
```

### 2. Size Selector Box (PDP & Filter)

```html
<div class="xiv-flex xiv-gap-1">
    <button class="xiv-w-9 xiv-h-9 xiv-flex xiv-items-center xiv-justify-center xiv-text-xs xiv-font-bold xiv-border xiv-border-xiv-gray-light hover:xiv-border-xiv-black">XS</button>
    <button class="xiv-w-9 xiv-h-9 xiv-flex xiv-items-center xiv-justify-center xiv-text-xs xiv-font-bold xiv-border xiv-border-xiv-gray-light hover:xiv-border-xiv-black">S</button>
    <button class="xiv-w-9 xiv-h-9 xiv-flex xiv-items-center xiv-justify-center xiv-text-xs xiv-font-bold xiv-border xiv-border-xiv-gray-light hover:xiv-border-xiv-black">M</button>
    <button class="xiv-w-9 xiv-h-9 xiv-flex xiv-items-center xiv-justify-center xiv-text-xs xiv-font-bold xiv-border xiv-border-xiv-black xiv-bg-xiv-black xiv-text-white">L</button>
    <button class="xiv-w-9 xiv-h-9 xiv-flex xiv-items-center xiv-justify-center xiv-text-xs xiv-font-bold xiv-border xiv-border-xiv-gray-light hover:xiv-border-xiv-black">XL</button>
    <button class="xiv-w-9 xiv-h-9 xiv-flex xiv-items-center xiv-justify-center xiv-text-xs xiv-font-bold xiv-border xiv-border-xiv-gray-light hover:xiv-border-xiv-black">2X</button>
</div>
```

### 3. Product Card Component

```html
<div class="xiv-group xiv-flex xiv-flex-col">
    <div class="xiv-relative xiv-aspect-[3/4] xiv-bg-stone-200 xiv-overflow-hidden">
        <img src="product.jpg" alt="Abstract Print Shirt" class="xiv-w-full xiv-h-full xiv-object-cover group-hover:xiv-scale-105 xiv-transition xiv-duration-300">
    </div>
    <div class="xiv-mt-3 xiv-flex xiv-justify-between xiv-items-start">
        <div>
            <span class="xiv-text-xs xiv-text-xiv-gray-text">Cotton T Shirt</span>
            <h3 class="xiv-text-sm xiv-font-bold xiv-text-xiv-black xiv-m-0">Full Sleeve Zipper</h3>
        </div>
        <span class="xiv-text-sm xiv-font-bold xiv-text-xiv-black">$ 199</span>
    </div>
</div>
```
