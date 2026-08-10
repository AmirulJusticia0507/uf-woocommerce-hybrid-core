<?php
/**
 * Custom admin panel CRUD untuk produk WooCommerce.
 *
 * Menu dashboard "XIV" → Produk / Tambah Produk / Edit Produk.
 * Operasi tulis langsung ke WC_Product (simple & variable/ukuran),
 * sehingga hasilnya langsung tampil di toko.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

define( 'XIV_ADMIN_CAP', 'manage_woocommerce_products' );

add_action( 'admin_menu', 'xiv_admin_register_menu' );
add_action( 'admin_enqueue_scripts', 'xiv_admin_assets' );
add_action( 'admin_init', 'xiv_admin_product_save' );
add_action( 'admin_post_xiv_delete_product', 'xiv_admin_product_delete' );

/**
 * Daftarkan menu admin.
 */
function xiv_admin_register_menu() {
	$cap = XIV_ADMIN_CAP;

	add_menu_page(
		'XIV Apparel',
		'XIV',
		$cap,
		'xiv-dashboard',
		'xiv_admin_dashboard_page',
		'dashicons-hammer',
		57
	);

	add_submenu_page( 'xiv-dashboard', 'Produk', 'Produk', $cap, 'xiv-products', 'xiv_admin_products_list_page' );
	add_submenu_page( 'xiv-dashboard', 'Tambah Produk', 'Tambah Produk', $cap, 'xiv-product-form', 'xiv_admin_product_form_page' );
	add_submenu_page( null, 'Edit Produk', 'Edit Produk', $cap, 'xiv-product-edit', 'xiv_admin_product_form_page' );
}

/**
 * Dashboard ringkas.
 */
