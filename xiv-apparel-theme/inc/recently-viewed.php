<?php
/**
 * Recently viewed products (cookie-based).
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

define( 'XIV_RECENTLY_COOKIE', 'xiv_recently_viewed' );
define( 'XIV_RECENTLY_MAX', 8 );

/**
 * Track the current product in the cookie (server-side).
 */
function xiv_recently_track() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}

	$id   = get_queried_object_id();
	$list = xiv_recently_get_ids();

	$list = array_diff( $list, array( $id ) );
	array_unshift( $list, $id );
	$list = array_slice( $list, 0, XIV_RECENTLY_MAX );

	setcookie( XIV_RECENTLY_COOKIE, implode( ',', $list ), time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false );
}
add_action( 'wp', 'xiv_recently_track' );

/**
 * @return int[] Product IDs from the cookie.
 */
function xiv_recently_get_ids(): array {
	if ( empty( $_COOKIE[ XIV_RECENTLY_COOKIE ] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		return array();
	}

	$raw = sanitize_text_field( wp_unslash( $_COOKIE[ XIV_RECENTLY_COOKIE ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}

/**
 * Render the recently viewed grid.
 */
function xiv_recently_viewed_section() {
	$ids = xiv_recently_get_ids();
	if ( empty( $ids ) ) {
		return;
	}

	$products = wc_get_products( array(
		'include'     => $ids,
		'limit'       => XIV_RECENTLY_MAX,
		'status'      => 'publish',
		'orderby'     => 'post__in',
	) );

	if ( empty( $products ) ) {
		return;
	}
	?>
	<section class="xiv-mt-20" aria-label="<?php echo esc_attr( xiv_t( 'Recently viewed' ) ); ?>">
		<div class="xiv-flex xiv-items-baseline xiv-justify-between xiv-mb-8">
			<h2 class="xiv-font-display xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-text-2xl md:xiv-text-3xl"><?php echo esc_html( xiv_t( 'RECENTLY VIEWED' ) ); ?></h2>
		</div>
		<div class="xiv-grid xiv-grid-cols-2 xiv-gap-x-4 xiv-gap-y-8 lg:xiv-grid-cols-4">
			<?php
			foreach ( $products as $xv_product ) {
				global $product;
				$product = $xv_product;
				wc_get_template_part( 'content', 'product' );
			}
			$product = null;
			?>
		</div>
	</section>
	<?php
}

/**
 * Shortcode [xiv_recently_viewed].
 */
function xiv_recently_viewed_shortcode() {
	ob_start();
	xiv_recently_viewed_section();
	return ob_get_clean();
}
add_shortcode( 'xiv_recently_viewed', 'xiv_recently_viewed_shortcode' );
