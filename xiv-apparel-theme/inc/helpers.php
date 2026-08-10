<?php
/**
 * Shared helper functions used across templates.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the custom brand SVG logo.
 */
function xiv_site_logo() {
	$logo = get_custom_logo();
	if ( $logo ) {
		echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}
	echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="xiv-flex xiv-items-center xiv-gap-2 xiv-text-xl xiv-font-black xiv-tracking-tighter xiv-text-xiv-black" rel="home">';
	echo xiv_diamond_svg( 'xiv-w-6 xiv-h-6' );
	echo '<span>XIV</span>';
	echo '</a>';
}

/**
 * Minimalist diamond logo SVG.
 */
function xiv_diamond_svg( $class = '' ) {
	return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M12 2L22 12L12 22L2 12L12 2Z" stroke-linejoin="round"/><path d="M12 8L16 12L12 16L8 12L12 8Z" fill="currentColor" stroke="none"/></svg>';
}

/**
 * Product card price, formatted via WooCommerce.
 */
function xiv_product_price( $product ) {
	if ( ! $product ) {
		return '';
	}
	if ( $product->get_price_html() ) {
		return wp_kses_post( $product->get_price_html() );
	}
	return '';
}

/**
 * Helper: does the current page have a shopping bag drawer target.
 */
function xiv_drawer_targets() {
	return class_exists( 'WooCommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_front_page() || is_home() );
}

/**
 * Grain texture overlay wrapper.
 */
function xiv_canvas_open( $class = '' ) {
	echo '<div class="xiv-canvas xiv-min-h-screen xiv-bg-xiv-bg xiv-text-xiv-black xiv-font-sans ' . esc_attr( $class ) . '">';
}

function xiv_canvas_close() {
	echo '</div>';
}