function xiv_admin_dashboard_page() {
	$total_products = (int) wp_count_posts( 'product' )->publish;
	$out_of_stock   = 0;

	$stock_args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_stock_status',
		'meta_value'     => 'outofstock',
	);
	$out_of_stock = count( get_posts( $stock_args ) );

	$cats    = wp_count_terms( 'product_cat' );
	$recent  = wc_get_products( array( 'limit' => 5, 'orderby' => 'date', 'order' => 'DESC' ) );
	?>
	<div class="wrap xiv-admin">
		<h1 class="xiv-admin-title"><?php esc_html_e( 'XIV Apparel', 'xiv-apparel' ); ?></h1>
		<p class="xiv-admin-sub"><?php esc_html_e( 'Kelola produk toko secara langsung.', 'xiv-apparel' ); ?></p>

		<div class="xiv-admin-stats">
			<div class="xiv-admin-stat">
				<span class="xiv-admin-stat-value"><?php echo esc_html( number_format_i18n( $total_products ) ); ?></span>
				<span class="xiv-admin-stat-label"><?php esc_html_e( 'Produk Terbit', 'xiv-apparel' ); ?></span>
			</div>
			<div class="xiv-admin-stat">
				<span class="xiv-admin-stat-value"><?php echo esc_html( number_format_i18n( $out_of_stock ) ); ?></span>
				<span class="xiv-admin-stat-label"><?php esc_html_e( 'Habis Stok', 'xiv-apparel' ); ?></span>
			</div>
			<div class="xiv-admin-stat">
				<span class="xiv-admin-stat-value"><?php echo esc_html( number_format_i18n( $cats ) ); ?></span>
				<span class="xiv-admin-stat-label"><?php esc_html_e( 'Kategori', 'xiv-apparel' ); ?></span>
			</div>
		</div>

		<a class="button button-primary button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=xiv-product-form' ) ); ?>">
			<?php esc_html_e( '+ Tambah Produk', 'xiv-apparel' ); ?>
		</a>
		<a class="button button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=xiv-products' ) ); ?>">
			<?php esc_html_e( 'Lihat Semua Produk', 'xiv-apparel' ); ?>
		</a>

		<h2 class="xiv-admin-section"><?php esc_html_e( 'Produk Terbaru', 'xiv-apparel' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Gambar', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Nama', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Harga', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Stok', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Aksi', 'xiv-apparel' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $recent ) ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'Belum ada produk.', 'xiv-apparel' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $recent as $p ) : ?>
					<tr>
						<td><?php echo wp_kses_post( $p->get_image( 'thumbnail' ) ); ?></td>
						<td><strong><?php echo esc_html( $p->get_name() ); ?></strong></td>
						<td><?php echo wp_kses_post( $p->get_price_html() ); ?></td>
						<td><?php echo $p->is_in_stock() ? esc_html__( 'Tersedia', 'xiv-apparel' ) : esc_html__( 'Habis', 'xiv-apparel' ); ?></td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=xiv-product-edit&product_id=' . $p->get_id() ) ); ?>"><?php esc_html_e( 'Edit', 'xiv-apparel' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Halaman daftar produk.
 */
function xiv_admin_products_list_page() {
	$search  = sanitize_text_field( $_GET['s'] ?? '' );
	$cat_id  = absint( $_GET['cat'] ?? 0 );
	$paged   = max( 1, absint( $_GET['paged'] ?? 1 ) );
	$per     = 20;

	$args = array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'pending' ),
		'posts_per_page' => $per,
		'paged'          => $paged,
	);

	if ( $search ) {
		$args['s'] = $search;
	}
	if ( $cat_id ) {
		$args['tax_query'] = array( array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $cat_id,
		) );
	}

	$query  = new WP_Query( $args );
	$cats   = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
	?>
	<div class="wrap xiv-admin">
		<h1 class="xiv-admin-title"><?php esc_html_e( 'Produk', 'xiv-apparel' ); ?></h1>
		<p class="xiv-admin-sub"><?php esc_html_e( 'Kelola produk WooCommerce dari sini.', 'xiv-apparel' ); ?></p>

		<form method="get" class="xiv-admin-filters">
			<input type="hidden" name="page" value="xiv-products" />
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Cari produk…', 'xiv-apparel' ); ?>" />
			<select name="cat">
				<option value="0"><?php esc_html_e( 'Semua kategori', 'xiv-apparel' ); ?></option>
				<?php foreach ( $cats as $cat ) : ?>
					<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( $cat_id, $cat->term_id ); ?>><?php echo esc_html( $cat->name ); ?></option>
				<?php endforeach; ?>
			</select>
			<button class="button"><?php esc_html_e( 'Filter', 'xiv-apparel' ); ?></button>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=xiv-product-form' ) ); ?>"><?php esc_html_e( '+ Tambah Produk', 'xiv-apparel' ); ?></a>
		</form>

		<table class="widefat striped xiv-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Gambar', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Nama', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'SKU', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Harga', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Stok', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Status', 'xiv-apparel' ); ?></th>
					<th><?php esc_html_e( 'Aksi', 'xiv-apparel' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! $query->have_posts() ) : ?>
					<tr><td colspan="7"><?php esc_html_e( 'Tidak ada produk ditemukan.', 'xiv-apparel' ); ?></td></tr>
				<?php endif; ?>
				<?php while ( $query->have_posts() ) : $query->the_post();
					$p = wc_get_product( get_the_ID() );
					if ( ! $p ) { continue; }
					$edit_url   = admin_url( 'admin.php?page=xiv-product-edit&product_id=' . $p->get_id() );
					$delete_url = wp_nonce_url( admin_url( 'admin-post.php?action=xiv_delete_product&product_id=' . $p->get_id() ), 'xiv_delete_product_' . $p->get_id() );
					?>
					<tr>
						<td><?php echo wp_kses_post( $p->get_image( 'thumbnail' ) ); ?></td>
						<td><strong><?php echo esc_html( $p->get_name() ); ?></strong></td>
						<td><?php echo esc_html( $p->get_sku() ?: '—' ); ?></td>
						<td><?php echo wp_kses_post( $p->get_price_html() ); ?></td>
						<td><?php echo $p->is_in_stock() ? esc_html__( 'Tersedia', 'xiv-apparel' ) : esc_html__( 'Habis', 'xiv-apparel' ); ?></td>
						<td><?php echo esc_html( ucfirst( get_post_status_object( $p->get_status() )->label ?? $p->get_status() ) ); ?></td>
						<td class="xiv-admin-actions">
							<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'xiv-apparel' ); ?></a> |
							<a href="<?php echo esc_url( $p->get_permalink() ); ?>" target="_blank"><?php esc_html_e( 'Lihat', 'xiv-apparel' ); ?></a> |
							<a href="<?php echo esc_url( $delete_url ); ?>" class="xiv-admin-delete" data-confirm="<?php esc_attr_e( 'Hapus produk ini?', 'xiv-apparel' ); ?>"><?php esc_html_e( 'Hapus', 'xiv-apparel' ); ?></a>
						</td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>

		<?php
		$total_pages = $query->max_num_pages;
		if ( $total_pages > 1 ) {
			$pagination_base = admin_url(
				'admin.php?page=xiv-products&s=' . rawurlencode( $search ) . '&cat=' . $cat_id . '%_%'
			);
			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo wp_kses_post( paginate_links( array(
				'base'    => $pagination_base,
				'format'  => '&paged=%#%',
				'current' => $paged,
				'total'   => $total_pages,
				'type'    => 'plain',
			) ) );
			echo '</div></div>';
		}
		wp_reset_postdata();
		?>
	</div>
	<?php
}

