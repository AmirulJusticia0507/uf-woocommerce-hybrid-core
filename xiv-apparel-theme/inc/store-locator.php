<?php
/**
 * Store locator: custom table, admin CRUD, shortcode.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

define( 'XIV_STORES_DB_VERSION', '1.0.0' );

/**
 * Create/upgrade wp_xiv_store_locations table.
 */
function xiv_stores_install() {
	global $wpdb;

	$table           = $wpdb->prefix . 'xiv_store_locations';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		id BIGINT NOT NULL AUTO_INCREMENT,
		name VARCHAR(120) NOT NULL,
		address TEXT NULL,
		city VARCHAR(80) NOT NULL,
		phone VARCHAR(30) NULL,
		hours VARCHAR(120) NULL,
		lat DECIMAL(10,6) NULL,
		lng DECIMAL(10,6) NULL,
		position INT NOT NULL DEFAULT 0,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY idx_xiv_store_city (city)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'xiv_stores_db_version', XIV_STORES_DB_VERSION );
}
add_action( 'after_switch_theme', 'xiv_stores_install' );

/**
 * Seed default demo stores.
 */
function xiv_stores_seed() {
	if ( get_option( 'xiv_stores_seeded' ) ) {
		return;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'xiv_store_locations';

	$rows = array(
		array( 'XIV SCBD', 'SCBD Lot 15, Jl. Jend. Sudirman Kav 52-53, Senayan, Kebayoran Baru', 'Jakarta', '+62 21 1234 0001', 'Mon–Sun 10:00–22:00', -6.227746, 106.803627, 1 ),
		array( 'XIV PIK 2', 'Jl. Pantai Indah Kapuk 2, Rukan Crown, Jakarta Utara', 'Jakarta', '+62 21 1234 0002', 'Mon–Sun 11:00–22:00', -6.116168, 106.744618, 2 ),
		array( 'XIV Bandung', 'Jl. Braga No. 99, Braga, Sumur Bandung', 'Bandung', '+62 22 1234 0003', 'Mon–Sun 10:00–21:00', -6.916830, 107.609238, 3 ),
		array( 'XIV Surabaya', 'Jl. Pemuda No. 120, Genteng', 'Surabaya', '+62 31 1234 0004', 'Mon–Sun 10:00–21:00', -7.245800, 112.739800, 4 ),
		array( 'XIV Yogyakarta', 'Jl. Malioboro No. 45, Ngupasan, Gondomanan', 'Yogyakarta', '+62 274 1234 0005', 'Mon–Sun 10:00–21:00', -7.795530, 110.369480, 5 ),
		array( 'XIV Bali', 'Jl. Raya Seminyak No. 28, Kuta', 'Bali', '+62 361 1234 0006', 'Mon–Sun 10:00–22:00', -8.684660, 115.159200, 6 ),
	);

	foreach ( $rows as $row ) {
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$table,
			array(
				'name'     => $row[0],
				'address'  => $row[1],
				'city'     => $row[2],
				'phone'    => $row[3],
				'hours'    => $row[4],
				'lat'      => $row[5],
				'lng'      => $row[6],
				'position' => $row[7],
			),
			array( '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%d' )
		);
	}

	update_option( 'xiv_stores_seeded', 1 );
}
add_action( 'after_switch_theme', 'xiv_stores_seed', 20 );

/**
 * Fetch stores, optionally filtered by city.
 *
 * @param string $city Optional city slug.
 * @return array
 */
function xiv_get_stores( string $city = '' ): array {
	global $wpdb;

	$table = $wpdb->prefix . 'xiv_store_locations';
	$sql   = "SELECT * FROM {$table}";

	if ( $city ) {
		$sql .= $wpdb->prepare( ' WHERE city = %s', $city );
	}
	$sql .= ' ORDER BY position ASC, id ASC';

	return (array) $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
}

/**
 * Available cities for the filter.
 */
