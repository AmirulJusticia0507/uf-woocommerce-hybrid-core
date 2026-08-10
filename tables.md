

# XIV Apparel - Database Schemas & PostgreSQL Compatibility

This document defines custom database tables for apparel sizing guides, cart session state, and order meta, structured for MySQL 8.0+ and PostgreSQL 15+ compatibility.

---

## 1. Custom Table: Size Recommendation & Measurements (`wp_xiv_size_guides`)

Stores detailed garment measurements for the "FIND YOUR SIZE | MEASUREMENT GUIDE" interactive modal.

```sql
CREATE TABLE IF NOT EXISTS wp_xiv_size_guides (
    id BIGINT NOT NULL AUTO_INCREMENT,
    product_category VARCHAR(100) NOT NULL, -- e.g. 'T-Shirts', 'Jeans', 'Shirts'
    size_label VARCHAR(10) NOT NULL,        -- 'XS', 'S', 'M', 'L', 'XL', '2X'
    chest_cm DECIMAL(5,2) NULL,
    shoulder_cm DECIMAL(5,2) NULL,
    waist_cm DECIMAL(5,2) NULL,
    length_cm DECIMAL(5,2) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_xiv_category (product_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 2. PostgreSQL / MySQL Abstraction Helper

```php
/**
 * Fetch size matrix for garment PDP modal.
 */
function xiv_get_category_size_guide(string $category): array {
    global $wpdb;
  
    $table = $wpdb->prefix . 'xiv_size_guides';
  
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT size_label, chest_cm, shoulder_cm, waist_cm, length_cm 
             FROM {$table} 
             WHERE product_category = %s 
             ORDER BY id ASC",
            $category
        ),
        ARRAY_A
    ) ?: [];
}
```
