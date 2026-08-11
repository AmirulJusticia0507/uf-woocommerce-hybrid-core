<?php
/**
 * Product compare: cookie list, toggle buttons, compare page.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

define( 'XIV_COMPARE_COOKIE', 'xiv_compare' );
define( 'XIV_COMPARE_MAX', 4 );

/**
 * @return int[] Product IDs currently in the compare list.
 */
function xiv_compare_get_ids(): array {
	if ( empty( $_COOKIE[ XIV_COMPARE_COOKIE ] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		return array();
	}
	$raw = sanitize_text_field( wp_unslash( $_COOKIE[ XIV_COMPARE_COOKIE ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}

/**
 * Toggle button on product cards.
 */
function xiv_compare_button( $product ) {
	$id        = $product->get_id();
	$is_active = in_array( $id, xiv_compare_get_ids(), true );
	?>
	<button type="button" class="xiv-compare-toggle xiv-absolute xiv-top-10 xiv-right-3 xiv-z-10 xiv-p-2 xiv-bg-white/90 xiv-text-xiv-black xiv-border xiv-border-xiv-gray-light xiv-transition <?php echo $is_active ? 'xiv-is-active' : ''; ?>"
			data-product-id="<?php echo esc_attr( $id ); ?>"
			aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
			aria-label="<?php echo esc_attr( xiv_t( 'Toggle compare' ) ); ?>"
			title="<?php echo esc_attr( xiv_t( 'Compare' ) ); ?>">
		<svg class="xiv-w-4 xiv-h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 7h10m0 0-3-3m3 3-3 3M20 17H10m0 0 3-3m-3 3 3 3" stroke-linecap="square"/></svg>
	</button>
	<?php
}

/**
 * Create the compare page on theme switch.
 */
function xiv_compare_create_page() {
	if ( get_option( 'xiv_compare_page_id' ) ) {
		return;
	}
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return;
	}

	$page_id = wc_get_page_id( 'shop' );
	$page_id = wp_insert_post( array(
		'post_title'   => 'Compare',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '[xiv_compare]',
	) );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_option( 'xiv_compare_page_id', (int) $page_id );
	}
}
add_action( 'after_switch_theme', 'xiv_compare_create_page', 40 );

/**
 * @return int Number of products in the compare list.
 */
function xiv_compare_count(): int {
	return count( xiv_compare_get_ids() );
}

/**
 * @return string Compare page URL.
 */
function xiv_compare_url() {
	$id = (int) get_option( 'xiv_compare_page_id' );
	return $id ? get_permalink( $id ) : '#';
}

/**
 * Compare shortcode: table of selected products.
 */
function xiv_compare_shortcode() {
	$ids      = xiv_compare_get_ids();
	$products = array();

	if ( ! empty( $ids ) ) {
		$products = wc_get_products( array(
			'include' => $ids,
			'limit'   => XIV_COMPARE_MAX,
			'status'  => 'publish',
			'orderby' => 'post__in',
		) );
	}

	if ( empty( $products ) ) {
		return '<div class="xiv-text-center xiv-py-16"><p class="xiv-font-display xiv-text-2xl xiv-font-extrabold xiv-uppercase xiv-tracking-tighter">' . esc_html( xiv_t( 'NOTHING TO COMPARE YET' ) ) . '</p><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="xiv-inline-block xiv-mt-4 xiv-bg-xiv-black xiv-text-white xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-py-3 xiv-px-6">' . esc_html( xiv_t( 'BROWSE PRODUCTS' ) ) . '</a></div>';
	}

	$rows = xiv_compare_rows();
	ob_start();
	?>
	<div class="xiv-compare xiv-overflow-x-auto">
		<table class="xiv-w-full xiv-border-collapse xiv-min-w-[720px]">
			<thead>
			<tr>
				<th class="xiv-p-3 xiv-text-left xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text xiv-border xiv-border-xiv-gray-light xiv-bg-xiv-bg"></th>
				<?php foreach ( $products as $p ) : ?>
					<th class="xiv-p-3 xiv-border xiv-border-xiv-gray-light xiv-text-left xiv-bg-xiv-bg">
						<button type="button" class="xiv-compare-remove xiv-text-[10px] xiv-font-mono xiv-uppercase xiv-text-xiv-gray-text hover:xiv-text-xiv-black" data-product-id="<?php echo esc_attr( $p->get_id() ); ?>"><?php echo esc_html( xiv_t( 'REMOVE' ) ); ?> &times;</button>
					</th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $rows as $key => $label ) : ?>
				<tr>
					<td class="xiv-p-3 xiv-border xiv-border-xiv-gray-light xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text xiv-align-top"><?php echo esc_html( $label ); ?></td>
					<?php foreach ( $products as $p ) : ?>
						<td data-pid="<?php echo esc_attr( $p->get_id() ); ?>" class="xiv-p-3 xiv-border xiv-border-xiv-gray-light xiv-align-top xiv-text-sm"><?php echo $rows[ $key ]['render']( $p ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'xiv_compare', 'xiv_compare_shortcode' );

/**
 * Compare table rows.
 */
function xiv_compare_rows() {
	return array(
		'image'    => array(
			'label'  => xiv_t( 'PRODUCT' ),
			'render' => function ( $p ) {
				return '<a href="' . esc_url( $p->get_permalink() ) . '" class="xiv-block xiv-aspect-[3/4] xiv-bg-stone-200 xiv-overflow-hidden xiv-w-24">' . $p->get_image( 'woocommerce_gallery_thumbnail', array( 'class' => 'xiv-w-full xiv-h-full xiv-object-cover' ) ) . '</a>';
			},
		),
		'title'    => array(
			'label'  => xiv_t( 'NAME' ),
			'render' => function ( $p ) {
				return '<a href="' . esc_url( $p->get_permalink() ) . '" class="xiv-font-bold xiv-uppercase xiv-tracking-wide hover:xiv-underline">' . esc_html( $p->get_name() ) . '</a>';
			},
		),
		'price'    => array(
			'label'  => xiv_t( 'PRICE' ),
			'render' => function ( $p ) {
				return wp_kses_post( xiv_product_price( $p ) );
			},
		),
		'rating'   => array(
			'label'  => xiv_t( 'RATING' ),
			'render' => function ( $p ) {
				if ( ! $p->get_review_count() ) {
					return '<span class="xiv-text-xs xiv-text-xiv-gray-text xiv-uppercase">' . esc_html( xiv_t( 'NO REVIEWS' ) ) . '</span>';
				}
				return wc_get_rating_html( $p->get_average_rating() ) . ' <span class="xiv-text-xs xiv-text-xiv-gray-text">(' . esc_html( $p->get_review_count() ) . ')</span>';
			},
		),
		'desc'     => array(
			'label'  => xiv_t( 'DETAILS' ),
			'render' => function ( $p ) {
				return '<p class="xiv-text-sm xiv-text-xiv-gray-text">' . esc_html( $p->get_short_description() ) . '</p>';
			},
		),
		'attr'     => array(
			'label'  => xiv_t( 'ATTRIBUTES' ),
			'render' => function ( $p ) {
				$attributes = $p->get_attributes();
				if ( empty( $attributes ) ) {
					return '<span class="xiv-text-xs xiv-text-xiv-gray-text xiv-uppercase">&mdash;</span>';
				}
				$out = '<ul class="xiv-text-xs xiv-space-y-1">';
				foreach ( $attributes as $attr ) {
					$out .= '<li class="xiv-uppercase xiv-text-xiv-gray-text"><strong class="xiv-text-xiv-black">' . esc_html( wc_attribute_label( $attr->get_name() ) ) . ':</strong> ' . esc_html( implode( ', ', $attr->get_options() ) ) . '</li>';
				}
				return $out . '</ul>';
			},
		),
		'stock'    => array(
			'label'  => xiv_t( 'AVAILABILITY' ),
			'render' => function ( $p ) {
				return $p->is_in_stock() ? '<span class="xiv-text-xs xiv-font-bold xiv-uppercase">' . esc_html( xiv_t( 'IN STOCK' ) ) . '</span>' : '<span class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-text-xiv-gray-text">' . esc_html( xiv_t( 'SOLD OUT' ) ) . '</span>';
			},
		),
		'action'   => array(
			'label'  => xiv_t( 'ACTION' ),
			'render' => function ( $p ) {
				return '<a href="' . esc_url( $p->get_permalink() ) . '" class="xiv-inline-block xiv-bg-xiv-black xiv-text-white xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-py-3 xiv-px-4">' . esc_html( xiv_t( 'VIEW' ) ) . '</a>';
			},
		),
	);
}
