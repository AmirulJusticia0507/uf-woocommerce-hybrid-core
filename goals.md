# XIV Apparel - Project Goals, Core Web Vitals & Roadmap

This document outlines the performance SLA, mobile UX goals, and frontend optimization targets for the **XIV Apparel** custom e-commerce theme.

---

## 🎯 Target Performance SLA

| Metric                                    | Benchmark Target  | Optimization Technique                                 |
| :---------------------------------------- | :---------------- | :----------------------------------------------------- |
| **Mobile Google PageSpeed**         | **90+**     | Image WebP conversion, critical CSS inline             |
| **Largest Contentful Paint (LCP)**  | **< 1.8s**  | Preloading hero product banner images                  |
| **Interaction to Next Paint (INP)** | **< 150ms** | Native JS filter state with optimistic UI update       |
| **Cumulative Layout Shift (CLS)**   | **< 0.02**  | Fixed aspect-ratio image containers (`aspect-[3/4]`) |

---

## 🛣️ Development Milestones

1. **Phase 1: Canvas & Visual Identity Setup**
   - Texture canvas overlay (`#f4f4f2`), typography tokens (Syne / Space Grotesk + Inter), and diamond logo SVG navbar.
2. **Phase 2: Product Catalog & Filtering (PLP)**
   - Sidebar filter drawer with Size options (XS to 2X), Availability stock indicators, and instant AJAX grid updates.
3. **Phase 3: High-Conversion Product Page (PDP)**
   - Multi-angle gallery layout, color swatch selectors, size picker modal, and sliding shopping bag drawer.
4. **Phase 4: Checkout Optimization & Launch**
   - Clean 3-step checkout form (Information -> Shipping -> Payment) and full mobile device QA.
