<?php
/**
 * Cart drawer + AJAX add-to-cart + fragments.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

/**
 * AJAX add-to-cart endpoint. Returns JSON fragments + cart count.
 */
function xiv_ajax_add_to_cart() {
	check_ajax_referer( 'xiv_cart_nonce', 'security' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$quantity   = absint( $_POST['quantity'] ?? 1 );
	$variation_id = absint( $_POST['variation_id'] ?? 0 );

	if ( ! $product_id || ! function_exists( 'WC' ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid product.', 'xiv-apparel' ) ) );
	}

	$variation = array();
	if ( ! empty( $_POST['variation'] ) && is_array( $_POST['variation'] ) ) {
		$variation = array_map( 'sanitize_text_field', wp_unslash( $_POST['variation'] ) );
	}

	WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

	wp_send_json_success( array(
		'count'      => WC()->cart->get_cart_contents_count(),
		'total'      => WC()->cart->get_cart_subtotal(),
		'fragments'  => xiv_cart_fragments(),
	) );
}

function xiv_ajax_update_cart() {
	check_ajax_referer( 'xiv_cart_nonce', 'security' );

	$cart_item_key = sanitize_text_field( $_POST['cart_item_key'] ?? '' );
	$quantity      = isset( $_POST['quantity'] ) ? max( 0, absint( $_POST['quantity'] ) ) : 1;

	if ( ! $cart_item_key || ! function_exists( 'WC' ) ) {
		wp_send_json_error();
	}

	if ( $quantity <= 0 ) {
		WC()->cart->remove_cart_item( $cart_item_key );
	} else {
		WC()->cart->set_quantity( $cart_item_key, $quantity );
	}

	wp_send_json_success( array(
		'count'     => WC()->cart->get_cart_contents_count(),
		'total'     => WC()->cart->get_cart_subtotal(),
		'fragments' => xiv_cart_fragments(),
	) );
}

/**
 * Return cart fragments for the drawer + header count.
 */
function xiv_cart_fragments() {
	ob_start();
	xiv_cart_drawer_items();
	$items = ob_get_clean();

	ob_start();
	xiv_cart_drawer_footer();
	$footer = ob_get_clean();

	return array(
		'items'      => $items,
		'footer'     => $footer,
		'countBadge' => '<span class="xiv-cart-count xiv-text-[10px] xiv-font-bold">' . esc_html( WC()->cart->get_cart_contents_count() ) . '</span>',
	);
}

/**
 * Renders mini-cart line items for the drawer.
 */
function xiv_cart_drawer_items() {
	if ( WC()->cart->is_empty() ) {
		echo '<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-uppercase xiv-text-center xiv-py-16">' . esc_html__( 'YOUR BAG IS EMPTY', 'xiv-apparel' ) . '</p>';
		return;
	}

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$_product   = $cart_item['data'];
		$permalink  = $_product->get_permalink( $cart_item );
		$thumbnail  = $_product->get_image( 'thumbnail' );
		$line_total = WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] );
		?>
		<div class="xiv-cart-item xiv-flex xiv-gap-3 xiv-py-4 xiv-border-b xiv-border-xiv-gray-light" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
			<a href="<?php echo esc_url( $permalink ); ?>" class="xiv-w-16 xiv-aspect-[3/4] xiv-bg-stone-200 xiv-overflow-hidden xiv-shrink-0">
				<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>
			<div class="xiv-flex-1 xiv-min-w-0">
				<p class="xiv-text-xs xiv-text-xiv-gray-text xiv-uppercase xiv-truncate"><?php echo esc_html( $_product->get_name() ); ?></p>
				<p class="xiv-text-sm xiv-font-bold xiv-mt-1"><?php echo wp_kses_post( $line_total ); ?></p>
				<div class="xiv-flex xiv-items-center xiv-gap-3 xiv-mt-2">
					<input type="number" min="1" value="<?php echo esc_attr( $cart_item['quantity'] ); ?>"
						   class="xiv-cart-qty xiv-w-14 xiv-border xiv-border-xiv-gray-light xiv-bg-transparent xiv-text-center xiv-text-xs xiv-py-1 xiv-font-mono"
						   aria-label="<?php esc_attr_e( 'Quantity', 'xiv-apparel' ); ?>" />
					<button type="button" class="xiv-cart-remove xiv-text-xs xiv-font-mono xiv-uppercase xiv-text-xiv-gray-text hover:xiv-text-xiv-black" aria-label="<?php esc_attr_e( 'Remove item', 'xiv-apparel' ); ?>">
						<?php esc_html_e( 'Remove', 'xiv-apparel' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}
}

/**
 * Renders mini-cart footer (subtotal + checkout button).
 */
function xiv_cart_drawer_footer() {
	?>
	<div class="xiv-mt-4">
		<div class="xiv-flex xiv-justify-between xiv-items-baseline xiv-py-2">
			<span class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-wide"><?php esc_html_e( 'SUBTOTAL', 'xiv-apparel' ); ?></span>
			<span class="xiv-cart-total xiv-text-lg xiv-font-black"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>
		</div>
		<p class="xiv-text-xs xiv-text-xiv-gray-text xiv-font-mono xiv-mb-4"><?php esc_html_e( 'MRP incl. of all taxes', 'xiv-apparel' ); ?></p>
		<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"
		   class="xiv-block xiv-w-full xiv-bg-xiv-black xiv-text-white xiv-text-sm xiv-font-bold xiv-uppercase xiv-tracking-wide xiv-py-4 xiv-px-6 xiv-flex xiv-items-center xiv-justify-between xiv-transition hover:xiv-bg-xiv-gray-text">
			<span><?php esc_html_e( 'CHECKOUT', 'xiv-apparel' ); ?></span><span aria-hidden="true">&rarr;</span>
		</a>
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="xiv-block xiv-w-full xiv-text-center xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-wide xiv-py-3 xiv-text-xiv-gray-text hover:xiv-text-xiv-black">
			<?php esc_html_e( 'VIEW FULL BAG', 'xiv-apparel' ); ?>
		</a>
	</div>
	<?php
}

add_action( 'wp_ajax_xiv_add_to_cart', 'xiv_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_xiv_add_to_cart', 'xiv_ajax_add_to_cart' );
add_action( 'wp_ajax_xiv_update_cart', 'xiv_ajax_update_cart' );
add_action( 'wp_ajax_nopriv_xiv_update_cart', 'xiv_ajax_update_cart' );

/**
 * Keep WooCommerce default cart fragments working too.
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'xiv_header_cart_fragment', 10, 1 );
function xiv_header_cart_fragment( $fragments ) {
	ob_start();
	?>
	<span class="xiv-cart-count xiv-text-[10px] xiv-font-bold"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
	<?php
	$fragments['.xiv-cart-count'] = ob_get_clean();
	return $fragments;
}
