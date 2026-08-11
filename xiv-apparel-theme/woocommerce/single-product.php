<?php
/**
 * Single product page (PDP).
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
xiv_canvas_open();

global $product;
$product = wc_get_product();

if ( ! $product ) {
	echo '<main id="xiv-main" class="xiv-max-w-7xl xiv-mx-auto xiv-px-4 xiv-py-20"><p>' . esc_html( xiv_t( 'Product not found.' ) ) . '</p></main>';
	xiv_canvas_close();
	get_footer( 'shop' );
	return;
}

while ( have_posts() ) :
	the_post();
	?>

	<main id="xiv-main" class="xiv-max-w-7xl xiv-mx-auto xiv-px-4 sm:xiv-px-6 xiv-py-8">
		<?php
		woocommerce_breadcrumb();
		?>

		<div class="xiv-grid lg:xiv-grid-cols-2 xiv-gap-8 xiv-mt-6">

			<div class="xiv-pb-24 lg:xiv-pb-0">
				<?php
				do_action( 'woocommerce_before_single_product_summary' );
				?>
			</div>

			<div class="xiv-entry-summary xiv-relative lg:xiv-sticky lg:xiv-top-6 lg:xiv-self-start">
				<?php
				do_action( 'woocommerce_single_product_summary' );
				?>
				<button type="button" class="xiv-size-guide-trigger xiv-mt-4 xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black xiv-border xiv-border-xiv-gray-light xiv-px-4 xiv-py-2.5 xiv-w-full xiv-flex xiv-items-center xiv-justify-between"
						data-category="T-Shirts">
					<span><?php xiv_e( 'FIND YOUR SIZE' ); ?></span><span aria-hidden="true">&rarr;</span>
				</button>
			</div>
		</div>

		<?php
		do_action( 'woocommerce_after_single_product_summary' );
		?>

		<div class="xiv-mt-16 xiv-py-8 xiv-border-t xiv-border-xiv-gray-light">
			<h2 class="xiv-font-display xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-text-2xl xiv-mb-6"><?php xiv_e( 'YOU MAY ALSO LIKE' ); ?></h2>
			<?php
			$related_ids = wc_get_related_products( $product->get_id(), 4 );
			$related     = wc_get_products( array( 'include' => $related_ids, 'limit' => 4 ) );
			if ( ! empty( $related ) ) :
				?>
				<div class="xiv-grid xiv-grid-cols-2 lg:xiv-grid-cols-4 xiv-gap-x-4 xiv-gap-y-8">
					<?php foreach ( $related as $related_product ) : ?>
						<?php
						$product = $related_product;
						wc_get_template_part( 'content', 'product' );
						?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</main>

	<?php
endwhile;

$product = wc_get_product();
xiv_canvas_close();
?>

<!-- Find Your Size modal -->
<div id="xiv-size-guide-modal" class="xiv-fixed xiv-inset-0 xiv-z-50 xiv-hidden" aria-hidden="true">
	<div class="xiv-size-guide-overlay xiv-absolute xiv-inset-0 xiv-bg-black/40"></div>
	<div class="xiv-relative xiv-max-w-2xl xiv-mx-auto xiv-mt-10 md:xiv-mt-24 xiv-bg-xiv-bg xiv-shadow-2xl xiv-mx-4">
		<header class="xiv-flex xiv-items-center xiv-justify-between xiv-px-6 xiv-py-4 xiv-border-b xiv-border-xiv-gray-light">
			<h3 class="xiv-text-sm xiv-font-black xiv-uppercase xiv-tracking-widest"><?php xiv_e( 'MEASUREMENT GUIDE' ); ?></h3>
			<button type="button" class="xiv-size-guide-close xiv-p-2 xiv-text-xiv-black" aria-label="<?php xiv_et( 'Close' ); ?>">
				<svg class="xiv-w-5 xiv-h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="square"/></svg>
			</button>
		</header>
		<div class="xiv-p-6 xiv-overflow-x-auto">
			<table class="xiv-w-full xiv-text-xs xiv-font-mono xiv-uppercase">
				<thead>
					<tr class="xiv-border-b xiv-border-xiv-black">
						<th class="xiv-text-left xiv-py-2 xiv-pr-4"><?php xiv_e( 'SIZE' ); ?></th>
						<th class="xiv-text-left xiv-py-2 xiv-pr-4"><?php xiv_e( 'CHEST' ); ?></th>
						<th class="xiv-text-left xiv-py-2 xiv-pr-4"><?php xiv_e( 'SHOULDER' ); ?></th>
						<th class="xiv-text-left xiv-py-2 xiv-pr-4"><?php xiv_e( 'WAIST' ); ?></th>
						<th class="xiv-text-left xiv-py-2"><?php xiv_e( 'LENGTH' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td colspan="5" class="xiv-py-8 xiv-text-center xiv-text-xiv-gray-text"><?php xiv_e( 'LOADING…' ); ?></td></tr>
				</tbody>
			</table>
			<p class="xiv-text-[10px] xiv-text-xiv-gray-text xiv-mt-4"><?php xiv_e( 'Measurements in centimetres (cm).' ); ?></p>
		</div>
	</div>
</div>

<?php
get_footer( 'shop' );
