<?php
/**
 * Back-in-stock notifications: table, PDP form, AJAX, admin queue.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

define( 'XIV_BIS_DB_VERSION', '1.0.0' );

/**
 * Create/upgrade wp_xiv_back_in_stock table.
 */
function xiv_bis_install() {
	global $wpdb;

	$table           = $wpdb->prefix . 'xiv_back_in_stock';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		id BIGINT NOT NULL AUTO_INCREMENT,
		product_id BIGINT NOT NULL,
		email VARCHAR(190) NOT NULL,
		notified TINYINT(1) NOT NULL DEFAULT 0,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY idx_xiv_bis_product (product_id),
		KEY idx_xiv_bis_email (email)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'xiv_bis_db_version', XIV_BIS_DB_VERSION );
}
add_action( 'after_switch_theme', 'xiv_bis_install' );

/**
 * Subscribe an email to a product restock alert.
 */
function xiv_bis_subscribe( int $product_id, string $email ) {
	global $wpdb;

	$table = $wpdb->prefix . 'xiv_back_in_stock';
	$email = sanitize_email( $email );

	if ( ! $product_id || ! is_email( $email ) ) {
		return false;
	}

	$existing = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"SELECT id FROM {$table} WHERE product_id = %d AND email = %s LIMIT 1",
			$product_id,
			$email
		)
	);

	if ( $existing ) {
		$wpdb->update( $table, array( 'notified' => 0 ), array( 'id' => $existing ), array( '%d' ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return true;
	}

	return (bool) $wpdb->insert( $table, array( 'product_id' => $product_id, 'email' => $email ), array( '%d', '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
}

/**
 * AJAX: subscribe to restock alert.
 */
function xiv_bis_ajax_subscribe() {
	check_ajax_referer( 'xiv_filter_nonce', 'security' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$email      = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

	if ( ! $product_id || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => xiv_t( 'Invalid email.' ) ) );
	}

	if ( xiv_bis_subscribe( $product_id, $email ) ) {
		wp_send_json_success( array( 'message' => xiv_t( 'WE WILL NOTIFY YOU WHEN BACK IN STOCK' ) ) );
	}

	wp_send_json_error( array( 'message' => xiv_t( 'SOMETHING WENT WRONG' ) ) );
}
add_action( 'wp_ajax_xiv_back_in_stock', 'xiv_bis_ajax_subscribe' );
add_action( 'wp_ajax_nopriv_xiv_back_in_stock', 'xiv_bis_ajax_subscribe' );

/**
 * Back-in-stock form rendered on out-of-stock products.
 */
function xiv_back_in_stock_form() {
	global $product;

	if ( ! $product || $product->is_in_stock() ) {
		return;
	}

	$heading = xiv_t( 'NOTIFY ME WHEN BACK IN STOCK' );
	$subs    = xiv_t( 'NOTIFY ME' );
	?>
	<div class="xiv-back-in-stock xiv-mt-5 xiv-border-t xiv-border-xiv-gray-light xiv-pt-5">
		<p class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest"><?php echo esc_html( $heading ); ?></p>
		<form class="xiv-bis-form xiv-mt-3" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
			<label class="xiv-sr-only" for="xiv-bis-email"><?php echo esc_html( xiv_t( 'Email address' ) ); ?></label>
			<div class="xiv-flex xiv-border-b xiv-border-xiv-black">
				<input id="xiv-bis-email" type="email" required placeholder="<?php echo esc_attr( xiv_t( 'YOUR EMAIL' ) ); ?>"
					   class="xiv-flex-1 xiv-bg-transparent xiv-border-0 xiv-text-sm xiv-uppercase xiv-tracking-widest xiv-font-bold placeholder:xiv-text-xiv-gray-text focus:xiv-outline-none" />
				<button type="submit" class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-black xiv-pl-4"><?php echo esc_html( $subs ); ?></button>
			</div>
			<p class="xiv-bis-msg xiv-hidden xiv-text-xs xiv-uppercase xiv-tracking-widest xiv-mt-2" aria-live="polite"></p>
		</form>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'xiv_back_in_stock_form', 31 );

/**
 * Admin queue for restock requests.
 */
if ( is_admin() ) {

	add_action( 'admin_menu', 'xiv_bis_register_menu', 11 );
	add_action( 'admin_post_xiv_bis_mark_notified', 'xiv_bis_mark_notified' );
	add_action( 'admin_post_xiv_bis_delete', 'xiv_bis_delete' );

	/**
	 * Submenu page under the XIV menu.
	 */
	function xiv_bis_register_menu() {
		$cap = defined( 'XIV_ADMIN_CAP' ) ? XIV_ADMIN_CAP : 'manage_woocommerce_products';

		add_submenu_page( 'xiv-dashboard', 'Back-in-Stock', 'Back-in-Stock', $cap, 'xiv-back-in-stock', 'xiv_bis_page' );
	}

	/**
	 * Fetch queue with product title/stock status.
	 */
	function xiv_bis_queue() {
		global $wpdb;

		$table = $wpdb->prefix . 'xiv_back_in_stock';

		return (array) $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT r.*, p.post_title, p.post_status,
			        COALESCE(pm.meta_value, '') AS stock_status
			 FROM {$table} r
			 LEFT JOIN {$wpdb->posts} p ON p.ID = r.product_id
			 LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = r.product_id AND pm.meta_key = '_stock_status'
			 ORDER BY r.notified ASC, r.created_at DESC"
		);
	}

	/**
	 * Mark a request as notified.
	 */
	function xiv_bis_mark_notified() {
		if ( ! current_user_can( 'manage_woocommerce_products' ) ) {
			wp_die( 'Nope.' );
		}
		check_admin_referer( 'xiv_bis_mark' );

		global $wpdb;
		$table = $wpdb->prefix . 'xiv_back_in_stock';
		$wpdb->update( $table, array( 'notified' => 1 ), array( 'id' => absint( $_GET['id'] ?? 0 ) ), array( '%d' ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		wp_safe_redirect( admin_url( 'admin.php?page=xiv-back-in-stock' ) );
		exit;
	}

	/**
	 * Delete a request.
	 */
	function xiv_bis_delete() {
		if ( ! current_user_can( 'manage_woocommerce_products' ) ) {
			wp_die( 'Nope.' );
		}
		check_admin_referer( 'xiv_bis_delete' );

		global $wpdb;
		$table = $wpdb->prefix . 'xiv_back_in_stock';
		$wpdb->delete( $table, array( 'id' => absint( $_GET['id'] ?? 0 ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

		wp_safe_redirect( admin_url( 'admin.php?page=xiv-back-in-stock' ) );
		exit;
	}

	/**
	 * Render queue page.
	 */
	function xiv_bis_page() {
		$rows = xiv_bis_queue();
		?>
		<div class="wrap xiv-pb-8">
			<h1 class="xiv-mb-1 xiv-text-[22px] xiv-font-black xiv-uppercase xiv-tracking-tight"><?php esc_html_e( 'Back-in-Stock', 'xiv-apparel' ); ?></h1>
			<p class="xiv-mt-0 xiv-text-[13px] xiv-text-xiv-gray-text"><?php esc_html_e( 'Permintaan notifikasi restock dari pelanggan.', 'xiv-apparel' ); ?></p>

			<table class="widefat striped xiv-mt-4">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Email', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Produk', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Status Stok', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Didaftarkan', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Notified', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Aksi', 'xiv-apparel' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php if ( empty( $rows ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'Belum ada permintaan.', 'xiv-apparel' ); ?></td></tr>
				<?php else : foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row->email ); ?></td>
						<td>
							<?php echo esc_html( $row->post_title ? $row->post_title : '(#' . $row->product_id . ')' ); ?>
							<?php if ( 'publish' !== $row->post_status ) : ?><span class="xiv-text-red-500"> (<?php echo esc_html( $row->post_status ); ?>)</span><?php endif; ?>
						</td>
						<td><?php echo esc_html( $row->stock_status ); ?></td>
						<td><?php echo esc_html( mysql2date( 'd M Y H:i', $row->created_at ) ); ?></td>
						<td><?php echo $row->notified ? '✓' : '—'; ?></td>
						<td>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=xiv_bis_mark_notified&id=' . $row->id ), 'xiv_bis_mark' ) ); ?>"><?php esc_html_e( 'Tandai notified', 'xiv-apparel' ); ?></a> |
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=xiv_bis_delete&id=' . $row->id ), 'xiv_bis_delete' ) ); ?>" class="xiv-text-red-500" onclick="return confirm('Hapus?');"><?php esc_html_e( 'Hapus', 'xiv-apparel' ); ?></a>
						</td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
