<?php
/**
 * Asset enqueueing: fonts, compiled Tailwind CSS, and bundled JS.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

function xiv_register_fonts() {
	wp_enqueue_style(
		'xiv-fonts',
		'https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'xiv_register_fonts', 5 );

function xiv_enqueue_assets() {
	$dist = get_template_directory_uri() . '/assets/dist';
	$path = get_template_directory() . '/assets/dist';

	wp_enqueue_style(
		'xiv-apparel',
		$dist . '/css/app.css',
		array( 'xiv-fonts' ),
		XIV_VERSION . '.' . ( file_exists( $path . '/css/app.css' ) ? (string) filemtime( $path . '/css/app.css' ) : '0' )
	);

	$js_files = array(
		'app'      => '/js/app.js',
		'filters'  => '/js/filters.js',
		'cart'     => '/js/cart.js',
		'checkout' => '/js/checkout.js',
	);

	$handles = array();
	foreach ( $js_files as $handle => $file ) {
		$file_path = $path . $file;
		if ( ! file_exists( $file_path ) ) {
			continue;
		}
		wp_enqueue_script(
			'xiv-' . $handle,
			$dist . $file,
			array(),
			XIV_VERSION . '.' . (string) filemtime( $file_path ),
			true
		);
		$handles[] = 'xiv-' . $handle;
	}

	wp_localize_script( 'xiv-app', 'XIV', array(
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'wcAjaxUrl'   => class_exists( 'WooCommerce' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : '',
		'cartUrl'     => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
		'nonce'       => wp_create_nonce( 'xiv_filter_nonce' ),
		'cartNonce'   => wp_create_nonce( 'xiv_cart_nonce' ),
		'currency'    => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
		'i18n'        => array(
			'addToCart' => __( 'ADD TO BAG', 'xiv-apparel' ),
			'added'     => __( 'ADDED TO BAG', 'xiv-apparel' ),
			'loading'   => __( 'LOADING', 'xiv-apparel' ),
			'error'     => __( 'SOMETHING WENT WRONG', 'xiv-apparel' ),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'xiv_enqueue_assets', 20 );

function xiv_enqueue_checkout_wizard() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}

	$dist = get_template_directory_uri() . '/assets/dist';
	$path = get_template_directory() . '/assets/dist/js/checkout-wizard.js';

	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'xiv-checkout-wizard',
		$dist . '/js/checkout-wizard.js',
		array( 'wc-checkout', 'jquery' ),
		XIV_VERSION . '.' . (string) filemtime( $path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'xiv_enqueue_checkout_wizard', 25 );

function xiv_enqueue_otp_login() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || is_user_logged_in() ) {
		return;
	}

	$dist = get_template_directory_uri() . '/assets/dist';
	$path = get_template_directory() . '/assets/dist/js/otp-login.js';

	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'xiv-otp-login',
		$dist . '/js/otp-login.js',
		array(),
		XIV_VERSION . '.' . (string) filemtime( $path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'xiv_enqueue_otp_login', 26 );

function xiv_inline_woo_overrides() {
	if ( class_exists( 'WooCommerce' ) && function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		wp_add_inline_style( 'xiv-apparel', '
			.woocommerce #content table.cart td.actions .input-text,
			.woocommerce table.cart td.actions .input-text { width: 100%; }
			.woocommerce .product .entry-summary, .woocommerce .product .entry-content { margin-top: 0; }
		' );
	}
}
add_action( 'wp_enqueue_scripts', 'xiv_inline_woo_overrides', 30 );