/**
 * Halaman form tambah/edit produk.
 */
function xiv_admin_product_form_page() {
	$product_id = absint( $_GET['product_id'] ?? 0 );
	$product    = $product_id ? wc_get_product( $product_id ) : null;

	if ( $product_id && ! $product ) {
		echo '<div class="wrap"><div class="notice notice-error"><p>' . esc_html__( 'Produk tidak ditemukan.', 'xiv-apparel' ) . '</p></div></div>';
		return;
	}

	$is_variable  = $product && $product->is_type( 'variable' );
	$is_new       = ! $product;
	$name         = $product ? $product->get_name() : '';
	$short_desc   = $product ? $product->get_short_description() : '';
	$description  = $product ? $product->get_description() : '';
	$sku          = $product ? $product->get_sku() : '';
	$reg_price    = $product ? $product->get_regular_price() : '';
	$sale_price   = $product ? $product->get_sale_price() : '';
	$image_id     = $product ? $product->get_image_id() : 0;
	$gallery_ids  = $product ? $product->get_gallery_image_ids() : array();
	$status       = $product ? $product->get_status() : 'publish';
	$stock_qty    = $product ? $product->get_stock_quantity() : '';
	$product_cats = $product ? wc_get_product_term_ids( $product->get_id(), 'product_cat' ) : array();

	$variations_data = array();
	if ( $is_variable ) {
		$attrs = $product->get_variation_attributes();
		$sizes = ! empty( $attrs['pa_size'] ) ? $attrs['pa_size'] : array();
		foreach ( $sizes as $size ) {
			$vid = xiv_find_variation_by_size( $product, $size );
			$v   = $vid ? wc_get_product( $vid ) : null;
			$variations_data[ $size ] = array(
				'price' => $v ? $v->get_regular_price() : '',
				'sale'  => $v ? $v->get_sale_price() : '',
				'qty'   => $v ? (string) $v->get_stock_quantity() : '',
			);
		}
	}

	$all_cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
	?>
	<div class="wrap xiv-admin">
		<h1 class="xiv-admin-title"><?php echo $is_new ? esc_html__( 'Tambah Produk', 'xiv-apparel' ) : esc_html__( 'Edit Produk', 'xiv-apparel' ); ?></h1>

		<form method="post" class="xiv-admin-form" id="xiv-product-form">
			<?php wp_nonce_field( 'xiv_product_nonce', 'xiv_product_nonce' ); ?>
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>" />
			<input type="hidden" name="product_image_id" id="xiv-image-id" value="<?php echo esc_attr( $image_id ); ?>" />
			<input type="hidden" name="product_gallery_ids" id="xiv-gallery-ids" value="<?php echo esc_attr( implode( ',', $gallery_ids ) ); ?>" />

			<div class="xiv-admin-grid">
				<div class="xiv-admin-main">

					<div class="xiv-admin-field">
						<label for="product_name"><?php esc_html_e( 'Nama Produk *', 'xiv-apparel' ); ?></label>
						<input type="text" id="product_name" name="product_name" required value="<?php echo esc_attr( $name ); ?>" />
					</div>

					<div class="xiv-admin-field">
						<label for="short_description"><?php esc_html_e( 'Deskripsi Singkat', 'xiv-apparel' ); ?></label>
						<input type="text" id="short_description" name="short_description" value="<?php echo esc_attr( $short_desc ); ?>" placeholder="<?php esc_attr_e( 'mis. Cotton T Shirt', 'xiv-apparel' ); ?>" />
					</div>

					<div class="xiv-admin-field">
						<label for="description"><?php esc_html_e( 'Deskripsi Lengkap', 'xiv-apparel' ); ?></label>
						<textarea id="description" name="description" rows="6"><?php echo esc_textarea( $description ); ?></textarea>
					</div>

					<div class="xiv-admin-field xiv-admin-row">
						<div>
							<label for="regular_price"><?php esc_html_e( 'Harga Reguler *', 'xiv-apparel' ); ?></label>
							<input type="number" step="0.01" min="0" id="regular_price" name="regular_price" required value="<?php echo esc_attr( $reg_price ); ?>" />
						</div>
						<div>
							<label for="sale_price"><?php esc_html_e( 'Harga Promo', 'xiv-apparel' ); ?></label>
							<input type="number" step="0.01" min="0" id="sale_price" name="sale_price" value="<?php echo esc_attr( $sale_price ); ?>" />
						</div>
						<div>
							<label for="sku"><?php esc_html_e( 'SKU', 'xiv-apparel' ); ?></label>
							<input type="text" id="sku" name="sku" value="<?php echo esc_attr( $sku ); ?>" />
						</div>
					</div>

					<div class="xiv-admin-field">
						<label><?php esc_html_e( 'Tipe Produk', 'xiv-apparel' ); ?></label>
						<div class="xiv-admin-radio">
							<label>
								<input type="radio" name="product_type" value="simple" <?php checked( ! $is_variable ); ?> class="xiv-type-radio" data-target="simple" />
								<?php esc_html_e( 'Simple (harga tunggal)', 'xiv-apparel' ); ?>
							</label>
							<label>
								<input type="radio" name="product_type" value="variable" <?php checked( $is_variable ); ?> class="xiv-type-radio" data-target="variable" />
								<?php esc_html_e( 'Variable (ukuran XS–2X)', 'xiv-apparel' ); ?>
							</label>
						</div>
					</div>

					<!-- Simple fields -->
					<div class="xiv-type-panel" data-panel="simple">
						<div class="xiv-admin-field xiv-admin-row">
							<div>
								<label for="stock_quantity"><?php esc_html_e( 'Jumlah Stok', 'xiv-apparel' ); ?></label>
								<input type="number" min="0" id="stock_quantity" name="stock_quantity" value="<?php echo esc_attr( $stock_qty ); ?>" />
							</div>
						</div>
					</div>

					<!-- Variable fields -->
					<div class="xiv-type-panel xiv-admin-hidden" data-panel="variable">
						<p class="xiv-admin-note"><?php esc_html_e( 'Isi harga & stok per ukuran. Kosongkan harga jika ukuran tidak dijual.', 'xiv-apparel' ); ?></p>
						<table class="widefat xiv-size-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Ukuran', 'xiv-apparel' ); ?></th>
									<th><?php esc_html_e( 'Harga', 'xiv-apparel' ); ?></th>
									<th><?php esc_html_e( 'Harga Promo', 'xiv-apparel' ); ?></th>
									<th><?php esc_html_e( 'Stok', 'xiv-apparel' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( array( 'XS', 'S', 'M', 'L', 'XL', '2X' ) as $size ) :
									$d = $variations_data[ $size ] ?? array( 'price' => '', 'sale' => '', 'qty' => '' );
									?>
									<tr>
										<td><strong><?php echo esc_html( $size ); ?></strong></td>
										<td><input type="number" step="0.01" min="0" name="size_price[<?php echo esc_attr( $size ); ?>]" value="<?php echo esc_attr( $d['price'] ); ?>" /></td>
										<td><input type="number" step="0.01" min="0" name="size_sale[<?php echo esc_attr( $size ); ?>]" value="<?php echo esc_attr( $d['sale'] ); ?>" /></td>
										<td><input type="number" min="0" name="size_qty[<?php echo esc_attr( $size ); ?>]" value="<?php echo esc_attr( $d['qty'] ); ?>" /></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<div class="xiv-admin-field">
						<label for="status"><?php esc_html_e( 'Status', 'xiv-apparel' ); ?></label>
						<select id="status" name="status">
							<option value="publish" <?php selected( $status, 'publish' ); ?>><?php esc_html_e( 'Terbit', 'xiv-apparel' ); ?></option>
							<option value="draft" <?php selected( $status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'xiv-apparel' ); ?></option>
						</select>
					</div>
				</div>

				<div class="xiv-admin-side">
					<div class="xiv-admin-card">
						<h3><?php esc_html_e( 'Gambar Produk', 'xiv-apparel' ); ?></h3>
						<div class="xiv-media-preview">
							<?php if ( $image_id ) : ?>
								<img id="xiv-image-preview" src="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'thumbnail' ) ); ?>" alt="" />
							<?php else : ?>
								<img id="xiv-image-preview" src="" alt="" style="display:none;" />
							<?php endif; ?>
						</div>
						<button type="button" class="button" id="xiv-upload-image"><?php esc_html_e( 'Pilih Gambar Utama', 'xiv-apparel' ); ?></button>
						<button type="button" class="button xiv-admin-hidden" id="xiv-remove-image"><?php esc_html_e( 'Hapus', 'xiv-apparel' ); ?></button>
					</div>

					<div class="xiv-admin-card">
						<h3><?php esc_html_e( 'Galeri (opsional)', 'xiv-apparel' ); ?></h3>
						<div class="xiv-gallery-preview" id="xiv-gallery-preview">
							<?php foreach ( $gallery_ids as $gid ) : ?>
								<img src="<?php echo esc_url( wp_get_attachment_image_url( $gid, 'thumbnail' ) ); ?>" data-id="<?php echo esc_attr( $gid ); ?>" alt="" />
							<?php endforeach; ?>
						</div>
						<button type="button" class="button" id="xiv-upload-gallery"><?php esc_html_e( 'Tambah Gambar', 'xiv-apparel' ); ?></button>
					</div>

					<div class="xiv-admin-card">
						<h3><?php esc_html_e( 'Kategori', 'xiv-apparel' ); ?></h3>
						<?php if ( ! empty( $all_cats ) && ! is_wp_error( $all_cats ) ) : ?>
							<?php foreach ( $all_cats as $cat ) : ?>
								<label class="xiv-admin-check">
									<input type="checkbox" name="product_cats[]" value="<?php echo esc_attr( $cat->term_id ); ?>" <?php checked( in_array( $cat->term_id, $product_cats, true ) ); ?> />
									<?php echo esc_html( $cat->name ); ?>
								</label>
							<?php endforeach; ?>
						<?php endif; ?>
						<input type="text" name="new_category" placeholder="<?php esc_attr_e( 'Kategori baru…', 'xiv-apparel' ); ?>" />
					</div>
				</div>
			</div>

			<div class="xiv-admin-save">
				<button type="submit" name="xiv_save_product" value="1" class="button button-primary button-hero">
					<?php echo $is_new ? esc_html__( 'Simpan Produk', 'xiv-apparel' ) : esc_html__( 'Perbarui Produk', 'xiv-apparel' ); ?>
				</button>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=xiv-products' ) ); ?>"><?php esc_html_e( 'Batal', 'xiv-apparel' ); ?></a>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Cari id variation berdasarkan atribut ukuran.
 */
function xiv_find_variation_by_size( $product, $size ) {
	foreach ( $product->get_children() as $vid ) {
		$v = wc_get_product( $vid );
		if ( $v ) {
			$attrs = $v->get_attributes();
			if ( isset( $attrs['pa_size'] ) && $attrs['pa_size'] === $size ) {
				return $vid;
			}
		}
	}
	return 0;
}

/**
 * Handler simpan produk (tambah & edit).
 */
function xiv_admin_product_save() {
	if ( empty( $_POST['xiv_save_product'] ) ) {
		return;
	}

	check_admin_referer( 'xiv_product_nonce', 'xiv_product_nonce' );

	if ( ! current_user_can( XIV_ADMIN_CAP ) ) {
		wp_die( esc_html__( 'Anda tidak memiliki izin.', 'xiv-apparel' ) );
	}

	$product_id  = absint( $_POST['product_id'] ?? 0 );
	$type        = ( $_POST['product_type'] ?? 'simple' ) === 'variable' ? 'variable' : 'simple';
	$name        = sanitize_text_field( wp_unslash( $_POST['product_name'] ?? '' ) );
	$short_desc  = sanitize_textarea_field( wp_unslash( $_POST['short_description'] ?? '' ) );
	$description = wp_kses_post( wp_unslash( $_POST['description'] ?? '' ) );
	$status      = ( $_POST['status'] ?? 'publish' ) === 'draft' ? 'draft' : 'publish';
	$sku         = sanitize_text_field( wp_unslash( $_POST['sku'] ?? '' ) );
	$image_id    = absint( $_POST['product_image_id'] ?? 0 );
	$gallery_ids = array_filter( array_map( 'absint', explode( ',', (string) ( $_POST['product_gallery_ids'] ?? '' ) ) ) );
	$new_cat     = sanitize_text_field( wp_unslash( $_POST['new_category'] ?? '' ) );
	$cats        = array_map( 'absint', (array) ( $_POST['product_cats'] ?? array() ) );

	if ( ! $name ) {
		xiv_admin_redirect_error( __( 'Nama produk wajib diisi.', 'xiv-apparel' ) );
	}

	if ( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			xiv_admin_redirect_error( __( 'Produk tidak ditemukan.', 'xiv-apparel' ) );
		}
		$was_variable = $product->is_type( 'variable' );
	} else {
		$product = 'variable' === $type ? new WC_Product_Variable() : new WC_Product_Simple();
		$was_variable = 'variable' === $type;
	}

	try {
		$product->set_name( $name );
		$product->set_status( $status );
		$product->set_short_description( $short_desc );
		$product->set_description( $description );
		if ( $sku ) {
			$product->set_sku( $sku );
		}
		$product->set_image_id( $image_id );
		$product->set_gallery_image_ids( $gallery_ids );

		$new_id = $product->save();
	} catch ( Exception $e ) {
		xiv_admin_redirect_error( $e->getMessage() );
	}

	$product = wc_get_product( $new_id );

	// Kategori (termasuk kategori baru).
	if ( $new_cat ) {
		$created = wp_insert_term( $new_cat, 'product_cat' );
		if ( ! is_wp_error( $created ) ) {
			$cats[] = (int) $created['term_id'];
		}
	}
	if ( ! empty( $cats ) ) {
		wp_set_object_terms( $product->get_id(), $cats, 'product_cat' );
	}

	$type = $product->is_type( 'variable' ) ? 'variable' : 'simple';

	if ( 'simple' === $type ) {
		if ( $was_variable ) {
			foreach ( $product->get_children() as $old_vid ) {
				$old = wc_get_product( $old_vid );
				if ( $old ) {
					$old->delete( true );
				}
			}
			$product->set_attributes( array() );
		}

		$reg = (float) ( $_POST['regular_price'] ?? 0 );
		$sale = (float) ( $_POST['sale_price'] ?? 0 );

		$product->set_regular_price( $reg > 0 ? wc_format_decimal( $reg ) : '' );
		$product->set_sale_price( $sale > 0 && $sale < $reg ? wc_format_decimal( $sale ) : '' );
		$qty = max( 0, absint( $_POST['stock_quantity'] ?? 0 ) );
		$product->set_manage_stock( true );
		$product->set_stock_quantity( $qty );
		$product->set_stock_status( $qty > 0 ? 'instock' : 'outofstock' );
		$product->save();
	} else {
		xiv_admin_sync_variable_product( $product );
	}

	$edit_url = admin_url( 'admin.php?page=xiv-product-edit&product_id=' . $product->get_id() );
	$notice   = __( 'Produk berhasil disimpan.', 'xiv-apparel' );
	set_transient( 'xiv_admin_notice', $notice, 60 );
	wp_safe_redirect( $edit_url );
	exit;
}

/**
 * Sinkronisasi atribut pa_size + variation untuk produk variable.
 */
function xiv_admin_sync_variable_product( $product ) {
	$size_data = array();
	$size_keys = array( 'XS', 'S', 'M', 'L', 'XL', '2X' );

	foreach ( $size_keys as $size ) {
		$price = (float) ( $_POST['size_price'][ $size ] ?? 0 );
		$sale  = (float) ( $_POST['size_sale'][ $size ] ?? 0 );
		$qty   = max( 0, absint( $_POST['size_qty'][ $size ] ?? 0 ) );
		if ( $price > 0 ) {
			$size_data[ $size ] = compact( 'price', 'sale', 'qty' );
		}
	}

	if ( empty( $size_data ) ) {
		xiv_admin_redirect_error( __( 'Produk variable butuh minimal satu ukuran dengan harga.', 'xiv-apparel' ) );
	}

	xiv_admin_ensure_size_attribute( array_keys( $size_data ) );

	$attr = new WC_Product_Attribute();
	$attr->set_id( (int) wc_attribute_taxonomy_id_by_name( 'size' ) );
	$attr->set_name( 'pa_size' );
	$attr->set_options( array_keys( $size_data ) );
	$attr->set_visible( true );
	$attr->set_variation( true );
	$product->set_attributes( array( $attr ) );
	$product->save();

	// Hapus variation lama.
	foreach ( $product->get_children() as $old_vid ) {
		$old = wc_get_product( $old_vid );
		if ( $old ) {
			$old->delete( true );
		}
	}

	foreach ( $size_data as $size => $d ) {
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_attributes( array( 'pa_size' => $size ) );
		$variation->set_regular_price( wc_format_decimal( $d['price'] ) );
		if ( $d['sale'] > 0 && $d['sale'] < $d['price'] ) {
			$variation->set_sale_price( wc_format_decimal( $d['sale'] ) );
		}
		$variation->set_manage_stock( true );
		$variation->set_stock_quantity( $d['qty'] );
		$variation->set_stock_status( $d['qty'] > 0 ? 'instock' : 'outofstock' );
		$variation->save();
	}

	WC_Product_Variable::sync( $product->get_id() );
}

/**
 * Pastikan atribut pa_size & term ukuran tersedia.
 */
function xiv_admin_ensure_size_attribute( $sizes ) {
	$attr_id = wc_attribute_taxonomy_id_by_name( 'size' );

	if ( ! $attr_id ) {
		wc_create_attribute( array(
			'name'         => 'Size',
			'slug'         => 'size',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
		delete_transient( 'wc_attribute_taxonomies' );
	}

	if ( ! taxonomy_exists( 'pa_size' ) ) {
		register_taxonomy( 'pa_size', array( 'product' ), array(
			'label'                => __( 'Size', 'xiv-apparel' ),
			'public'               => true,
			'hierarchical'         => false,
			'show_ui'              => true,
			'show_in_rest'         => true,
			'rewrite'              => array( 'slug' => 'size' ),
			'update_count_callback' => '_update_post_term_count',
		) );
	}

	foreach ( $sizes as $size ) {
		if ( ! term_exists( $size, 'pa_size' ) ) {
			wp_insert_term( $size, 'pa_size' );
		}
	}
}

/**
 * Handler hapus produk.
 */
function xiv_admin_product_delete() {
	$product_id = absint( $_GET['product_id'] ?? 0 );
	if ( ! $product_id || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'xiv_delete_product_' . $product_id ) ) {
		wp_die( esc_html__( 'Verifikasi gagal.', 'xiv-apparel' ) );
	}
	if ( ! current_user_can( XIV_ADMIN_CAP ) ) {
		wp_die( esc_html__( 'Anda tidak memiliki izin.', 'xiv-apparel' ) );
	}

	$product = wc_get_product( $product_id );
	if ( $product ) {
		$product->delete( true );
	}

	set_transient( 'xiv_admin_notice', __( 'Produk dihapus.', 'xiv-apparel' ), 60 );
	wp_safe_redirect( admin_url( 'admin.php?page=xiv-products' ) );
	exit;
}

/**
 * Tampilkan notice admin dari transient.
 */
function xiv_admin_show_notice() {
	$notice = get_transient( 'xiv_admin_notice' );
	if ( $notice ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $notice ) . '</p></div>';
		delete_transient( 'xiv_admin_notice' );
	}
}
add_action( 'admin_notices', 'xiv_admin_show_notice' );

/**
 * Redirect dengan pesan error (disimpan di transient).
 */
function xiv_admin_redirect_error( $message ) {
	set_transient( 'xiv_admin_notice', $message, 60 );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=xiv-products' ) );
	exit;
}

/**
 * Enqueue asset admin (media uploader).
 */
function xiv_admin_assets( $hook ) {
	if ( false === strpos( (string) $hook, 'xiv' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'xiv-admin', get_template_directory_uri() . '/assets/admin/css/admin.css', array(), XIV_VERSION );
	wp_enqueue_script( 'xiv-admin', get_template_directory_uri() . '/assets/admin/js/admin.js', array( 'jquery', 'media-upload' ), XIV_VERSION, true );
}
