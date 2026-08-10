<?php
/**
 * Cross-sells (custom editorial grid).
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package XIV_Apparel
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

if ( $cross_sells ) : ?>

	<div class="cross-sells xiv-mt-20 xiv-pt-10 xiv-border-t xiv-border-xiv-gray-light">
		<?php
		$heading = apply_filters( 'woocommerce_product_cross_sells_products_heading', __( 'You may be interested in&hellip;', 'woocommerce' ) );

		if ( $heading ) :
			?>
			<h2 class="xiv-font-display xiv-text-2xl xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-mb-8"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<div class="xiv-grid xiv-grid-cols-2 xiv-gap-x-4 xiv-gap-y-8 lg:xiv-grid-cols-4">
			<?php foreach ( $cross_sells as $cross_sell ) : ?>

				<?php
					$post_object = get_post( $cross_sell->get_id() );

					setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

					wc_get_template_part( 'content', 'product' );
				?>

			<?php endforeach; ?>
		</div>

	</div>
	<?php
endif;

wp_reset_postdata();
