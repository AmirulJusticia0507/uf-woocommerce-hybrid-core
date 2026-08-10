<?php
/**
 * Empty cart page (custom editorial empty state).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package XIV_Apparel
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked wc_empty_cart_message - 10
 */
do_action( 'woocommerce_cart_is_empty' );

if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
	<div class="return-to-shop xiv-flex xiv-flex-col xiv-items-center xiv-text-center xiv-py-24">
		<p class="xiv-font-display xiv-text-2xl xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-mb-3"><?php esc_html_e( 'Your bag is empty', 'xiv-apparel' ); ?></p>
		<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-mb-8 xiv-max-w-md"><?php esc_html_e( 'Looks like you have not added anything yet. Browse the collection and find your next piece.', 'xiv-apparel' ); ?></p>
		<a class="button wc-backward xiv-btn<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php
				/**
				 * Filter "Return To Shop" text.
				 *
				 * @since 4.6.0
				 * @param string $default_text Default text.
				 */
				echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'woocommerce' ) ) );
			?>
		</a>
	</div>
<?php endif; ?>
