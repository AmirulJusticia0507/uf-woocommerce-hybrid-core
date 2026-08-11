<?php
/**
 * Cart drawer + AJAX add-to-cart + fragments.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maximum total items allowed in the cart.
 *
 * @return int
 */
function xiv_cart_max_items() {
	return 100;
}

/**
 * @return string Limit notice (translated).
 */
function xiv_cart_limit_message() {
	/* translators: %d: maximum total items */
	return sprintf( xiv_t( 'Your bag is limited to %d items.' ), xiv_cart_max_items() );
}

/**
 * Block add-to-cart when the cart would exceed the item limit.
 */
add_filter( 'woocommerce_add_to_cart_validation', 'xiv_cart_limit_total', 10, 5 );
function xiv_cart_limit_total( $passed, $product_id, $quantity, $variation_id = 0, $variations = array() ) {
	if ( ! $passed ) {
		return $passed;
	}

	$current = WC()->cart->get_cart_contents_count();
	if ( $current + (int) $quantity > xiv_cart_max_items() ) {
		wc_add_notice( xiv_cart_limit_message(), 'error' );
		return false;
	}

	return $passed;
}

/**
 * Safety net: surface a notice if the cart is somehow over the limit.
 */
add_action( 'woocommerce_check_cart_items', 'xiv_cart_check_limit_notice' );
function xiv_cart_check_limit_notice() {
	if ( WC()->cart->get_cart_contents_count() > xiv_cart_max_items() ) {
		wc_add_notice( xiv_cart_limit_message(), 'error' );
	}
}

/**
 * AJAX add-to-cart endpoint. Returns JSON fragments + cart count.
 */
