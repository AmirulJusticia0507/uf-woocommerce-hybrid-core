<?php
/**
 * Admin extras: newsletter subscribers + size guide CRUD.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	return;
}

add_action( 'admin_menu', 'xiv_admin_extras_register_menu', 11 );
add_action( 'admin_init', 'xiv_size_guides_admin_save' );
add_action( 'admin_post_xiv_size_guide_delete', 'xiv_size_guides_admin_delete' );
add_action( 'admin_post_xiv_newsletter_delete', 'xiv_newsletter_admin_delete' );
add_action( 'admin_post_xiv_newsletter_export', 'xiv_newsletter_admin_export' );

/**
 * Submenu pages under the XIV menu.
 */
function xiv_admin_extras_register_menu() {
	$cap = defined( 'XIV_ADMIN_CAP' ) ? XIV_ADMIN_CAP : 'manage_woocommerce_products';

	add_submenu_page( 'xiv-dashboard', 'Newsletter', 'Newsletter', $cap, 'xiv-newsletter', 'xiv_newsletter_admin_page' );
	add_submenu_page( 'xiv-dashboard', 'Size Guides', 'Size Guides', $cap, 'xiv-size-guides', 'xiv_size_guides_admin_page' );
}

// ------------------------------------------------------------------ Newsletter

/**
 * Render subscriber list.
 */
