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
require get_template_directory() . '/inc/woocommerce-hooks.php';
require get_template_directory() . '/inc/cart.php';
require get_template_directory() . '/inc/ajax.php';
require get_template_directory() . '/inc/size-guides.php';
