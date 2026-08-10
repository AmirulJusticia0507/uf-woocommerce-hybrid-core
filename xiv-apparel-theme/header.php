<?php
/**
 * Theme header.
 *
 * @package XIV_Apparel
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>

<body <?php body_class( 'xiv-bg-xiv-bg xiv-text-xiv-black xiv-font-sans xiv-antialiased' ); ?>>
<?php wp_body_open(); ?>

<a class="xiv-sr-only xiv-focus:not-sr-only xiv-focus:xiv-absolute xiv-focus:xiv-z-50 xiv-focus:xiv-bg-xiv-black xiv-focus:text-white xiv-focus:xiv-px-4 xiv-focus:xiv-py-2 xiv-focus:xiv-uppercase" href="#xiv-main">
	<?php esc_html_e( 'Skip to content', 'xiv-apparel' ); ?>
</a>

<header class="xiv-sticky xiv-top-0 xiv-z-40 xiv-bg-xiv-bg/95 xiv-backdrop-blur xiv-border-b xiv-border-xiv-gray-light">
	<div class="xiv-max-w-7xl xiv-mx-auto xiv-px-4 sm:xiv-px-6 xiv-h-16 xiv-flex xiv-items-center xiv-justify-between xiv-gap-4">

		<button type="button" class="xiv-mobile-nav-toggle xiv-p-2 -xiv-ml-2 xiv-text-xiv-black lg:xiv-hidden" aria-expanded="false" aria-controls="xiv-mobile-menu" aria-label="<?php esc_attr_e( 'Open menu', 'xiv-apparel' ); ?>">
			<svg class="xiv-w-6 xiv-h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="square"/></svg>
		</button>

		<div class="xiv-hidden lg:xiv-flex">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'xiv-flex xiv-items-center xiv-gap-8 xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest',
				'fallback_cb'    => false,
			) );
			?>
		</div>

		<div class="xiv-shrink-0"><?php xiv_site_logo(); ?></div>

		<div class="xiv-flex xiv-items-center xiv-gap-1 sm:xiv-gap-3">
			<button type="button" class="xiv-search-toggle xiv-p-2 xiv-text-xiv-black" aria-expanded="false" aria-label="<?php esc_attr_e( 'Search', 'xiv-apparel' ); ?>">
				<svg class="xiv-w-5 xiv-h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M16.5 16.5L21 21" stroke-linecap="square"/></svg>
			</button>

			<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ?: home_url( '/' ) ); ?>" class="xiv-p-2 xiv-text-xiv-black" aria-label="<?php esc_attr_e( 'My account', 'xiv-apparel' ); ?>">
				<svg class="xiv-w-5 xiv-h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5" stroke-linecap="square"/></svg>
			</a>

			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<button type="button" class="xiv-cart-toggle xiv-relative xiv-p-2 xiv-text-xiv-black" aria-label="<?php esc_attr_e( 'Open shopping bag', 'xiv-apparel' ); ?>">
				<svg class="xiv-w-5 xiv-h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 8h14l-1 13H6L5 8Z" stroke-linejoin="round"/><path d="M8.5 8V6a3.5 3.5 0 0 1 7 0v2" stroke-linecap="square"/></svg>
				<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>
					<span class="xiv-cart-count xiv-absolute -xiv-top-0.5 -xiv-right-0.5 xiv-bg-xiv-blue-accent xiv-text-white xiv-w-4 xiv-h-4 xiv-rounded-full xiv-flex xiv-items-center xiv-justify-center xiv-text-[10px] xiv-font-bold"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
				<?php else : ?>
					<span class="xiv-cart-count xiv-absolute -xiv-top-0.5 -xiv-right-0.5 xiv-bg-xiv-blue-accent xiv-text-white xiv-w-4 xiv-h-4 xiv-rounded-full xiv-flex xiv-items-center xiv-justify-center xiv-text-[10px] xiv-font-bold xiv-hidden">0</span>
				<?php endif; ?>
			</button>
			<?php endif; ?>
		</div>
	</div>

	<div id="xiv-mobile-menu" class="xiv-hidden lg:xiv-hidden xiv-border-t xiv-border-xiv-gray-light xiv-bg-xiv-bg">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'xiv-px-4 xiv-py-4 xiv-space-y-3 xiv-text-sm xiv-font-bold xiv-uppercase xiv-tracking-widest',
			'fallback_cb'    => false,
		) );
		?>
	</div>

	<div id="xiv-search-overlay" class="xiv-hidden xiv-border-t xiv-border-xiv-gray-light xiv-bg-xiv-bg">
		<form role="search" method="get" class="xiv-max-w-7xl xiv-mx-auto xiv-px-4 sm:xiv-px-6 xiv-py-6" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="xiv-sr-only" for="xiv-search-input"><?php esc_html_e( 'Search products', 'xiv-apparel' ); ?></label>
			<div class="xiv-flex xiv-items-center xiv-gap-3 xiv-border-b xiv-border-xiv-black xiv-pb-2">
				<svg class="xiv-w-5 xiv-h-5 xiv-text-xiv-gray-text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M16.5 16.5L21 21" stroke-linecap="square"/></svg>
				<input id="xiv-search-input" type="search" name="s" placeholder="<?php esc_attr_e( 'SEARCH COLLECTION', 'xiv-apparel' ); ?>"
					   class="xiv-flex-1 xiv-bg-transparent xiv-border-0 xiv-text-sm xiv-uppercase xiv-tracking-widest xiv-font-bold placeholder:xiv-text-xiv-gray-text focus:xiv-outline-none" />
				<input type="hidden" name="post_type" value="product" />
				<button type="submit" class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-black"><?php esc_html_e( 'GO', 'xiv-apparel' ); ?></button>
			</div>
		</form>
	</div>
</header>
