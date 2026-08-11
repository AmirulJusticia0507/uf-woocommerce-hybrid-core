<?php
/**
 * XIV Apparel theme bootstrap.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

define( 'XIV_VERSION', '1.0.0' );

require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/enqueue.php';
require get_template_directory() . '/inc/helpers.php';
require get_template_directory() . '/inc/i18n.php';
require get_template_directory() . '/inc/woocommerce-hooks.php';
require get_template_directory() . '/inc/cart.php';
require get_template_directory() . '/inc/ajax.php';
require get_template_directory() . '/inc/size-guides.php';
require get_template_directory() . '/inc/otp-login.php';
require get_template_directory() . '/inc/webauthn.php';
require get_template_directory() . '/inc/wishlist.php';
require get_template_directory() . '/inc/seo.php';
require get_template_directory() . '/inc/popup.php';
require get_template_directory() . '/inc/theme-options.php';
require get_template_directory() . '/inc/store-locator.php';
require get_template_directory() . '/inc/back-in-stock.php';
require get_template_directory() . '/inc/quick-view.php';
require get_template_directory() . '/inc/recently-viewed.php';
require get_template_directory() . '/inc/compare.php';

if ( is_admin() ) {
	require get_template_directory() . '/inc/admin-crud.php';
	require get_template_directory() . '/inc/admin-extras.php';
}