function xiv_newsletter_admin_page() {
	$subs = (array) get_option( 'xiv_newsletter_subscribers', array() );
	?>
	<div class="wrap xiv-pb-8">
		<h1 class="xiv-mb-1 xiv-text-[22px] xiv-font-black xiv-uppercase xiv-tracking-tight"><?php esc_html_e( 'Newsletter Subscribers', 'xiv-apparel' ); ?></h1>
		<p class="xiv-mt-0 xiv-text-[13px] xiv-text-xiv-gray-text"><?php esc_html_e( 'Daftar email yang berlangganan via form footer / popup.', 'xiv-apparel' ); ?></p>

		<?php if ( isset( $_GET['deleted'] ) ) : ?>
			<div class="xiv-bg-green-50 xiv-border xiv-border-green-200 xiv-text-green-800 xiv-rounded xiv-px-4 xiv-py-3 xiv-my-4 xiv-text-sm"><?php esc_html_e( 'Dihapus.', 'xiv-apparel' ); ?></div>
		<?php endif; ?>

		<div class="xiv-mt-4 xiv-flex xiv-items-center xiv-gap-3">
			<span class="xiv-text-sm xiv-text-xiv-gray-text"><?php echo esc_html( count( $subs ) . ' subscriber' . ( count( $subs ) === 1 ? '' : 's' ) ); ?></span>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=xiv_newsletter_export' ), 'xiv_newsletter_export' ) ); ?>" class="button"><?php esc_html_e( 'Export CSV', 'xiv-apparel' ); ?></a>
		</div>

		<table class="widefat striped xiv-mt-4">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Email', 'xiv-apparel' ); ?></th>
				<th><?php esc_html_e( 'Tanggal', 'xiv-apparel' ); ?></th>
				<th><?php esc_html_e( 'Aksi', 'xiv-apparel' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( empty( $subs ) ) : ?>
				<tr><td colspan="3"><?php esc_html_e( 'Belum ada subscriber.', 'xiv-apparel' ); ?></td></tr>
			<?php else : foreach ( array_reverse( $subs ) as $i => $s ) : ?>
				<tr>
					<td><?php echo esc_html( $s['email'] ?? '' ); ?></td>
					<td><?php echo esc_html( mysql2date( 'd M Y H:i', $s['time'] ?? '' ) ); ?></td>
					<td>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=xiv_newsletter_delete&i=' . $i ), 'xiv_newsletter_delete' ) ); ?>" class="xiv-text-red-500" onclick="return confirm('Hapus?');"><?php esc_html_e( 'Hapus', 'xiv-apparel' ); ?></a>
					</td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Delete a subscriber by option index.
 */
function xiv_newsletter_admin_delete() {
	if ( ! current_user_can( 'manage_woocommerce_products' ) ) {
		wp_die( 'Nope.' );
	}
	check_admin_referer( 'xiv_newsletter_delete' );

	$subs = (array) get_option( 'xiv_newsletter_subscribers', array() );
	$i    = absint( $_GET['i'] ?? 0 );
	if ( isset( $subs[ $i ] ) ) {
		unset( $subs[ $i ] );
		update_option( 'xiv_newsletter_subscribers', array_values( $subs ) );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=xiv-newsletter&deleted=1' ) );
	exit;
}

/**
 * Export subscribers as CSV.
 */
function xiv_newsletter_admin_export() {
	if ( ! current_user_can( 'manage_woocommerce_products' ) ) {
		wp_die( 'Nope.' );
	}
	check_admin_referer( 'xiv_newsletter_export' );

	$subs = (array) get_option( 'xiv_newsletter_subscribers', array() );

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="xiv-newsletter-' . gmdate( 'Y-m-d' ) . '.csv"' );
	echo "Email,Waktu\n";
	foreach ( $subs as $s ) {
		echo esc_html( ( $s['email'] ?? '' ) . ',' . ( $s['time'] ?? '' ) ) . "\n";
	}
	exit;
}

// ---------------------------------------------------------------- Size guides

/**
 * Render size guide CRUD.
 */
function xiv_size_guides_admin_page() {
	global $wpdb;
	$table   = $wpdb->prefix . 'xiv_size_guides';
	$rows    = (array) $wpdb->get_results( "SELECT * FROM {$table} ORDER BY product_category ASC, id ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$cats    = array();
	foreach ( $rows as $r ) {
		$cats[ $r['product_category'] ] = 1;
	}
	$cats    = array_keys( $cats );
	$edit    = null;
	$editing = 0;
	if ( isset( $_GET['edit'] ) ) {
		foreach ( $rows as $r ) {
			if ( (int) $r['id'] === (int) $_GET['edit'] ) {
				$edit    = $r;
				$editing = (int) $r['id'];
				break;
			}
		}
	}
	$f = $edit ? $edit : array(
		'id'              => 0,
		'product_category' => '',
		'size_label'      => '',
		'chest_cm'        => '',
		'shoulder_cm'     => '',
		'waist_cm'        => '',
		'length_cm'       => '',
	);
	?>
	<div class="wrap xiv-pb-8">
		<h1 class="xiv-mb-1 xiv-text-[22px] xiv-font-black xiv-uppercase xiv-tracking-tight"><?php esc_html_e( 'Size Guides', 'xiv-apparel' ); ?></h1>
		<p class="xiv-mt-0 xiv-text-[13px] xiv-text-xiv-gray-text"><?php esc_html_e( 'Kelola tabel ukuran untuk modal "Find Your Size" di PDP.', 'xiv-apparel' ); ?></p>

		<?php if ( isset( $_GET['saved'] ) ) : ?>
			<div class="xiv-bg-green-50 xiv-border xiv-border-green-200 xiv-text-green-800 xiv-rounded xiv-px-4 xiv-py-3 xiv-my-4 xiv-text-sm"><?php esc_html_e( 'Tersimpan.', 'xiv-apparel' ); ?></div>
		<?php endif; ?>
		<?php if ( isset( $_GET['deleted'] ) ) : ?>
			<div class="xiv-bg-green-50 xiv-border xiv-border-green-200 xiv-text-green-800 xiv-rounded xiv-px-4 xiv-py-3 xiv-my-4 xiv-text-sm"><?php esc_html_e( 'Dihapus.', 'xiv-apparel' ); ?></div>
		<?php endif; ?>

		<div class="xiv-grid xiv-grid-cols-1 lg:xiv-grid-cols-2 xiv-gap-8 xiv-mt-4">
			<div>
				<h2 class="xiv-text-base xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-3"><?php echo $editing ? esc_html_e( 'Edit Baris', 'xiv-apparel' ) : esc_html_e( 'Tambah Baris', 'xiv-apparel' ); ?></h2>
				<form method="post" action="" class="xiv-bg-white xiv-border xiv-border-xiv-gray-light xiv-rounded xiv-p-5">
					<input type="hidden" name="xiv_size_guides_submit" value="1" />
					<input type="hidden" name="id" value="<?php echo esc_attr( $f['id'] ); ?>" />
					<?php wp_nonce_field( 'xiv_size_guides_save' ); ?>
					<table class="form-table xiv-max-w-xl">
						<tr>
							<th><label for="product_category">Kategori</label></th>
							<td>
								<input id="product_category" name="product_category" type="text" list="xiv-sg-cats" value="<?php echo esc_attr( $f['product_category'] ); ?>" required />
								<datalist id="xiv-sg-cats">
									<?php foreach ( $cats as $c ) : ?>
										<option value="<?php echo esc_attr( $c ); ?>"></option>
									<?php endforeach; ?>
								</datalist>
							</td>
						</tr>
						<tr>
							<th><label for="size_label">Ukuran</label></th>
							<td><input id="size_label" name="size_label" type="text" class="regular-text" value="<?php echo esc_attr( $f['size_label'] ); ?>" required /></td>
						</tr>
						<tr>
							<th><label for="chest_cm">Chest (cm)</label></th>
							<td><input id="chest_cm" name="chest_cm" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( $f['chest_cm'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="shoulder_cm">Shoulder (cm)</label></th>
							<td><input id="shoulder_cm" name="shoulder_cm" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( $f['shoulder_cm'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="waist_cm">Waist (cm)</label></th>
							<td><input id="waist_cm" name="waist_cm" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( $f['waist_cm'] ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="length_cm">Length (cm)</label></th>
							<td><input id="length_cm" name="length_cm" type="number" step="0.01" class="small-text" value="<?php echo esc_attr( $f['length_cm'] ); ?>" /></td>
						</tr>
					</table>
					<p class="submit"><input type="submit" class="button button-primary" value="<?php echo $editing ? esc_attr_e( 'Simpan Perubahan', 'xiv-apparel' ) : esc_attr_e( 'Tambah Baris', 'xiv-apparel' ); ?>" /></p>
				</form>
			</div>

			<div>
				<h2 class="xiv-text-base xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-3"><?php esc_html_e( 'Semua Data', 'xiv-apparel' ); ?></h2>
				<table class="widefat striped">
					<thead>
					<tr>
						<th><?php esc_html_e( 'Kategori', 'xiv-apparel' ); ?></th>
						<th><?php esc_html_e( 'Ukuran', 'xiv-apparel' ); ?></th>
						<th><?php esc_html_e( 'Aksi', 'xiv-apparel' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php if ( empty( $rows ) ) : ?>
						<tr><td colspan="3"><?php esc_html_e( 'Belum ada data.', 'xiv-apparel' ); ?></td></tr>
					<?php else : foreach ( $rows as $r ) : ?>
						<tr>
							<td><?php echo esc_html( $r['product_category'] ); ?></td>
							<td><?php echo esc_html( $r['size_label'] ); ?></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=xiv-size-guides&edit=' . $r['id'] ) ); ?>"><?php esc_html_e( 'Edit', 'xiv-apparel' ); ?></a> |
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=xiv_size_guide_delete&id=' . $r['id'] ), 'xiv_size_guide_delete' ) ); ?>" class="xiv-text-red-500" onclick="return confirm('Hapus baris ini?');"><?php esc_html_e( 'Hapus', 'xiv-apparel' ); ?></a>
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

/**
 * Handle size guide save.
 */
function xiv_size_guides_admin_save() {
	if ( ! isset( $_POST['xiv_size_guides_submit'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_woocommerce_products' ) ) {
		wp_die( 'Nope.' );
	}
	check_admin_referer( 'xiv_size_guides_save' );

	global $wpdb;
	$table = $wpdb->prefix . 'xiv_size_guides';

	$data = array(
		'product_category' => sanitize_text_field( wp_unslash( $_POST['product_category'] ?? '' ) ),
		'size_label'       => sanitize_text_field( wp_unslash( $_POST['size_label'] ?? '' ) ),
		'chest_cm'         => isset( $_POST['chest_cm'] ) && '' !== $_POST['chest_cm'] ? (float) $_POST['chest_cm'] : null,
		'shoulder_cm'      => isset( $_POST['shoulder_cm'] ) && '' !== $_POST['shoulder_cm'] ? (float) $_POST['shoulder_cm'] : null,
		'waist_cm'         => isset( $_POST['waist_cm'] ) && '' !== $_POST['waist_cm'] ? (float) $_POST['waist_cm'] : null,
		'length_cm'        => isset( $_POST['length_cm'] ) && '' !== $_POST['length_cm'] ? (float) $_POST['length_cm'] : null,
	);
	$fmt  = array( '%s', '%s', '%f', '%f', '%f', '%f' );

	$id = absint( $_POST['id'] ?? 0 );
	if ( $id ) {
		$wpdb->update( $table, $data, array( 'id' => $id ), $fmt, array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	} else {
		$wpdb->insert( $table, $data, $fmt ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	wp_safe_redirect( admin_url( 'admin.php?page=xiv-size-guides&saved=1' ) );
	exit;
}

/**
 * Handle size guide delete.
 */
function xiv_size_guides_admin_delete() {
	if ( ! current_user_can( 'manage_woocommerce_products' ) ) {
		wp_die( 'Nope.' );
	}
	check_admin_referer( 'xiv_size_guide_delete' );

	global $wpdb;
	$table = $wpdb->prefix . 'xiv_size_guides';
	$wpdb->delete( $table, array( 'id' => absint( $_GET['id'] ?? 0 ) ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

	wp_safe_redirect( admin_url( 'admin.php?page=xiv-size-guides&deleted=1' ) );
	exit;
}
