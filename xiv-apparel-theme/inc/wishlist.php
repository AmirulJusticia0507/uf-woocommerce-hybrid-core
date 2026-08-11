<?php
/**
 * Wishlist (favorite products).
 *
 * Logged-in users: stored in user meta `_xiv_wishlist`.
 * Guests: stored in a `xiv_wishlist` cookie, managed client-side + echoed by AJAX.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

define( 'XIV_WISHLIST_COOKIE', 'xiv_wishlist' );
define( 'XIV_WISHLIST_META', '_xiv_wishlist' );

/**
 * Get wishlist product IDs for current user (cookie for guests).
 *
 * @return int[]
 */
function xiv_wishlist_get_ids() {
	if ( is_user_logged_in() ) {
		$ids = get_user_meta( get_current_user_id(), XIV_WISHLIST_META, true );
		return xiv_wishlist_sanitize_ids( $ids );
	}

	if ( ! empty( $_COOKIE[ XIV_WISHLIST_COOKIE ] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		return xiv_wishlist_sanitize_ids( explode( ',', sanitize_text_field( wp_unslash( $_COOKIE[ XIV_WISHLIST_COOKIE ] ) ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	}

	return array();
}

/**
 * Persist wishlist IDs for current user.
 *
 * @param int[] $ids Product IDs.
 */
function xiv_wishlist_save_ids( $ids ) {
	$ids = xiv_wishlist_sanitize_ids( $ids );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), XIV_WISHLIST_META, $ids );
		return;
	}

	setcookie( XIV_WISHLIST_COOKIE, implode( ',', $ids ), time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
}

/**
 * @param mixed $ids
 * @return int[]
 */
function xiv_wishlist_sanitize_ids( $ids ) {
	if ( ! is_array( $ids ) ) {
		$ids = $ids ? explode( ',', (string) $ids ) : array();
	}

	$out = array();
	foreach ( array_map( 'absint', $ids ) as $id ) {
		if ( $id && 'product' === get_post_type( $id ) ) {
			$out[ $id ] = $id;
		}
	}
	return array_values( $out );
}

/**
 * @return int
 */
function xiv_wishlist_count() {
	return count( xiv_wishlist_get_ids() );
}

/**
 * Toggle a product in the wishlist.
 *
 * @param int $product_id
 * @return array{ids:int[],added:bool,count:int}
 */
function xiv_wishlist_toggle( $product_id ) {
	$product_id = absint( $product_id );
	$ids        = xiv_wishlist_get_ids();
	$added      = true;

	if ( in_array( $product_id, $ids, true ) ) {
		$ids    = array_values( array_diff( $ids, array( $product_id ) ) );
		$added  = false;
	} else {
		array_unshift( $ids, $product_id );
	}

	$ids = xiv_wishlist_sanitize_ids( $ids );
	xiv_wishlist_save_ids( $ids );

	return array(
		'ids'   => $ids,
		'added' => $added,
		'count' => count( $ids ),
	);
}

/**
 * Auto-create the Wishlist page once.
 */
function xiv_wishlist_create_page() {
	$page_id = (int) get_option( 'xiv_wishlist_page_id' );

	if ( ! $page_id || ! get_post( $page_id ) ) {
		$page_id = wp_insert_post( array(
			'post_title'     => __( 'Wishlist', 'xiv-apparel' ),
			'post_name'      => 'wishlist',
			'post_content'   => '[xiv_wishlist]',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'comment_status' => 'closed',
		) );
		update_option( 'xiv_wishlist_page_id', $page_id );
	}

	return $page_id;
}
add_action( 'after_switch_theme', 'xiv_wishlist_create_page', 30 );

/**
 * @return string
 */
function xiv_wishlist_url() {
	$page_id = (int) get_option( 'xiv_wishlist_page_id' );
	return $page_id ? get_permalink( $page_id ) : home_url( '/' );
}

/**
 * AJAX: toggle wishlist.
 */
function xiv_wishlist_ajax_toggle() {
	check_ajax_referer( 'xiv_wishlist_nonce', 'nonce' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error( array( 'message' => xiv_t( 'Invalid product.' ) ) );
	}

	wp_send_json_success( xiv_wishlist_toggle( $product_id ) );
}
add_action( 'wp_ajax_xiv_wishlist_toggle', 'xiv_wishlist_ajax_toggle' );
add_action( 'wp_ajax_nopriv_xiv_wishlist_toggle', 'xiv_wishlist_ajax_toggle' );

/**
 * AJAX: remove single item (wishlist page).
 */
function xiv_wishlist_ajax_remove() {
	check_ajax_referer( 'xiv_wishlist_nonce', 'nonce' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$ids        = xiv_wishlist_get_ids();

	if ( in_array( $product_id, $ids, true ) ) {
		$ids = array_values( array_diff( $ids, array( $product_id ) ) );
		xiv_wishlist_save_ids( $ids );
	}

	wp_send_json_success( array(
		'ids'   => $ids,
		'count' => count( $ids ),
	) );
}
add_action( 'wp_ajax_xiv_wishlist_remove', 'xiv_wishlist_ajax_remove' );
add_action( 'wp_ajax_nopriv_xiv_wishlist_remove', 'xiv_wishlist_ajax_remove' );

/**
 * AJAX: refresh wishlist (used on load for guests).
 */
function xiv_wishlist_ajax_get() {
	check_ajax_referer( 'xiv_wishlist_nonce', 'nonce' );

	wp_send_json_success( array(
		'ids'   => xiv_wishlist_get_ids(),
		'count' => xiv_wishlist_count(),
	) );
}
add_action( 'wp_ajax_xiv_wishlist_get', 'xiv_wishlist_ajax_get' );
add_action( 'wp_ajax_nopriv_xiv_wishlist_get', 'xiv_wishlist_ajax_get' );

/**
 * Render the wishlist page.
 */
function xiv_wishlist_shortcode() {
	$ids = xiv_wishlist_get_ids();

	if ( empty( $ids ) ) {
		ob_start();
		?>
		<div class="xiv-wishlist xiv-py-10 xiv-text-center">
			<p class="xiv-text-sm xiv-font-mono xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text"><?php echo esc_html( xiv_t( 'YOUR WISHLIST IS EMPTY' ) ); ?></p>
			<a class="xiv-inline-block xiv-mt-6 xiv-bg-xiv-black xiv-text-white xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-py-3 xiv-px-6" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php echo esc_html( xiv_t( 'BROWSE PRODUCTS' ) ); ?></a>
		</div>
		<?php
		return ob_get_clean();
	}

	ob_start();
	?>
	<div class="xiv-wishlist">
		<div class="xiv-wishlist-empty-placeholder xiv-hidden xiv-py-10 xiv-text-center">
			<p class="xiv-text-sm xiv-font-mono xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text"><?php echo esc_html( xiv_t( 'YOUR WISHLIST IS EMPTY' ) ); ?></p>
			<a class="xiv-inline-block xiv-mt-6 xiv-bg-xiv-black xiv-text-white xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-py-3 xiv-px-6" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php echo esc_html( xiv_t( 'BROWSE PRODUCTS' ) ); ?></a>
		</div>

		<div class="xiv-grid xiv-grid-cols-2 md:xiv-grid-cols-3 lg:xiv-grid-cols-4 xiv-gap-x-4 xiv-gap-y-8">
			<?php
			$products = wc_get_products( array(
				'include' => $ids,
				'limit'   => -1,
				'orderby' => 'post__in',
			) );

			foreach ( $products as $product ) :
				$product_id = $product->get_id();
				?>
				<div class="xiv-group xiv-relative" data-wishlist-item>
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="xiv-block">
						<div class="xiv-relative xiv-aspect-[3/4] xiv-bg-stone-200 xiv-overflow-hidden">
							<?php echo $product->get_image( 'xiv-product-grid', array( 'class' => 'xiv-w-full xiv-h-full xiv-object-cover xiv-transition xiv-duration-300 group-hover:xiv-scale-105' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<div class="xiv-mt-3 xiv-flex xiv-justify-between xiv-items-start xiv-gap-2">
							<div class="xiv-min-w-0">
								<h3 class="xiv-text-sm xiv-font-bold xiv-text-xiv-black xiv-m-0 xiv-truncate"><?php echo esc_html( $product->get_name() ); ?></h3>
							</div>
							<span class="xiv-text-sm xiv-font-bold xiv-text-xiv-black xiv-shrink-0"><?php echo wp_kses_post( xiv_product_price( $product ) ); ?></span>
						</div>
					</a>
					<button type="button" class="xiv-wishlist-remove xiv-absolute xiv-top-3 xiv-right-3 xiv-p-2 xiv-bg-white xiv-border xiv-border-xiv-gray-light xiv-text-xiv-black" data-product-id="<?php echo esc_attr( $product_id ); ?>" aria-label="<?php echo esc_attr( xiv_t( 'Remove from wishlist' ) ); ?>">
						<svg class="xiv-w-4 xiv-h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="square"/></svg>
					</button>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'xiv_wishlist', 'xiv_wishlist_shortcode' );

/**
 * Wishlist toggle button on the product page (PDP summary).
 */
function xiv_pdp_wishlist_button() {
	global $product;

	if ( ! $product ) {
		return;
	}

	$product_id = $product->get_id();
	$active     = in_array( $product_id, xiv_wishlist_get_ids(), true );
	?>
	<button type="button" class="xiv-wishlist-toggle xiv-mt-4 xiv-w-full xiv-flex xiv-items-center xiv-justify-between xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-black xiv-border xiv-border-xiv-gray-light xiv-px-4 xiv-py-2.5 xiv-transition hover:xiv-border-xiv-black <?php echo $active ? 'xiv-is-active xiv-border-xiv-black' : ''; ?>"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			data-label-on="<?php echo esc_attr( xiv_t( 'SAVED TO WISHLIST' ) ); ?>"
			data-label-off="<?php echo esc_attr( xiv_t( 'SAVE TO WISHLIST' ) ); ?>"
			aria-pressed="<?php echo $active ? 'true' : 'false'; ?>">
		<span class="xiv-flex xiv-items-center xiv-gap-2">
			<svg class="xiv-w-4 xiv-h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 20.5s-7.5-4.7-7.5-10A4.5 4.5 0 0 1 12 6.4a4.5 4.5 0 0 1 7.5 4.1c0 5.3-7.5 10-7.5 10Z" stroke-linejoin="round"/></svg>
			<span class="xiv-wishlist-btn-label"><?php echo esc_html( xiv_t( $active ? 'SAVED TO WISHLIST' : 'SAVE TO WISHLIST' ) ); ?></span>
		</span>
		<span aria-hidden="true">&rarr;</span>
	</button>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'xiv_pdp_wishlist_button', 11 );
