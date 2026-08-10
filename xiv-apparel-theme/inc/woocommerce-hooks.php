<?php
/**
 * WooCommerce template override hooks & custom logic.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove default WooCommerce wrappers; we use our own markup.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

add_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );

/**
 * Custom product gallery renderer for single product (PDP).
 */
function xiv_product_gallery() {
	global $product;

	if ( ! $product ) {
		return;
	}

	$main_image_id  = $product->get_image_id();
	$attachment_ids = $product->get_gallery_image_ids();

	echo '<div class="xiv-gallery-wrapper xiv-flex xiv-gap-4">';
	echo '<div class="xiv-main-image xiv-w-3/4 xiv-aspect-[3/4] xiv-bg-stone-200 xiv-overflow-hidden">';
	if ( $main_image_id ) {
		echo wp_get_attachment_image( $main_image_id, 'full', false, array(
			'class' => 'xiv-w-full xiv-h-full xiv-object-cover',
		) );
	}
	echo '</div>';

	if ( ! empty( $attachment_ids ) ) {
		echo '<div class="xiv-thumbnails xiv-w-1/4 xiv-flex xiv-flex-col xiv-gap-2">';
		foreach ( array_slice( $attachment_ids, 0, 6 ) as $attachment_id ) {
			echo '<button type="button" class="xiv-thumb-item xiv-aspect-[3/4] xiv-bg-stone-200 xiv-overflow-hidden xiv-cursor-pointer xiv-p-0 xiv-border-0" data-full="' . esc_attr( wp_get_attachment_image_url( $attachment_id, 'full' ) ) . '">';
			echo wp_get_attachment_image( $attachment_id, 'thumbnail', false, array(
				'class' => 'xiv-w-full xiv-h-full xiv-object-cover',
			) );
			echo '</button>';
		}
		echo '</div>';
	}
	echo '</div>';
}
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
add_action( 'woocommerce_before_single_product_summary', 'xiv_product_gallery', 20 );

/**
 * Move price display into summary, uppercase product title styling.
 */
function xiv_after_title() {
	woocommerce_template_single_price();
}
add_action( 'woocommerce_single_product_summary', 'xiv_after_title', 5 );

/**
 * Replace default add-to-cart for single product with sticky bag drawer trigger.
 * Mendukung simple & variable (ukuran) product.
 */
function xiv_single_add_to_cart() {
	global $product;

	echo '<div class="xiv-sticky-bar xiv-fixed xiv-bottom-0 xiv-inset-x-0 xiv-z-30 xiv-bg-xiv-bg xiv-border-t xiv-border-xiv-gray-light xiv-px-4 xiv-py-3 sm:xiv-static sm:xiv-p-0 sm:xiv-border-0 sm:xiv-bg-transparent">';

	if ( $product->is_type( 'variable' ) ) {
		xiv_variable_add_to_cart( $product );
	} elseif ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
		?>
		<form class="cart xiv-flex xiv-gap-3" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data">
			<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
			<?php
			if ( $product->is_sold_individually() ) {
				echo '<input type="hidden" name="quantity" value="1" min="1" />';
			} else {
				woocommerce_quantity_input( array(
					'min_value'   => 1,
					'max_value'   => $product->get_max_purchase_quantity(),
					'input_value' => 1,
					'input_name'  => 'quantity',
				) );
			}
			?>
			<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"
					class="xiv-add-to-cart single_add_to_cart_button xiv-flex-1 xiv-bg-xiv-black xiv-text-white xiv-text-sm xiv-font-bold xiv-uppercase xiv-tracking-wide xiv-py-4 xiv-px-6 xiv-transition xiv-flex xiv-items-center xiv-justify-between hover:xiv-bg-xiv-gray-text">
				<span><?php esc_html_e( 'ADD TO BAG', 'xiv-apparel' ); ?></span>
				<span aria-hidden="true">&rarr;</span>
			</button>
			<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
		</form>
		<?php
	} else {
		echo '<p class="xiv-text-sm xiv-font-mono xiv-text-xiv-gray-text xiv-uppercase">' . esc_html__( 'UNAVAILABLE', 'xiv-apparel' ) . '</p>';
	}

	echo '</div>';
}
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
add_action( 'woocommerce_single_product_summary', 'xiv_single_add_to_cart', 30 );

