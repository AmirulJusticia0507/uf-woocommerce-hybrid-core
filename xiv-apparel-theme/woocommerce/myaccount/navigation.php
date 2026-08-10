<?php
/**
 * My Account navigation (styled sidebar).
 *
 * @package XIV_Apparel
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<nav class="woocommerce-MyAccount-navigation xiv-w-full lg:xiv-w-56 xiv-shrink-0" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
	<ul class="xiv-flex xiv-flex-row xiv-flex-wrap lg:xiv-flex-col xiv-gap-1 xiv-list-none xiv-m-0 xiv-p-0">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="xiv-m-0 <?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"
					<?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>
					class="xiv-block xiv-px-3 xiv-py-2 xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text xiv-border-l-2 xiv-border-transparent hover:xiv-text-xiv-black">
					<?php echo esc_html( $label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
