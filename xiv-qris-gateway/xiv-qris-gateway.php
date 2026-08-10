<?php
/**
 * Plugin Name:       XIV QRIS Payment Gateway
 * Description:       Pembayaran QRIS statis untuk WooCommerce. Menampilkan kode QR + instruksi bayar di step PAYMENT dan halaman terima kasih. Verifikasi pembayaran dilakukan manual oleh admin.
 * Version:           1.0.0
 * Author:            XIV Apparel
 * Text Domain:       xiv-qris
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * WC requires at least: 8.0
 *
 * @package XIV_QRIS
 */

defined( 'ABSPATH' ) || exit;

define( 'XIV_QRIS_VERSION', '1.0.0' );
define( 'XIV_QRIS_PLUGIN_FILE', __FILE__ );
define( 'XIV_QRIS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'XIV_QRIS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once XIV_QRIS_PLUGIN_DIR . 'includes/class-xiv-qris-gateway.php';

/**
 * Bootstrap setelah semua plugin termuat.
 */
function xiv_qris_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'xiv_qris_missing_woocommerce_notice' );
		return;
	}

	add_filter( 'woocommerce_payment_gateways', 'xiv_qris_register_gateway' );
}
add_action( 'plugins_loaded', 'xiv_qris_init' );

/**
 * Daftarkan gateway ke WooCommerce.
 *
 * @param array $gateways Daftar gateway yang sudah ada.
 * @return array
 */
function xiv_qris_register_gateway( $gateways ) {
	$gateways[] = 'XIV_QRIS_Gateway';
	return $gateways;
}

/**
 * Notifikasi jika WooCommerce tidak aktif.
 */
function xiv_qris_missing_woocommerce_notice() {
	echo '<div class="notice notice-warning"><p>';
	esc_html_e( 'XIV QRIS Payment Gateway membutuhkan plugin WooCommerce yang aktif.', 'xiv-qris' );
	echo '</p></div>';
}

/**
 * Muat asset media library hanya di halaman settings gateway QRIS.
 *
 * @param string $hook Hook suffix layar admin saat ini.
 */
function xiv_qris_admin_assets( $hook ) {
	if ( 'woocommerce_page_wc-settings' !== $hook ) {
		return;
	}

	if ( ! isset( $_GET['page'], $_GET['tab'] ) || 'wc-settings' !== $_GET['page'] || 'checkout' !== $_GET['tab'] ) {
		return;
	}

	$section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';
	if ( '' !== $section && 'xiv_qris' !== $section ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'xiv-qris-admin',
		XIV_QRIS_PLUGIN_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		XIV_QRIS_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'xiv_qris_admin_assets' );
