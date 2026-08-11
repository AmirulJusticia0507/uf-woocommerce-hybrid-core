<?php
/**
 * Quick view: AJAX content + modal + card button.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Quick view button on product cards.
 */
function xiv_quick_view_button( $product ) {
	?>
	<button type="button" class="xiv-quick-view xiv-absolute xiv-inset-x-3 xiv-bottom-3 xiv-z-10 xiv-bg-xiv-black/85 xiv-backdrop-blur xiv-text-white xiv-text-[10px] xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-py-2.5 xiv-px-4 xiv-opacity-0 xiv-transition group-hover:xiv-opacity-100 hover:xiv-bg-xiv-black focus:xiv-opacity-100"
			data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
		<?php echo esc_html( xiv_t( 'QUICK VIEW' ) ); ?>
	</button>
	<?php
}

/**
 * Render quick view modal markup (injected once in wp_footer).
 */
function xiv_quick_view_modal() {
	$close = xiv_t( 'Close quick view' );
	?>
	<div id="xiv-quick-view-modal" class="xiv-hidden xiv-fixed xiv-inset-0 xiv-z-50 xiv-flex xiv-items-center xiv-justify-center xiv-p-4" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( xiv_t( 'Quick view' ) ); ?>">
		<button type="button" class="xiv-absolute xiv-inset-0 xiv-bg-xiv-black/50 xiv-backdrop-blur-sm xiv-w-full xiv-h-full xiv-cursor-default" data-xiv-qv-close aria-hidden="true" tabindex="-1"></button>
		<div class="xiv-qv-panel xiv-relative xiv-w-full xiv-max-w-3xl xiv-bg-xiv-bg xiv-border xiv-border-xiv-gray-light xiv-max-h-[88vh] xiv-overflow-y-auto">
			<button type="button" class="xiv-absolute xiv-top-3 xiv-right-3 xiv-z-10 xiv-p-2 xiv-text-xs xiv-font-mono xiv-uppercase xiv-text-xiv-gray-text hover:xiv-text-xiv-black" data-xiv-qv-close>
				<?php echo esc_html( $close ); ?> &times;
			</button>
			<div class="xiv-qv-content xiv-p-5 md:xiv-p-8"></div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'xiv_quick_view_modal', 45 );

/**
 * AJAX: return quick view content for a product.
 */
function xiv_quick_view_ajax() {
	check_ajax_referer( 'xiv_filter_nonce', 'security' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$product    = $product_id ? wc_get_product( $product_id ) : false;

	if ( ! $product || ! $product->is_visible() ) {
		wp_send_json_error( array( 'message' => xiv_t( 'Invalid product.' ) ) );
	}

	ob_start();
	xiv_quick_view_content( $product );
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_xiv_quick_view', 'xiv_quick_view_ajax' );
add_action( 'wp_ajax_nopriv_xiv_quick_view', 'xiv_quick_view_ajax' );

/**
 * Render quick view content.
 */
function xiv_quick_view_content( $product ) {
	$gallery_ids = $product->get_gallery_image_ids();
	$main_id     = $product->get_image_id();
	$main_url    = $main_id ? wp_get_attachment_image_url( $main_id, 'full' ) : wc_placeholder_img_src( 'full' );
	?>
	<div class="xiv-grid md:xiv-grid-cols-2 xiv-gap-6 md:xiv-gap-8 xiv-items-start">
		<div class="xiv-quick-view-gallery">
			<div class="xiv-aspect-[3/4] xiv-bg-stone-200 xiv-overflow-hidden">
				<img src="<?php echo esc_url( $main_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" class="xiv-qv-main xiv-w-full xiv-h-full xiv-object-cover" loading="lazy" />
			</div>
			<?php if ( $gallery_ids ) : ?>
				<div class="xiv-flex xiv-gap-2 xiv-mt-2 xiv-overflow-x-auto">
					<?php if ( $main_id ) : ?>
						<button type="button" class="xiv-qv-thumb xiv-w-14 xiv-h-[72px] xiv-shrink-0 xiv-bg-stone-200 xiv-overflow-hidden xiv-border xiv-border-xiv-black" data-full="<?php echo esc_url( $main_url ); ?>">
							<img src="<?php echo esc_url( $main_url ); ?>" alt="" class="xiv-w-full xiv-h-full xiv-object-cover" loading="lazy" />
						</button>
					<?php endif; ?>
					<?php foreach ( $gallery_ids as $gid ) : $gurl = wp_get_attachment_image_url( $gid, 'full' ); ?>
						<button type="button" class="xiv-qv-thumb xiv-w-14 xiv-h-[72px] xiv-shrink-0 xiv-bg-stone-200 xiv-overflow-hidden xiv-border xiv-border-xiv-gray-light" data-full="<?php echo esc_url( $gurl ); ?>">
							<img src="<?php echo esc_url( $gurl ); ?>" alt="" class="xiv-w-full xiv-h-full xiv-object-cover" loading="lazy" />
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="xiv-quick-view-summary xiv-text-left">
			<p class="xiv-text-xs xiv-font-mono xiv-text-xiv-gray-text xiv-uppercase xiv-tracking-widest"><?php echo esc_html( $product->get_categories( ', ' ) ?: 'XIV' ); ?></p>
			<h2 class="xiv-font-display xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-text-2xl xiv-mt-1"><?php echo esc_html( $product->get_name() ); ?></h2>

			<?php if ( wc_review_ratings_enabled() && $product->get_review_count() ) : ?>
				<div class="xiv-flex xiv-items-center xiv-gap-2 xiv-mt-2">
					<?php echo wc_get_rating_html( $product->get_average_rating() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="xiv-text-xs xiv-font-mono xiv-text-xiv-gray-text">(<?php echo esc_html( $product->get_review_count() ); ?>)</span>
				</div>
			<?php endif; ?>

			<p class="xiv-text-xl xiv-font-black xiv-mt-3"><?php echo wp_kses_post( xiv_product_price( $product ) ); ?></p>

			<?php if ( $product->get_short_description() ) : ?>
				<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-mt-3"><?php echo esc_html( $product->get_short_description() ); ?></p>
			<?php endif; ?>

			<div class="xiv-mt-5">
				<?php
				if ( $product->is_type( 'variable' ) && function_exists( 'xiv_variable_add_to_cart' ) ) {
					xiv_variable_add_to_cart( $product );
				} elseif ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
					?>
					<div class="xiv-flex xiv-items-center xiv-gap-3">
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="ajax_add_to_cart xiv-flex-1 xiv-bg-xiv-black xiv-text-white xiv-text-sm xiv-font-bold xiv-uppercase xiv-tracking-wide xiv-py-4 xiv-px-6 xiv-transition xiv-flex xiv-items-center xiv-justify-between hover:xiv-bg-xiv-gray-text"
						   data-product_id="<?php echo esc_attr( $product->get_id() ); ?>">
							<span><?php echo esc_html( xiv_t( 'ADD TO BAG' ) ); ?></span>
							<span aria-hidden="true">&rarr;</span>
						</a>
					</div>
					<?php
				} else {
					echo '<p class="xiv-text-sm xiv-font-mono xiv-text-xiv-gray-text xiv-uppercase">' . esc_html( xiv_t( 'UNAVAILABLE' ) ) . '</p>';
				}
				?>
			</div>

			<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="xiv-inline-block xiv-mt-4 xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black">
				<?php echo esc_html( xiv_t( 'VIEW FULL DETAILS' ) ); ?> &rarr;
			</a>
		</div>
	</div>
	<?php
}