/**
 * Add-to-cart untuk variable product: selector ukuran + AJAX.
 */
function xiv_variable_add_to_cart( $product ) {
	$attrs      = $product->get_variation_attributes();
	$sizes      = ! empty( $attrs['pa_size'] ) ? $attrs['pa_size'] : array();
	$variations = $product->get_available_variations();
	?>
	<div class="xiv-variations" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
		<?php if ( ! empty( $sizes ) ) : ?>
			<p class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-2">
				<?php esc_html_e( 'SELECT SIZE', 'xiv-apparel' ); ?>
			</p>
			<div class="xiv-flex xiv-flex-wrap xiv-gap-1.5" role="group" aria-label="<?php esc_attr_e( 'Size', 'xiv-apparel' ); ?>">
				<?php foreach ( $sizes as $size ) : ?>
					<button type="button" class="xiv-size-option xiv-w-9 xiv-h-9 xiv-flex xiv-items-center xiv-justify-center xiv-text-xs xiv-font-bold xiv-border xiv-border-xiv-gray-light xiv-transition hover:xiv-border-xiv-black"
							data-size="<?php echo esc_attr( $size ); ?>"><?php echo esc_html( $size ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="xiv-selected-price xiv-mt-4 xiv-text-lg xiv-font-black xiv-min-h-7" aria-live="polite"></div>

		<button type="button" disabled
				class="xiv-add-bag xiv-add-to-cart xiv-w-full xiv-mt-3 xiv-bg-xiv-black xiv-text-white xiv-text-sm xiv-font-bold xiv-uppercase xiv-tracking-wide xiv-py-4 xiv-px-6 xiv-transition xiv-flex xiv-items-center xiv-justify-between disabled:xiv-opacity-40 disabled:xiv-cursor-not-allowed"
				data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
			<span><?php esc_html_e( 'ADD TO BAG', 'xiv-apparel' ); ?></span>
			<span aria-hidden="true">&rarr;</span>
		</button>

		<script type="application/json" class="xiv-variations-json"><?php echo wp_json_encode( $variations ); // phpcs:ignore WordPress.Security.EscapeOutput ?></script>
	</div>
	<?php
}

/**
 * Category pills on catalog (PLP) top.
 */
function xiv_shop_category_pills() {
	if ( ! is_shop() && ! is_product_taxonomy() ) {
		return;
	}

	$cats = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => 12,
	) );

	if ( is_wp_error( $cats ) || empty( $cats ) ) {
		return;
	}

	$current = get_queried_object();
	$current_id = ( $current && isset( $current->term_id ) ) ? $current->term_id : 0;

	echo '<div class="xiv-flex xiv-flex-wrap xiv-gap-2 xiv-my-4" role="navigation" aria-label="' . esc_attr__( 'Categories', 'xiv-apparel' ) . '">';

	$shop_url = wc_get_page_permalink( 'shop' );
	printf(
		'<a href="%s" class="xiv-px-4 xiv-py-1.5 xiv-text-xs xiv-font-bold xiv-uppercase xiv-border xiv-transition %s">%s</a>',
		esc_url( $shop_url ),
		! $current_id ? 'xiv-border-xiv-black xiv-bg-xiv-black xiv-text-white' : 'xiv-border-xiv-gray-light xiv-bg-transparent hover:xiv-border-xiv-black',
		esc_html__( 'ALL', 'xiv-apparel' )
	);

	foreach ( $cats as $cat ) {
		printf(
			'<a href="%s" class="xiv-px-4 xiv-py-1.5 xiv-text-xs xiv-font-bold xiv-uppercase xiv-border xiv-transition %s">%s</a>',
			esc_url( get_term_link( $cat ) ),
			$current_id === $cat->term_id ? 'xiv-border-xiv-black xiv-bg-xiv-black xiv-text-white' : 'xiv-border-xiv-gray-light xiv-bg-transparent hover:xiv-border-xiv-black',
			esc_html( $cat->name )
		);
	}

	echo '</div>';
}

/**
 * Remove default title wrappers (we render our own header).
 */
add_filter( 'woocommerce_show_page_title', '__return_false' );
