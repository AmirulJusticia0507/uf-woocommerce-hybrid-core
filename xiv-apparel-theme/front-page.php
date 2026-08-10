<?php
/**
 * Front page (Home / Collection Hero).
 *
 * @package XIV_Apparel
 */

get_header();
xiv_canvas_open();
?>

<main id="xiv-main" class="xiv-max-w-7xl xiv-mx-auto xiv-px-4 sm:xiv-px-6">
	<!-- Hero -->
	<section class="xiv-py-10 md:xiv-py-16" aria-label="<?php esc_attr_e( 'New collection', 'xiv-apparel' ); ?>">
		<div class="xiv-grid md:xiv-grid-cols-12 xiv-gap-8 xiv-items-end">
			<div class="md:xiv-col-span-5">
				<p class="xiv-text-xs xiv-font-mono xiv-text-xiv-gray-text xiv-uppercase xiv-tracking-widest"><?php esc_html_e( 'XIV COLLECTIONS 23-24', 'xiv-apparel' ); ?></p>
				<h1 class="xiv-font-display xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-leading-[0.9] xiv-text-[13vw] md:xiv-text-7xl xiv-mt-4">
					NEW<br>THIS<br>WEEK
				</h1>
				<a href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>"
				   class="xiv-inline-flex xiv-mt-8 xiv-items-center xiv-gap-4 xiv-bg-xiv-black xiv-text-white xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-py-4 xiv-px-6 xiv-transition hover:xiv-bg-xiv-gray-text">
					<?php esc_html_e( 'SHOP NEW', 'xiv-apparel' ); ?><span aria-hidden="true">&rarr;</span>
				</a>
			</div>

			<div class="md:xiv-col-span-7">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="xiv-aspect-[4/3] xiv-bg-stone-200 xiv-overflow-hidden">
						<?php the_post_thumbnail( 'xiv-hero', array( 'class' => 'xiv-w-full xiv-h-full xiv-object-cover', 'fetchpriority' => 'high' ) ); ?>
					</div>
				<?php else : ?>
					<div class="xiv-aspect-[4/3] xiv-bg-xiv-gray-light xiv-flex xiv-items-center xiv-justify-center xiv-font-display xiv-font-extrabold xiv-text-6xl xiv-uppercase xiv-tracking-tighter xiv-text-stone-300">
						XIV
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- Category Quick Links -->
	<section class="xiv-py-12" aria-label="<?php esc_attr_e( 'Categories', 'xiv-apparel' ); ?>">
		<?php if ( class_exists( 'WooCommerce' ) ) : $xiv_cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 3 ) ); ?>
			<?php if ( ! is_wp_error( $xiv_cats ) && ! empty( $xiv_cats ) ) : ?>
				<div class="xiv-grid sm:xiv-grid-cols-3 xiv-gap-px xiv-bg-xiv-gray-light xiv-border xiv-border-xiv-gray-light">
					<?php foreach ( $xiv_cats as $xiv_cat ) : ?>
						<a href="<?php echo esc_url( get_term_link( $xiv_cat ) ); ?>" class="xiv-group xiv-relative xiv-bg-xiv-bg xiv-aspect-[3/4] xiv-overflow-hidden xiv-flex xiv-items-end">
							<?php
							$thumbnail_id = get_term_meta( $xiv_cat->term_id, 'thumbnail_id', true );
							if ( $thumbnail_id ) :
								?>
								<img src="<?php echo esc_url( wp_get_attachment_image_url( $thumbnail_id, 'xiv-product-grid' ) ); ?>" alt="<?php echo esc_attr( $xiv_cat->name ); ?>" class="xiv-absolute xiv-inset-0 xiv-w-full xiv-h-full xiv-object-cover xiv-transition xiv-duration-300 group-hover:xiv-scale-105" loading="lazy" />
							<?php endif; ?>
							<span class="xiv-relative xiv-z-10 xiv-w-full xiv-bg-xiv-bg/90 xiv-backdrop-blur xiv-px-4 xiv-py-3 xiv-text-sm xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-flex xiv-items-center xiv-justify-between">
								<?php echo esc_html( $xiv_cat->name ); ?><span aria-hidden="true">&rarr;</span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</section>

	<!-- New Arrivals -->
	<?php if ( class_exists( 'WooCommerce' ) ) : $xiv_new = wc_get_products( array( 'limit' => 4, 'orderby' => 'date', 'order' => 'DESC' ) ); ?>
		<?php if ( ! empty( $xiv_new ) ) : ?>
			<section class="xiv-py-12" aria-label="<?php esc_attr_e( 'New arrivals', 'xiv-apparel' ); ?>">
				<div class="xiv-flex xiv-items-baseline xiv-justify-between xiv-mb-8">
					<h2 class="xiv-font-display xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-text-2xl md:xiv-text-4xl"><?php esc_html_e( 'NEW ARRIVALS', 'xiv-apparel' ); ?></h2>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black">
						<?php esc_html_e( 'VIEW ALL', 'xiv-apparel' ); ?>
					</a>
				</div>
				<div class="xiv-grid xiv-grid-cols-2 lg:xiv-grid-cols-4 xiv-gap-x-4 xiv-gap-y-8">
					<?php foreach ( $xiv_new as $xiv_product ) : ?>
						<?php
						global $product;
						$product = $xiv_product;
						wc_get_template_part( 'content', 'product' );
						$product = null;
						?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>
</main>

<?php
xiv_canvas_close();
get_footer();
