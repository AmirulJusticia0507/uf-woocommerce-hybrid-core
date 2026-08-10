<?php
/**
 * Theme setup, supports, menus, image sizes.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

function xiv_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 48,
		'width'       => 160,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'editor-styles' );

	if ( class_exists( 'WooCommerce' ) ) {
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'xiv-apparel' ),
		'footer'  => __( 'Footer Menu', 'xiv-apparel' ),
	) );

	add_image_size( 'xiv-product-grid', 720, 960, true );
	add_image_size( 'xiv-hero', 1600, 1200, true );
}
add_action( 'after_setup_theme', 'xiv_theme_setup' );

function xiv_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'xiv_content_width', 1440 );
}
add_action( 'after_setup_theme', 'xiv_content_width', 0 );