function xiv_get_store_cities(): array {
	global $wpdb;

	$table = $wpdb->prefix . 'xiv_store_locations';

	return (array) $wpdb->get_col( "SELECT DISTINCT city FROM {$table} ORDER BY city ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
}

/**
 * Store locator shortcode.
 */
function xiv_store_locator_shortcode() {
	$stores = xiv_get_stores();
	if ( empty( $stores ) ) {
		return '<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-uppercase">' . esc_html( xiv_t( 'NO STORES YET' ) ) . '</p>';
	}

	$cities = xiv_get_store_cities();

	$out  = '<div id="xiv-store-locator" class="xiv-store-locator">';
	$out .= '<div class="xiv-flex xiv-flex-wrap xiv-items-center xiv-justify-between xiv-gap-4 xiv-mb-8">';
	$out .= '<h2 class="xiv-font-display xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-text-2xl md:xiv-text-3xl">' . esc_html( xiv_t( 'FIND OUR STORES' ) ) . '</h2>';
	$out .= '<label class="xiv-flex xiv-items-center xiv-gap-3 xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest">';
	$out .= '<span>' . esc_html( xiv_t( 'CITY' ) ) . '</span>';
	$out .= '<select id="xiv-store-city-filter" class="xiv-bg-transparent xiv-border xiv-border-xiv-gray-light xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-py-2 xiv-px-3 focus:xiv-outline-none">';
	$out .= '<option value="">' . esc_html( xiv_t( 'ALL' ) ) . '</option>';
	foreach ( $cities as $city ) {
		$out .= '<option value="' . esc_attr( $city ) . '">' . esc_html( $city ) . '</option>';
	}
	$out .= '</select>';
	$out .= '</label>';
	$out .= '</div>';

	$out .= '<div class="xiv-grid sm:xiv-grid-cols-2 lg:xiv-grid-cols-3 xiv-gap-px xiv-bg-xiv-gray-light xiv-border xiv-border-xiv-gray-light">';
	foreach ( $stores as $store ) {
		$dir_url = 'https://www.google.com/maps/dir/?api=1&destination=' . $store['lat'] . ',' . $store['lng'];
		$out    .= '<article class="xiv-store-card xiv-bg-xiv-bg xiv-p-6 xiv-flex xiv-flex-col" data-city="' . esc_attr( $store['city'] ) . '">';
		$out    .= '<p class="xiv-text-xs xiv-font-mono xiv-text-xiv-gray-text xiv-uppercase xiv-tracking-widest">' . esc_html( $store['city'] ) . '</p>';
		$out    .= '<h3 class="xiv-font-display xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-text-xl xiv-mt-1 xiv-mb-3">' . esc_html( $store['name'] ) . '</h3>';
		$out    .= '<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-flex-1">' . esc_html( $store['address'] ) . '</p>';
		$out    .= '<dl class="xiv-text-sm xiv-mt-4 xiv-space-y-1">';
		$out    .= '<div class="xiv-flex xiv-gap-2"><dt class="xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xs">' . esc_html( xiv_t( 'PHONE' ) ) . '</dt><dd>' . esc_html( $store['phone'] ) . '</dd></div>';
		$out    .= '<div class="xiv-flex xiv-gap-2"><dt class="xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xs">' . esc_html( xiv_t( 'HOURS' ) ) . '</dt><dd>' . esc_html( $store['hours'] ) . '</dd></div>';
		$out    .= '</dl>';
		$out    .= '<a href="' . esc_url( $dir_url ) . '" target="_blank" rel="noopener" class="xiv-inline-flex xiv-mt-5 xiv-items-center xiv-justify-between xiv-gap-4 xiv-border xiv-border-xiv-black xiv-px-4 xiv-py-3 xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-transition hover:xiv-bg-xiv-black hover:xiv-text-white">';
		$out    .= '<span>' . esc_html( xiv_t( 'GET DIRECTIONS' ) ) . '</span><span aria-hidden="true">&rarr;</span></a>';
		$out    .= '</article>';
	}
	$out .= '</div>';
	$out .= '</div>';

	return $out;
}
add_shortcode( 'xiv_store_locator', 'xiv_store_locator_shortcode' );

/**
 * Front-end city filter (client-side).
 */
function xiv_store_locator_script() {
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var select = document.getElementById('xiv-store-city-filter');
		if (!select) return;
		select.addEventListener('change', function () {
			var city = this.value;
			document.querySelectorAll('.xiv-store-card').forEach(function (card) {
				card.style.display = (!city || card.dataset.city === city) ? '' : 'none';
			});
		});
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'xiv_store_locator_script', 99 );

/**
 * Admin CRUD for store locations.
 */
if ( is_admin() ) {

	add_action( 'admin_menu', 'xiv_stores_register_menu', 11 );
	add_action( 'admin_init', 'xiv_stores_save' );
	add_action( 'admin_post_xiv_delete_store', 'xiv_stores_delete' );

	/**
	 * Submenu page under the XIV menu.
	 */
	function xiv_stores_register_menu() {
		$cap = defined( 'XIV_ADMIN_CAP' ) ? XIV_ADMIN_CAP : 'manage_woocommerce_products';

		add_submenu_page( 'xiv-dashboard', 'Store Locations', 'Store Locations', $cap, 'xiv-stores', 'xiv_stores_page' );
	}

	/**
	 * Handle save (create/update).
	 */
	function xiv_stores_save() {
		if ( ! isset( $_POST['xiv_stores_submit'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce_products' ) ) {
			wp_die( 'Nope.' );
		}
		check_admin_referer( 'xiv_stores_save' );

		global $wpdb;
		$table = $wpdb->prefix . 'xiv_store_locations';

		$data = array(
			'name'     => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'address'  => sanitize_textarea_field( wp_unslash( $_POST['address'] ?? '' ) ),
			'city'     => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
			'phone'    => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'hours'    => sanitize_text_field( wp_unslash( $_POST['hours'] ?? '' ) ),
			'lat'      => isset( $_POST['lat'] ) && '' !== $_POST['lat'] ? (float) $_POST['lat'] : null,
			'lng'      => isset( $_POST['lng'] ) && '' !== $_POST['lng'] ? (float) $_POST['lng'] : null,
			'position' => absint( $_POST['position'] ?? 0 ),
		);
		$fmt = array( '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%d' );

		$id = absint( $_POST['id'] ?? 0 );
		if ( $id ) {
			$wpdb->update( $table, $data, array( 'id' => $id ), $fmt, array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		} else {
			$wpdb->insert( $table, $data, $fmt ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		}

		wp_safe_redirect( admin_url( 'admin.php?page=xiv-stores&saved=1' ) );
		exit;
	}

	/**
	 * Handle delete.
	 */
	function xiv_stores_delete() {
		if ( ! current_user_can( 'manage_woocommerce_products' ) ) {
			wp_die( 'Nope.' );
		}
		check_admin_referer( 'xiv_stores_delete' );

		global $wpdb;
		$table = $wpdb->prefix . 'xiv_store_locations';
		$wpdb->delete( $table, array( 'id' => absint( $_GET['id'] ?? 0 ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		wp_safe_redirect( admin_url( 'admin.php?page=xiv-stores&deleted=1' ) );
		exit;
	}

	/**
	 * Render list + form.
	 */
	function xiv_stores_page() {
		$stores = xiv_get_stores();
		$edit   = null;
		if ( isset( $_GET['edit'] ) ) {
			foreach ( $stores as $s ) {
				if ( (int) $s['id'] === (int) $_GET['edit'] ) {
					$edit = $s;
					break;
				}
			}
		}
		$fields = $edit ? $edit : array( 'id' => 0, 'name' => '', 'address' => '', 'city' => '', 'phone' => '', 'hours' => '', 'lat' => '', 'lng' => '', 'position' => '' );
		?>
		<div class="wrap xiv-pb-8">
			<h1 class="xiv-mb-1 xiv-text-[22px] xiv-font-black xiv-uppercase xiv-tracking-tight"><?php esc_html_e( 'Store Locations', 'xiv-apparel' ); ?></h1>
			<p class="xiv-mt-0 xiv-text-[13px] xiv-text-xiv-gray-text"><?php esc_html_e( 'Kelola lokasi toko untuk shortcode [xiv_store_locator].', 'xiv-apparel' ); ?></p>

			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="xiv-bg-green-50 xiv-border xiv-border-green-200 xiv-text-green-800 xiv-rounded xiv-px-4 xiv-py-3 xiv-my-4 xiv-text-sm"><?php esc_html_e( 'Tersimpan.', 'xiv-apparel' ); ?></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['deleted'] ) ) : ?>
				<div class="xiv-bg-green-50 xiv-border xiv-border-green-200 xiv-text-green-800 xiv-rounded xiv-px-4 xiv-py-3 xiv-my-4 xiv-text-sm"><?php esc_html_e( 'Dihapus.', 'xiv-apparel' ); ?></div>
			<?php endif; ?>

			<div class="xiv-grid xiv-grid-cols-1 lg:xiv-grid-cols-2 xiv-gap-8 xiv-mt-4">
				<div>
					<h2 class="xiv-text-base xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-3"><?php echo $edit ? esc_html_e( 'Edit Store', 'xiv-apparel' ) : esc_html_e( 'Tambah Store', 'xiv-apparel' ); ?></h2>
					<form method="post" action="" class="xiv-bg-white xiv-border xiv-border-xiv-gray-light xiv-rounded xiv-p-5">
						<input type="hidden" name="xiv_stores_submit" value="1" />
						<input type="hidden" name="id" value="<?php echo esc_attr( $fields['id'] ); ?>" />
						<?php wp_nonce_field( 'xiv_stores_save' ); ?>
						<table class="form-table xiv-max-w-xl">
							<tr>
								<th><label for="name">Nama</label></th>
								<td><input id="name" name="name" type="text" class="regular-text" value="<?php echo esc_attr( $fields['name'] ); ?>" required /></td>
							</tr>
							<tr>
								<th><label for="address">Alamat</label></th>
								<td><textarea id="address" name="address" class="regular-text" rows="3"><?php echo esc_textarea( $fields['address'] ); ?></textarea></td>
							</tr>
							<tr>
								<th><label for="city">Kota</label></th>
								<td><input id="city" name="city" type="text" class="regular-text" value="<?php echo esc_attr( $fields['city'] ); ?>" required /></td>
							</tr>
							<tr>
								<th><label for="phone">Telepon</label></th>
								<td><input id="phone" name="phone" type="text" class="regular-text" value="<?php echo esc_attr( $fields['phone'] ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="hours">Jam buka</label></th>
								<td><input id="hours" name="hours" type="text" class="regular-text" value="<?php echo esc_attr( $fields['hours'] ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="lat">Latitude</label></th>
								<td><input id="lat" name="lat" type="text" class="regular-text" value="<?php echo esc_attr( $fields['lat'] ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="lng">Longitude</label></th>
								<td><input id="lng" name="lng" type="text" class="regular-text" value="<?php echo esc_attr( $fields['lng'] ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="position">Urutan</label></th>
								<td><input id="position" name="position" type="number" class="small-text" value="<?php echo esc_attr( $fields['position'] ); ?>" /></td>
							</tr>
						</table>
						<p class="submit"><input type="submit" class="button button-primary" value="<?php echo $edit ? esc_attr_e( 'Simpan Perubahan', 'xiv-apparel' ) : esc_attr_e( 'Tambah Store', 'xiv-apparel' ); ?>" /></p>
					</form>
				</div>

				<div>
					<h2 class="xiv-text-base xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-3"><?php esc_html_e( 'Semua Store', 'xiv-apparel' ); ?></h2>
					<table class="widefat striped">
						<thead>
						<tr>
							<th><?php esc_html_e( 'Nama', 'xiv-apparel' ); ?></th>
							<th><?php esc_html_e( 'Kota', 'xiv-apparel' ); ?></th>
							<th><?php esc_html_e( 'Aksi', 'xiv-apparel' ); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php if ( empty( $stores ) ) : ?>
							<tr><td colspan="3"><?php esc_html_e( 'Belum ada store.', 'xiv-apparel' ); ?></td></tr>
						<?php else : foreach ( $stores as $s ) : ?>
							<tr>
								<td><?php echo esc_html( $s['name'] ); ?></td>
								<td><?php echo esc_html( $s['city'] ); ?></td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=xiv-stores&edit=' . $s['id'] ) ); ?>"><?php esc_html_e( 'Edit', 'xiv-apparel' ); ?></a> |
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=xiv_delete_store&id=' . $s['id'] ), 'xiv_stores_delete' ) ); ?>" class="xiv-text-red-500" onclick="return confirm('Hapus store ini?');"><?php esc_html_e( 'Hapus', 'xiv-apparel' ); ?></a>
								</td>
							</tr>
						<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}
}
