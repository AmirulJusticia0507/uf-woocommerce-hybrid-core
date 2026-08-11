<?php
/**
 * AJAX handlers: catalog filtering, "find your size", newsletter.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

/**
 * AJAX live search endpoint.
 *
 * Accepts: term. Returns up to 6 matching products.
 */
function xiv_ajax_live_search() {
	check_ajax_referer( 'xiv_filter_nonce', 'security' );

	$term = sanitize_text_field( wp_unslash( $_POST['term'] ?? '' ) );
	$term = trim( $term );

	if ( '' === $term || mb_strlen( $term ) < 2 ) {
		wp_send_json_success( array( 'results' => array(), 'count' => 0 ) );
	}

	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => 6,
		's'                   => $term,
		'orderby'             => 'relevance',
		'tax_query'           => array(
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'exclude-from-search',
				'operator' => 'NOT IN',
			),
		),
		'wc_query'            => 'search',
	);

	$query = new WP_Query( $args );
	$out   = array();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$product = wc_get_product( $query->post );
			if ( ! $product ) {
				continue;
			}
			$out[] = array(
				'id'     => $product->get_id(),
				'title'  => $product->get_name(),
				'url'    => $product->get_permalink(),
				'image'  => wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_gallery_thumbnail' ) ? wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_gallery_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ),
				'price'  => wp_strip_all_tags( $product->get_price_html() ),
				'stock'  => $product->is_in_stock() ? 'in' : 'out',
			);
		}
		wp_reset_postdata();
	}

	$total = (int) $query->found_posts;
	if ( ! $total && count( $out ) >= 6 ) {
		$total = 6;
	}

	wp_send_json_success( array( 'results' => $out, 'count' => $total ) );
}
add_action( 'wp_ajax_xiv_live_search', 'xiv_ajax_live_search' );
add_action( 'wp_ajax_nopriv_xiv_live_search', 'xiv_ajax_live_search' );

/**
 * AJAX product filter endpoint (PLP).
 *
 * Accepts: sizes[], categories[], availability, min_price, max_price, orderby, search.
 */
function xiv_ajax_filter_products() {
	check_ajax_referer( 'xiv_filter_nonce', 'nonce' );

	$sizes      = isset( $_POST['sizes'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['sizes'] ) ) : array();
	$categories = isset( $_POST['categories'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['categories'] ) ) : array();
	$availability = sanitize_text_field( $_POST['availability'] ?? '' );
	$min_price  = isset( $_POST['min_price'] ) && '' !== $_POST['min_price'] ? (float) $_POST['min_price'] : null;
	$max_price  = isset( $_POST['max_price'] ) && '' !== $_POST['max_price'] ? (float) $_POST['max_price'] : null;
	$orderby    = sanitize_text_field( $_POST['orderby'] ?? 'menu_order' );
	$search     = sanitize_text_field( $_POST['search'] ?? '' );
	$paged      = max( 1, absint( $_POST['paged'] ?? 1 ) );

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => apply_filters( 'xiv_products_per_page', 12 ),
		'paged'          => $paged,
		'no_found_rows'  => false,
	);

	if ( $search ) {
		$args['s'] = $search;
	}

	$tax_query = array();

	if ( ! empty( $categories ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $categories,
		);
	}

	if ( ! empty( $sizes ) ) {
		$tax_query[] = array(
			'taxonomy' => 'pa_size',
			'field'    => 'name',
			'terms'    => $sizes,
			'operator' => 'AND',
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}

	if ( null !== $min_price || null !== $max_price ) {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => '_price',
				'value'   => array( $min_price ?? 0, $max_price ?? PHP_INT_MAX ),
				'type'    => 'NUMERIC',
				'compare' => 'BETWEEN',
			),
		);
	}

	if ( 'in_stock' === $availability ) {
		$args['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'key'     => '_stock_status',
			'value'   => 'instock',
			'compare' => '=',
		);
	}

	switch ( $orderby ) {
		case 'price':
			$args['orderby'] = 'meta_value_num';
			$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['order']    = 'ASC';
			break;
		case 'price-desc':
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['order']    = 'DESC';
			break;
		case 'rating':
			$args['orderby'] = 'meta_value_num';
			$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['order']    = 'DESC';
			break;
		case 'date':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'popularity':
			$args['orderby'] = 'meta_value_num';
			$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['order']    = 'DESC';
			break;
		default:
			$args['orderby'] = 'menu_order';
			$args['order']   = 'ASC';
			break;
	}

	$query = new WP_Query( $args );

	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}
		wp_reset_postdata();
	} else {
		echo '<p class="xiv-col-span-full xiv-text-center xiv-text-xiv-gray-text xiv-uppercase xiv-font-mono xiv-text-sm xiv-py-20">' . esc_html( xiv_t( 'NO PRODUCTS FOUND' ) ) . '</p>';
	}
	$grid_html = ob_get_clean();

	$pagination = '';
	if ( $query->max_num_pages > 1 ) {
		$pagination = paginate_links( array(
			'base'      => '%_%',
			'format'    => '?paged=%#%',
			'current'   => $paged,
			'total'     => $query->max_num_pages,
			'type'      => 'list',
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
		) );
	}

	wp_send_json_success( array(
		'grid'       => $grid_html,
		'pagination' => $pagination,
		'found'      => $query->found_posts,
		'perPage'    => (int) $args['posts_per_page'],
	) );
}
add_action( 'wp_ajax_xiv_filter_products', 'xiv_ajax_filter_products' );
add_action( 'wp_ajax_nopriv_xiv_filter_products', 'xiv_ajax_filter_products' );

/**
 * "Find your size" modal data from wp_xiv_size_guides table.
 */
function xiv_ajax_size_guide() {
	check_ajax_referer( 'xiv_filter_nonce', 'nonce' );

	$category = sanitize_text_field( $_POST['category'] ?? '' );
	if ( ! $category ) {
		wp_send_json_error( array( 'message' => xiv_t( 'Category missing.' ) ) );
	}

	$guides = xiv_get_category_size_guide( $category );
	wp_send_json_success( array(
		'guides' => $guides ?: array(),
	) );
}
add_action( 'wp_ajax_xiv_size_guide', 'xiv_ajax_size_guide' );
add_action( 'wp_ajax_nopriv_xiv_size_guide', 'xiv_ajax_size_guide' );

/**
 * Newsletter subscription endpoint (demo: stores to option; swap for your ESP).
 */
function xiv_ajax_newsletter() {
	check_ajax_referer( 'xiv_filter_nonce', 'security' );

	$email = sanitize_email( $_POST['email'] ?? '' );
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => xiv_t( 'Invalid email.' ) ) );
	}

	$subs   = (array) get_option( 'xiv_newsletter_subscribers', array() );
	$subs[] = array(
		'email'     => $email,
		'time'      => current_time( 'mysql' ),
		'ip'        => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
	);
	update_option( 'xiv_newsletter_subscribers', array_values( array_unique( $subs, SORT_REGULAR ) ) );

	wp_send_json_success( array( 'message' => xiv_t( 'Subscribed.' ) ) );
}
add_action( 'wp_ajax_xiv_newsletter', 'xiv_ajax_newsletter' );
add_action( 'wp_ajax_nopriv_xiv_newsletter', 'xiv_ajax_newsletter' );
