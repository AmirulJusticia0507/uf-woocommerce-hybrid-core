<?php
/**
 * Main template / fallback.
 *
 * @package XIV_Apparel
 */

get_header();
xiv_canvas_open();
?>

<main id="xiv-main" class="xiv-max-w-7xl xiv-mx-auto xiv-px-4 sm:xiv-px-6 xiv-py-10">

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<article <?php post_class( 'xiv-max-w-2xl xiv-mx-auto' ); ?>>
			<h1 class="xiv-font-display xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-text-4xl xiv-mb-6"><?php the_title(); ?></h1>
			<div class="xiv-prose xiv-text-sm xiv-leading-relaxed">
				<?php the_content(); ?>
			</div>
		</article>

	<?php endwhile; else : ?>
		<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-uppercase"><?php echo esc_html( xiv_t( 'Nothing found.' ) ); ?></p>
	<?php endif; ?>

</main>

<?php
xiv_canvas_close();
get_footer();
