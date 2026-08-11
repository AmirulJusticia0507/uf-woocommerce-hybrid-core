<?php
/**
 * Theme footer + cart drawer.
 *
 * @package XIV_Apparel
 */

$xiv_footer_menu = has_nav_menu( 'footer' );
?>

<footer class="xiv-border-t xiv-border-xiv-gray-light xiv-mt-20">
	<div class="xiv-max-w-7xl xiv-mx-auto xiv-px-4 sm:xiv-px-6 xiv-py-14">
		<div class="xiv-grid xiv-gap-10 md:xiv-grid-cols-3">

			<div>
				<h2 class="xiv-text-lg xiv-font-black xiv-uppercase xiv-tracking-tighter"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
				<p class="xiv-text-xs xiv-text-xiv-gray-text xiv-font-mono xiv-mt-2 xiv-uppercase"><?php echo esc_html( xiv_t( 'XIV COLLECTIONS 23-24' ) ); ?></p>
				<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-mt-4 xiv-max-w-xs"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
				<?php xiv_social_links(); ?>
				<?php $xiv_wa = xiv_support_whatsapp_url(); if ( $xiv_wa ) : ?>
					<a href="<?php echo esc_url( $xiv_wa ); ?>" target="_blank" rel="noopener noreferrer" class="xiv-inline-flex xiv-items-center xiv-gap-2 xiv-mt-5 xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-black xiv-border xiv-border-xiv-black xiv-px-4 xiv-py-2.5 xiv-transition hover:xiv-bg-xiv-black hover:xiv-text-white">
						<?php xiv_e( 'CHAT US' ); ?><span aria-hidden="true">&rarr;</span>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $xiv_footer_menu ) : ?>
			<nav aria-label="<?php xiv_et( 'Footer' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'xiv-space-y-2 xiv-text-sm xiv-uppercase xiv-tracking-wide',
					'fallback_cb'    => false,
				) );
				?>
			</nav>
			<?php endif; ?>

			<div>
				<h3 class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-3"><?php xiv_e( 'NEWSLETTER' ); ?></h3>
				<form id="xiv-newsletter-form" class="xiv-flex xiv-items-center xiv-gap-3 xiv-border-b xiv-border-xiv-black xiv-pb-2">
					<label class="xiv-sr-only" for="xiv-newsletter-email"><?php xiv_e( 'Email address' ); ?></label>
					<input id="xiv-newsletter-email" type="email" required placeholder="<?php xiv_et( 'YOUR EMAIL' ); ?>"
						   class="xiv-flex-1 xiv-bg-transparent xiv-border-0 xiv-text-sm xiv-uppercase xiv-tracking-widest placeholder:xiv-text-xiv-gray-text focus:xiv-outline-none" />
					<button type="submit" class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest"><?php xiv_e( 'SUBSCRIBE' ); ?></button>
				</form>
			</div>
		</div>

		<div class="xiv-mt-14 xiv-pt-6 xiv-border-t xiv-border-xiv-gray-light xiv-flex xiv-flex-col sm:xiv-flex-row xiv-items-center xiv-justify-between xiv-gap-2 xiv-text-xs xiv-text-xiv-gray-text xiv-uppercase xiv-font-mono">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			<span><?php xiv_e( 'MRP incl. of all taxes' ); ?></span>
		</div>
	</div>
</footer>

<?php if ( class_exists( 'WooCommerce' ) ) : ?>
<!-- Shopping Bag Drawer -->
<div id="xiv-cart-drawer" class="xiv-fixed xiv-inset-0 xiv-z-50 xiv-pointer-events-none" aria-hidden="true">
	<div class="xiv-cart-overlay xiv-absolute xiv-inset-0 xiv-bg-black/40 xiv-opacity-0 xiv-transition-opacity"></div>
	<aside role="dialog" aria-modal="true" aria-label="<?php xiv_et( 'Shopping bag' ); ?>"
		   class="xiv-cart-panel xiv-absolute xiv-top-0 xiv-right-0 xiv-h-full xiv-w-full xiv-max-w-md xiv-bg-xiv-bg xiv-shadow-2xl xiv-flex xiv-flex-col xiv-translate-x-full xiv-transition-transform">
		<header class="xiv-flex xiv-items-center xiv-justify-between xiv-px-6 xiv-py-4 xiv-border-b xiv-border-xiv-gray-light">
			<h2 class="xiv-text-sm xiv-font-black xiv-uppercase xiv-tracking-widest"><?php xiv_e( 'SHOPPING BAG' ); ?></h2>
			<button type="button" class="xiv-cart-close xiv-p-2 xiv-text-xiv-black" aria-label="<?php xiv_et( 'Close bag' ); ?>">
				<svg class="xiv-w-5 xiv-h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="square"/></svg>
			</button>
		</header>

		<div class="xiv-flex-1 xiv-overflow-y-auto xiv-px-6 xiv-py-4">
			<div id="xiv-cart-items">
				<?php xiv_cart_drawer_items(); ?>
			</div>

			<?php if ( function_exists( 'xiv_cart_upsells' ) ) : ?>
				<div id="xiv-cart-upsells">
					<?php xiv_cart_upsells(); ?>
				</div>
			<?php endif; ?>
		</div>

		<footer class="xiv-cart-footer xiv-border-t xiv-border-xiv-gray-light xiv-px-6 xiv-py-4" id="xiv-cart-footer">
			<?php xiv_cart_drawer_footer(); ?>
		</footer>
	</aside>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
