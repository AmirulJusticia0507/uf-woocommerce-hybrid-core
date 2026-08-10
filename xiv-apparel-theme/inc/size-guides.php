<?php
/**
 * Custom size-guide database table & queries (MySQL 8 / PostgreSQL ready).
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Schema version for the custom table.
 */
define( 'XIV_DB_VERSION', '1.0.0' );

/**
 * Create/upgrade wp_xiv_size_guides table.
 */
function xiv_size_guides_install() {
	global $wpdb;

	$table           = $wpdb->prefix . 'xiv_size_guides';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		id BIGINT NOT NULL AUTO_INCREMENT,
		product_category VARCHAR(100) NOT NULL,
		size_label VARCHAR(10) NOT NULL,
		chest_cm DECIMAL(5,2) NULL,
		shoulder_cm DECIMAL(5,2) NULL,
		waist_cm DECIMAL(5,2) NULL,
		length_cm DECIMAL(5,2) NULL,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY idx_xiv_category (product_category)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'xiv_size_guides_db_version', XIV_DB_VERSION );
}
add_action( 'after_switch_theme', 'xiv_size_guides_install' );

/**
 * Seed default size guide data for demo categories.
 */
function xiv_size_guides_seed() {
	if ( get_option( 'xiv_size_guides_seeded' ) ) {
		return;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'xiv_size_guides';

	$rows = array(
		// T-Shirts (chest, shoulder, waist, length) in cm.
		array( 'T-Shirts', 'XS', 86, 40, 74, 64 ),
		array( 'T-Shirts', 'S', 91, 42, 79, 66 ),
		array( 'T-Shirts', 'M', 96, 44, 84, 68 ),
		array( 'T-Shirts', 'L', 101, 46, 89, 70 ),
		array( 'T-Shirts', 'XL', 106, 48, 94, 72 ),
		array( 'T-Shirts', '2X', 111, 50, 99, 74 ),
		// Jeans.
		array( 'Jeans', 'XS', 72, 0, 84, 100 ),
		array( 'Jeans', 'S', 76, 0, 88, 102 ),
		array( 'Jeans', 'M', 80, 0, 92, 104 ),
		array( 'Jeans', 'L', 84, 0, 96, 106 ),
		array( 'Jeans', 'XL', 88, 0, 100, 108 ),
		array( 'Jeans', '2X', 92, 0, 104, 110 ),
		// Shirts.
		array( 'Shirts', 'XS', 88, 41, 76, 70 ),
		array( 'Shirts', 'S', 93, 43, 81, 72 ),
		array( 'Shirts', 'M', 98, 45, 86, 74 ),
		array( 'Shirts', 'L', 103, 47, 91, 76 ),
		array( 'Shirts', 'XL', 108, 49, 96, 78 ),
		array( 'Shirts', '2X', 113, 51, 101, 80 ),
		// Polo Shirts.
		array( 'Polo Shirts', 'S', 90, 42, 78, 65 ),
		array( 'Polo Shirts', 'M', 95, 44, 83, 67 ),
		array( 'Polo Shirts', 'L', 100, 46, 88, 69 ),
	);

	foreach ( $rows as $row ) {
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$table,
			array(
				'product_category' => $row[0],
				'size_label'       => $row[1],
				'chest_cm'         => $row[2] ?: null,
				'shoulder_cm'      => $row[3] ?: null,
				'waist_cm'         => $row[4] ?: null,
				'length_cm'        => $row[5] ?: null,
			),
			array( '%s', '%s', '%f', '%f', '%f', '%f' )
		);
	}

	update_option( 'xiv_size_guides_seeded', 1 );
}
add_action( 'after_switch_theme', 'xiv_size_guides_seed', 20 );

/**
 * Fetch size matrix for a product category.
 *
 * @param string $category Product category slug/label.
 * @return array
 */
function xiv_get_category_size_guide( string $category ): array {
	global $wpdb;

	$table = $wpdb->prefix . 'xiv_size_guides';

	return (array) $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"SELECT size_label, chest_cm, shoulder_cm, waist_cm, length_cm
			 FROM {$table}
			 WHERE product_category = %s
			 ORDER BY id ASC",
			$category
		),
		ARRAY_A
	);
}
