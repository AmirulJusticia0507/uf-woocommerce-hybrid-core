<?php
/**
 * Product card (content-product).
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$product_id = $product->get_id();
$classes    = implode( ' ', wc_get_product_class( 'xiv-group xiv-flex xiv-flex-col', $product ) );
$is_in_stock = $product->is_in_stock();
?>
<li <?php post_class( 'xiv-list-none' ); ?>>
	<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="xiv-block xiv-group">
		<div class="xiv-relative xiv-aspect-[3/4] xiv-bg-stone-200 xiv-overflow-hidden">
			<?php echo $product->get_image( 'xiv-product-grid', array( 'class' => 'xiv-w-full xiv-h-full xiv-object-cover xiv-transition xiv-duration-300 group-hover:xiv-scale-105' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

			<?php if ( $product->is_on_sale() ) : ?>
				<span class="xiv-absolute xiv-top-3 xiv-left-3 xiv-bg-xiv-black xiv-text-white xiv-text-[10px] xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-px-2 xiv-py-1"><?php esc_html_e( 'SALE', 'xiv-apparel' ); ?></span>
			<?php endif; ?>

			<?php if ( ! $is_in_stock ) : ?>
				<span class="xiv-absolute xiv-top-3 xiv-right-3 xiv-bg-xiv-gray-light xiv-text-xiv-gray-text xiv-text-[10px] xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-px-2 xiv-py-1"><?php esc_html_e( 'SOLD OUT', 'xiv-apparel' ); ?></span>
			<?php endif; ?>
		</div>

		<div class="xiv-mt-3 xiv-flex xiv-justify-between xiv-items-start xiv-gap-2">
			<div class="xiv-min-w-0">
				<p class="xiv-text-xs xiv-text-xiv-gray-text xiv-uppercase xiv-truncate"><?php echo esc_html( $product->get_short_description() ?: get_the_category_list( ', ' ) ); ?></p>
				<h3 class="xiv-text-sm xiv-font-bold xiv-text-xiv-black xiv-m-0 xiv-truncate"><?php echo esc_html( $product->get_name() ); ?></h3>
			</div>
			<span class="xiv-text-sm xiv-font-bold xiv-text-xiv-black xiv-shrink-0"><?php echo wp_kses_post( xiv_product_price( $product ) ); ?></span>
		</div>
	</a>
</li>
