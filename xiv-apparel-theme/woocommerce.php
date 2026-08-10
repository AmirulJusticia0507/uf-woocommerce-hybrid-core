<?php
/**
 * WooCommerce wrapper (forces our canvas + layout on WC pages).
 *
 * @package XIV_Apparel
 */

get_header();
xiv_canvas_open();
?>

<main id="xiv-main" class="xiv-max-w-7xl xiv-mx-auto xiv-px-4 sm:xiv-px-6 xiv-py-8">
	<?php
	woocommerce_content();
	?>
</main>

<?php
xiv_canvas_close();
get_footer();
