<?php
/**
 * SEO meta tags + structured data (self-contained, no plugin).
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether an SEO plugin already controls output.
 */
function xiv_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'AIOSEO\Plugin\AIOSEO' ) || class_exists( 'The_SEO_Framework\Load' );
}

/**
 * Resolve meta description for the current view.
 */
function xiv_seo_meta_description() {
	if ( is_front_page() ) {
		return get_bloginfo( 'description' );
	}

	if ( is_singular() ) {
		$post_id = get_queried_object_id();
		$custom  = get_post_meta( $post_id, '_xiv_seo_description', true );
		if ( $custom ) {
			return $custom;
		}
		$post = get_post( $post_id );
		if ( $post ) {
			if ( 'product' === $post->post_type && ! empty( $post->post_excerpt ) ) {
				return wp_strip_all_tags( $post->post_excerpt, true );
			}
			if ( ! empty( $post->post_content ) ) {
				return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28 );
			}
		}
	}

	if ( is_category() || is_tax() || is_tag() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term && ! empty( $term->description ) ) {
			return wp_strip_all_tags( $term->description, true );
		}
	}

	return get_bloginfo( 'description' );
}

/**
 * Meta description + canonical + robots.
 */
function xiv_seo_meta_tags() {
	if ( xiv_seo_plugin_active() ) {
		return;
	}

	$description = xiv_seo_meta_description();
	$canonical   = get_pagenum_link( 1 );
	if ( is_singular() || is_front_page() ) {
		$canonical = get_permalink( get_queried_object_id() );
		if ( is_front_page() ) {
			$canonical = home_url( '/' );
		}
	}

	$noindex = false;
	if ( is_search() || is_404() ) {
		$noindex = true;
	}
	if ( is_paged() ) {
		$noindex = true;
	}
	$filters = array( 'category', 'min_price', 'max_price', 'sizes', 'instock' );
	foreach ( $filters as $key ) {
		if ( isset( $_GET[ $key ] ) && '' !== $_GET[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$noindex = true;
			break;
		}
	}

	echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
	echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
	if ( $noindex ) {
		echo '<meta name="robots" content="noindex, follow" />' . "\n";
	}
}
add_action( 'wp_head', 'xiv_seo_meta_tags', 2 );

/**
 * Open Graph + Twitter card meta.
 */
function xiv_seo_social_meta() {
	if ( xiv_seo_plugin_active() ) {
		return;
	}

	$title       = wp_get_document_title();
	$description = xiv_seo_meta_description();
	$url         = ( is_singular() || is_front_page() ) ? get_permalink( get_queried_object_id() ) : get_pagenum_link( 1 );
	$image       = '';
	$type        = 'website';

	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_singular( 'product' ) ) {
		$type = 'product';
	}

	if ( is_singular() ) {
		$thumb_id = get_post_thumbnail_id();
		if ( $thumb_id ) {
			$image = wp_get_attachment_image_url( $thumb_id, 'full' );
		}
	}

	if ( ! $image ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$image = wp_get_attachment_image_url( $logo_id, 'full' );
		}
	}

	$locale = ( 'id' === xiv_lang() ) ? 'id_ID' : 'en_US';

	echo "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '" />' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";
	echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '" />' . "\n";
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '" />' . "\n";
	}

	echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n";
	if ( $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'xiv_seo_social_meta', 3 );

/**
 * JSON-LD structured data: Organization on front, Product on single product.
 */
function xiv_seo_json_ld() {
	if ( xiv_seo_plugin_active() ) {
		return;
	}

	$name  = get_bloginfo( 'name' );
	$url   = home_url( '/' );
	$logo  = '';
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo = wp_get_attachment_image_url( $logo_id, 'full' );
	}

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => $name,
		'url'      => $url,
	);
	if ( $logo ) {
		$schema['logo'] = $logo;
	}

	if ( is_singular( 'product' ) ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product ) {
			$schema = array(
				'@context'      => 'https://schema.org',
				'@type'         => 'Product',
				'name'          => $product->get_name(),
				'description'   => $product->get_short_description() ? $product->get_short_description() : wp_trim_words( $product->get_description(), 30 ),
				'url'           => $product->get_permalink(),
				'sku'           => $product->get_sku() ? $product->get_sku() : $product->get_id(),
				'image'         => wp_get_attachment_image_url( $product->get_image_id(), 'full' ) ? wp_get_attachment_image_url( $product->get_image_id(), 'full' ) : $logo,
				'brand'         => array( '@type' => 'Brand', 'name' => $name ),
				'offers'        => array(
					'@type'         => 'Offer',
					'price'         => $product->get_price(),
					'priceCurrency' => get_woocommerce_currency(),
					'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
					'url'           => $product->get_permalink(),
				),
			);

			$rating = $product->get_average_rating();
			if ( $rating > 0 ) {
				$schema['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => $rating,
					'ratingCount' => $product->get_rating_count(),
				);
			}
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
add_action( 'wp_head', 'xiv_seo_json_ld', 4 );

/**
 * Open Graph namespace on <html>.
 */
function xiv_seo_language_attributes( $output ) {
	if ( ! xiv_seo_plugin_active() && strpos( $output, 'og: http' ) === false ) {
		$output .= ' xmlns:og="http://ogp.me/ns#" xmlns:fb="http://ogp.me/ns/fb#"';
	}
	return $output;
}
add_filter( 'language_attributes', 'xiv_seo_language_attributes' );