function xiv_ajax_add_to_cart() {
	check_ajax_referer( 'xiv_cart_nonce', 'security' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$quantity   = absint( $_POST['quantity'] ?? 1 );
	$variation_id = absint( $_POST['variation_id'] ?? 0 );

	if ( ! $product_id || ! function_exists( 'WC' ) ) {
		wp_send_json_error( array( 'message' => xiv_t( 'Invalid product.' ) ) );
	}

	$variation = array();
	if ( ! empty( $_POST['variation'] ) && is_array( $_POST['variation'] ) ) {
		$variation = array_map( 'sanitize_text_field', wp_unslash( $_POST['variation'] ) );
	}

	$added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

	if ( ! $added ) {
		$notices = wc_get_notices( 'error' );
		$message = '';
		if ( ! empty( $notices ) ) {
			$message = implode( ' ', wp_list_pluck( $notices, 'notice' ) );
		}
		wp_send_json_error( array( 'message' => $message ? $message : xiv_cart_limit_message() ) );
	}

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
		$other_total = 0;
		foreach ( WC()->cart->get_cart() as $key => $item ) {
			if ( $key !== $cart_item_key ) {
				$other_total += (int) $item['quantity'];
			}
		}
		if ( $other_total + $quantity > xiv_cart_max_items() ) {
			$quantity = max( 1, xiv_cart_max_items() - $other_total );
		}
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
		echo '<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-uppercase xiv-text-center xiv-py-16">' . esc_html( xiv_t( 'YOUR BAG IS EMPTY' ) ) . '</p>';
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
						   aria-label="<?php echo esc_attr( xiv_t( 'Quantity' ) ); ?>" />
					<button type="button" class="xiv-cart-remove xiv-text-xs xiv-font-mono xiv-uppercase xiv-text-xiv-gray-text hover:xiv-text-xiv-black" aria-label="<?php echo esc_attr( xiv_t( 'Remove item' ) ); ?>">
						<?php echo esc_html( xiv_t( 'Remove' ) ); ?>
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
			<span class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-wide"><?php echo esc_html( xiv_t( 'SUBTOTAL' ) ); ?></span>
			<span class="xiv-cart-total xiv-text-lg xiv-font-black"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>
		</div>
		<p class="xiv-text-xs xiv-text-xiv-gray-text xiv-font-mono xiv-mb-4"><?php echo esc_html( xiv_t( 'MRP incl. of all taxes' ) ); ?></p>
		<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"
		   class="xiv-block xiv-w-full xiv-bg-xiv-black xiv-text-white xiv-text-sm xiv-font-bold xiv-uppercase xiv-tracking-wide xiv-py-4 xiv-px-6 xiv-flex xiv-items-center xiv-justify-between xiv-transition hover:xiv-bg-xiv-gray-text">
			<span><?php echo esc_html( xiv_t( 'CHECKOUT' ) ); ?></span><span aria-hidden="true">&rarr;</span>
		</a>
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="xiv-block xiv-w-full xiv-text-center xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-wide xiv-py-3 xiv-text-xiv-gray-text hover:xiv-text-xiv-black">
			<?php echo esc_html( xiv_t( 'VIEW FULL BAG' ) ); ?>
		</a>
	</div>
	<?php
}

/**
 * "You may also like" upsells inside the bag drawer.
 */
function xiv_cart_upsells() {
	if ( WC()->cart->is_empty() ) {
		return;
	}

	$ids = array();
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$cross = $cart_item['data']->get_cross_sells();
		if ( $cross ) {
			$ids = array_merge( $ids, $cross );
		}
	}
	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

	if ( ! empty( $ids ) ) {
		$products = wc_get_products( array(
			'include' => array_slice( $ids, 0, 4 ),
			'limit'   => 4,
			'status'  => 'publish',
			'orderby' => 'post__in',
		) );
	} else {
		$products = wc_get_products( array(
			'limit'   => 4,
			'status'  => 'publish',
			'orderby' => array( 'meta_value_num' => 'DESC' ),
			'meta_key' => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery
		) );
	}

	if ( empty( $products ) ) {
		return;
	}
	?>
	<div class="xiv-mt-6 xiv-border-t xiv-border-xiv-gray-light xiv-pt-6">
		<h3 class="xiv-text-xs xiv-font-black xiv-uppercase xiv-tracking-widest xiv-mb-4"><?php echo esc_html( xiv_t( 'YOU MAY ALSO LIKE' ) ); ?></h3>
		<div class="xiv-grid xiv-grid-cols-2 xiv-gap-4">
			<?php foreach ( $products as $up ) : ?>
				<div class="xiv-group xiv-flex xiv-flex-col">
					<a href="<?php echo esc_url( $up->get_permalink() ); ?>" class="xiv-aspect-[3/4] xiv-bg-stone-200 xiv-overflow-hidden xiv-block">
						<?php echo $up->get_image( 'thumbnail', array( 'class' => 'xiv-w-full xiv-h-full xiv-object-cover' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</a>
					<p class="xiv-text-[11px] xiv-font-bold xiv-uppercase xiv-tracking-wide xiv-mt-2 xiv-truncate"><?php echo esc_html( $up->get_name() ); ?></p>
					<p class="xiv-text-xs xiv-text-xiv-gray-text xiv-mt-0.5"><?php echo wp_kses_post( $up->get_price_html() ); ?></p>
					<?php if ( $up->is_purchasable() && $up->is_in_stock() && $up->is_type( 'simple' ) ) : ?>
						<a href="<?php echo esc_url( $up->add_to_cart_url() ); ?>" class="ajax_add_to_cart xiv-mt-2 xiv-text-[11px] xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black"
						   data-product_id="<?php echo esc_attr( $up->get_id() ); ?>"><?php echo esc_html( xiv_t( 'QUICK ADD' ) ); ?></a>
					<?php else : ?>
						<a href="<?php echo esc_url( $up->get_permalink() ); ?>" class="xiv-mt-2 xiv-text-[11px] xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black"><?php echo esc_html( xiv_t( 'VIEW' ) ); ?></a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

add_action( 'wp_ajax_xiv_add_to_cart', 'xiv_ajax_add_to_cart' );add_action( 'wp_ajax_nopriv_xiv_add_to_cart', 'xiv_ajax_add_to_cart' );
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
