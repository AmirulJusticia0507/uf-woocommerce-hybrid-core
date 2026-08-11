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
$is_in_stock = $product->is_in_stock();
$in_wishlist = function_exists( 'xiv_wishlist_get_ids' ) && in_array( $product_id, xiv_wishlist_get_ids(), true );
?>
<li <?php post_class( 'xiv-list-none' ); ?>>
	<div class="xiv-group xiv-flex xiv-flex-col">
		<div class="xiv-relative xiv-aspect-[3/4] xiv-bg-stone-200 xiv-overflow-hidden">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="xiv-block xiv-w-full xiv-h-full" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
				<?php echo $product->get_image( 'xiv-product-grid', array( 'class' => 'xiv-w-full xiv-h-full xiv-object-cover xiv-transition xiv-duration-300 group-hover:xiv-scale-105' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>

			<?php if ( $product->is_on_sale() ) : ?>
				<span class="xiv-absolute xiv-top-3 xiv-left-3 xiv-bg-xiv-black xiv-text-white xiv-text-[10px] xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-px-2 xiv-py-1"><?php echo esc_html( xiv_t( 'SALE' ) ); ?></span>
			<?php endif; ?>

			<?php if ( ! $is_in_stock ) : ?>
				<span class="xiv-absolute xiv-top-3 xiv-left-3 <?php echo $product->is_on_sale() ? 'xiv-top-9' : ''; ?> xiv-bg-xiv-gray-light xiv-text-xiv-gray-text xiv-text-[10px] xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-px-2 xiv-py-1"><?php echo esc_html( xiv_t( 'SOLD OUT' ) ); ?></span>
			<?php endif; ?>

			<?php if ( function_exists( 'xiv_wishlist_get_ids' ) ) : ?>
				<button type="button" class="xiv-wishlist-toggle xiv-absolute xiv-top-3 xiv-right-3 xiv-z-10 xiv-p-2 xiv-bg-white/90 xiv-text-xiv-black xiv-border xiv-border-xiv-gray-light xiv-transition <?php echo $in_wishlist ? 'xiv-is-active' : ''; ?>"
						data-product-id="<?php echo esc_attr( $product_id ); ?>"
						aria-pressed="<?php echo $in_wishlist ? 'true' : 'false'; ?>"
						aria-label="<?php echo esc_attr( xiv_t( 'Toggle wishlist' ) ); ?>">
					<svg class="xiv-w-4 xiv-h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 20.5s-7.5-4.7-7.5-10A4.5 4.5 0 0 1 12 6.4a4.5 4.5 0 0 1 7.5 4.1c0 5.3-7.5 10-7.5 10Z" stroke-linejoin="round"/></svg>
				</button>
			<?php endif; ?>

			<?php if ( function_exists( 'xiv_quick_view_button' ) ) : ?>
				<?php xiv_quick_view_button( $product ); ?>
			<?php endif; ?>

			<?php if ( function_exists( 'xiv_compare_button' ) ) : ?>
				<?php xiv_compare_button( $product ); ?>
			<?php endif; ?>
		</div>

		<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="xiv-mt-3 xiv-flex xiv-justify-between xiv-items-start xiv-gap-2">
			<div class="xiv-min-w-0">
				<p class="xiv-text-xs xiv-text-xiv-gray-text xiv-uppercase xiv-truncate"><?php echo esc_html( $product->get_short_description() ?: get_the_category_list( ', ' ) ); ?></p>
				<h3 class="xiv-text-sm xiv-font-bold xiv-text-xiv-black xiv-m-0 xiv-truncate"><?php echo esc_html( $product->get_name() ); ?></h3>
				<?php if ( wc_review_ratings_enabled() && $product->get_review_count() ) : ?>
					<span class="xiv-inline-flex xiv-items-center xiv-gap-1.5 xiv-mt-1 xiv-text-[10px] xiv-font-mono xiv-text-xiv-gray-text xiv-uppercase">
						<?php echo wc_get_rating_html( $product->get_average_rating() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span>(<?php echo esc_html( $product->get_review_count() ); ?>)</span>
					</span>
				<?php endif; ?>
			</div>
			<span class="xiv-text-sm xiv-font-bold xiv-text-xiv-black xiv-shrink-0"><?php echo wp_kses_post( xiv_product_price( $product ) ); ?></span>
		</a>
	</div>
</li>
