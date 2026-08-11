<?php
/**
 * Product catalog (PLP) with sidebar filter + AJAX grid.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

$xiv_page_title = woocommerce_page_title( false );
$xiv_has_filter = taxonomy_exists( 'pa_size' );
?>

<header class="xiv-py-6 md:xiv-py-10">
	<h1 class="xiv-font-display xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-text-4xl md:xiv-text-6xl">
		<?php echo esc_html( $xiv_page_title ); ?>
	</h1>
	<p class="xiv-result-count xiv-text-xs xiv-font-mono xiv-text-xiv-gray-text xiv-uppercase xiv-mt-3">
		<?php
		global $wp_query;
		printf( esc_html( xiv_t( '%s PRODUCTS' ) ), esc_html( number_format_i18n( $wp_query->found_posts ) ) );
		?>
	</p>
</header>

<?php if ( function_exists( 'xiv_shop_category_pills' ) ) : xiv_shop_category_pills(); endif; ?>

<div class="xiv-grid xiv-gap-8 lg:xiv-grid-cols-[240px_1fr]">

	<!-- Sidebar Filter -->
	<aside class="xiv-filter-panel xiv-relative xiv-z-20">
		<button type="button" class="xiv-filter-open lg:xiv-hidden xiv-w-full xiv-bg-xiv-black xiv-text-white xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-py-3 xiv-px-4 xiv-flex xiv-items-center xiv-justify-between xiv-mb-4">
			<span><?php echo esc_html( xiv_t( 'FILTERS' ) ); ?></span><span aria-hidden="true">&rarr;</span>
		</button>

		<form id="xiv-filter-form" class="xiv-filter-body xiv-fixed xiv-inset-0 xiv-z-40 xiv-hidden lg:xiv-static lg:xiv-block lg:xiv-z-auto lg:xiv-bg-transparent" method="get" action="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
			<div class="xiv-h-full xiv-bg-xiv-bg xiv-shadow-xl lg:xiv-shadow-none xiv-p-6 lg:xiv-p-0 xiv-flex xiv-flex-col xiv-overflow-y-auto">
				<div class="lg:xiv-hidden xiv-flex xiv-items-center xiv-justify-between xiv-mb-4">
					<span class="xiv-text-sm xiv-font-black xiv-uppercase"><?php echo esc_html( xiv_t( 'FILTERS' ) ); ?></span>
					<button type="button" class="xiv-filter-close xiv-p-2" aria-label="<?php echo esc_attr( xiv_t( 'Close filters' ) ); ?>">
						<svg class="xiv-w-5 xiv-h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="square"/></svg>
					</button>
				</div>

				<div class="xiv-space-y-6 xiv-text-sm">

					<!-- Availability -->
					<fieldset>
						<legend class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-2"><?php echo esc_html( xiv_t( 'AVAILABILITY' ) ); ?></legend>
						<label class="xiv-flex xiv-items-center xiv-gap-2 xiv-cursor-pointer">
							<input type="radio" name="availability" value="" checked class="xiv-accent-xiv-black">
							<span class="xiv-text-xs xiv-text-xiv-gray-text xiv-uppercase"><?php echo esc_html( xiv_t( 'All' ) ); ?></span>
						</label>
						<label class="xiv-flex xiv-items-center xiv-gap-2 xiv-cursor-pointer">
							<input type="radio" name="availability" value="in_stock" class="xiv-accent-xiv-black">
							<span class="xiv-text-xs xiv-text-xiv-gray-text xiv-uppercase"><?php echo esc_html( xiv_t( 'In Stock' ) ); ?></span>
						</label>
					</fieldset>

					<?php if ( $xiv_has_filter ) : $xiv_sizes = get_terms( array( 'taxonomy' => 'pa_size', 'hide_empty' => true, 'orderby' => 'menu_order' ) ); ?>
						<?php if ( ! is_wp_error( $xiv_sizes ) && ! empty( $xiv_sizes ) ) : ?>
						<!-- Size -->
						<fieldset>
							<legend class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-2"><?php echo esc_html( xiv_t( 'SIZE' ) ); ?></legend>
							<div class="xiv-flex xiv-flex-wrap xiv-gap-1.5">
								<?php foreach ( $xiv_sizes as $xiv_size ) : ?>
									<label class="xiv-cursor-pointer">
										<input type="checkbox" name="sizes[]" value="<?php echo esc_attr( $xiv_size->name ); ?>" class="xiv-peer xiv-sr-only" />
										<span class="xiv-w-9 xiv-h-9 xiv-flex xiv-items-center xiv-justify-center xiv-text-xs xiv-font-bold xiv-border xiv-border-xiv-gray-light xiv-transition peer-checked:xiv-border-xiv-black peer-checked:xiv-bg-xiv-black peer-checked:xiv-text-white"><?php echo esc_html( $xiv_size->name ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</fieldset>
						<?php endif; ?>
					<?php endif; ?>

					<!-- Category -->
					<?php $xiv_cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 20 ) ); ?>
					<?php if ( ! is_wp_error( $xiv_cats ) && ! empty( $xiv_cats ) ) : ?>
						<fieldset>
							<legend class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-2"><?php echo esc_html( xiv_t( 'CATEGORY' ) ); ?></legend>
							<div class="xiv-space-y-2">
								<?php foreach ( $xiv_cats as $xiv_cat ) : ?>
									<label class="xiv-flex xiv-items-center xiv-gap-2 xiv-cursor-pointer">
										<input type="checkbox" name="categories[]" value="<?php echo esc_attr( $xiv_cat->term_id ); ?>" class="xiv-accent-xiv-black" />
										<span class="xiv-text-xs xiv-text-xiv-gray-text xiv-uppercase"><?php echo esc_html( $xiv_cat->name ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</fieldset>
					<?php endif; ?>

					<!-- Price Range -->
					<fieldset>
						<legend class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-2"><?php echo esc_html( xiv_t( 'PRICE' ) ); ?></legend>
						<div class="xiv-grid xiv-grid-cols-2 xiv-gap-2">
							<input type="number" name="min_price" min="0" placeholder="<?php echo esc_attr( xiv_t( 'MIN' ) ); ?>"
								   class="xiv-w-full xiv-p-2 xiv-text-xs xiv-bg-transparent xiv-border xiv-border-xiv-gray-light focus:xiv-border-xiv-black focus:xiv-outline-none" />
							<input type="number" name="max_price" min="0" placeholder="<?php echo esc_attr( xiv_t( 'MAX' ) ); ?>"
								   class="xiv-w-full xiv-p-2 xiv-text-xs xiv-bg-transparent xiv-border xiv-border-xiv-gray-light focus:xiv-border-xiv-black focus:xiv-outline-none" />
						</div>
					</fieldset>

					<div class="lg:xiv-hidden">
						<button type="submit" class="xiv-w-full xiv-bg-xiv-black xiv-text-white xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-py-3">
							<?php echo esc_html( xiv_t( 'APPLY FILTERS' ) ); ?>
						</button>
					</div>
				</div>
			</div>
		</form>
	</aside>

	<!-- Products -->
	<section aria-label="<?php echo esc_attr( xiv_t( 'Products' ) ); ?>">
		<div class="xiv-flex xiv-items-center xiv-justify-between xiv-mb-6">
			<p class="xiv-filter-status xiv-text-xs xiv-font-mono xiv-text-xiv-gray-text xiv-uppercase"></p>
			<select name="orderby" class="xiv-catalog-orderby xiv-bg-transparent xiv-border xiv-border-xiv-gray-light xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-px-3 xiv-py-2 focus:xiv-outline-none focus:xiv-border-xiv-black" aria-label="<?php echo esc_attr( xiv_t( 'Sort products' ) ); ?>">
				<option value="menu_order"><?php echo esc_html( xiv_t( 'DEFAULT' ) ); ?></option>
				<option value="date"><?php echo esc_html( xiv_t( 'NEWEST' ) ); ?></option>
				<option value="price"><?php echo esc_html( xiv_t( 'PRICE: LOW TO HIGH' ) ); ?></option>
				<option value="price-desc"><?php echo esc_html( xiv_t( 'PRICE: HIGH TO LOW' ) ); ?></option>
				<option value="popularity"><?php echo esc_html( xiv_t( 'POPULARITY' ) ); ?></option>
			</select>
		</div>

		<div id="xiv-product-grid" class="xiv-grid xiv-grid-cols-2 xiv-gap-x-4 xiv-gap-y-8 lg:xiv-grid-cols-3">
			<?php
			if ( woocommerce_product_loop() ) {
				while ( have_posts() ) {
					the_post();
					wc_get_template_part( 'content', 'product' );
				}
			} else {
				echo '<p class="xiv-col-span-full xiv-text-center xiv-text-xiv-gray-text xiv-uppercase xiv-font-mono xiv-text-sm xiv-py-20">' . esc_html( xiv_t( 'NO PRODUCTS FOUND' ) ) . '</p>';
			}
			?>
		</div>

		<div class="xiv-pagination xiv-mt-12 xiv-flex xiv-justify-center">
			<?php woocommerce_pagination(); ?>
		</div>

		<?php if ( function_exists( 'xiv_recently_viewed_section' ) ) : ?>
			<?php xiv_recently_viewed_section(); ?>
		<?php endif; ?>
	</section>
</div>
