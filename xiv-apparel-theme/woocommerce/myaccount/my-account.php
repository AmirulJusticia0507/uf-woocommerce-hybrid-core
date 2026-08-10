<?php
/**
 * My Account page layout: sidebar nav + content.
 *
 * @package XIV_Apparel
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

wc_print_notices();

do_action( 'woocommerce_before_account_navigation' );
?>

<div class="xiv-myaccount xiv-flex xiv-flex-col lg:xiv-flex-row xiv-gap-10">
	<?php do_action( 'woocommerce_account_navigation' ); ?>

	<div class="woocommerce-MyAccount-content xiv-flex-1 xiv-min-w-0">
		<?php do_action( 'woocommerce_account_content' ); ?>
	</div>
</div>

<?php do_action( 'woocommerce_after_account_content' ); ?>
