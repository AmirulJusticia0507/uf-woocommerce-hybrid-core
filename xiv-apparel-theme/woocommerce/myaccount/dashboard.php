<?php
/**
 * Dashboard.
 *
 * @package XIV_Apparel
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$orders_count = wc_get_customer_order_count( get_current_user_id() );
$download_count = count( wc_get_customer_available_downloads( get_current_user_id() ) );
$edit_account = wc_get_page_permalink( 'edit-account' );
?>

<div class="xiv-max-w-xl">
	<p class="xiv-text-lg xiv-font-display xiv-font-black xiv-uppercase xiv-tracking-widest xiv-mb-2">
		<?php
		/* translators: %s: user display name. */
		printf( esc_html__( 'Hello %s', 'woocommerce' ), '<strong>' . esc_html( $current_user->display_name ) . '</strong>' );
		?>
	</p>

	<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-mb-6">
		<?php
		/* translators: 1: orders count 2: account page URL. */
		printf( esc_html__( 'From your account dashboard you can view your %1$s, manage your %2$s, and %3$s.', 'woocommerce' ),
			'<a href="' . esc_url( wc_get_endpoint_url( 'orders' ) ) . '" class="xiv-text-xiv-black xiv-font-bold hover:xiv-underline">' . esc_html__( 'recent orders', 'woocommerce' ) . '</a>',
			'<a href="' . esc_url( wc_get_endpoint_url( 'edit-address' ) ) . '" class="xiv-text-xiv-black xiv-font-bold hover:xiv-underline">' . esc_html__( 'shipping and billing addresses', 'woocommerce' ) . '</a>',
			'<a href="' . esc_url( $edit_account ) . '" class="xiv-text-xiv-black xiv-font-bold hover:xiv-underline">' . esc_html__( 'edit your password and account details', 'woocommerce' ) . '</a>'
		);
		?>
	</p>

	<div class="xiv-flex xiv-flex-wrap xiv-gap-3">
		<?php if ( $orders_count > 0 ) : ?>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>" class="xiv-btn">
				<?php echo esc_html( $orders_count ); ?> <?php echo esc_html( _n( 'order', 'orders', $orders_count, 'woocommerce' ) ); ?>
			</a>
		<?php endif; ?>

		<?php if ( $download_count > 0 ) : ?>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'downloads' ) ); ?>" class="xiv-btn xiv-btn--ghost">
				<?php echo esc_html( $download_count ); ?> <?php echo esc_html( _n( 'download', 'downloads', $download_count, 'woocommerce' ) ); ?>
			</a>
		<?php endif; ?>

		<a href="<?php echo esc_url( wc_logout_url() ); ?>" class="xiv-btn xiv-btn--ghost">
			<?php esc_html_e( 'Logout', 'woocommerce' ); ?>
		</a>
	</div>

	<?php
	if ( function_exists( 'xiv_wkn_render_dashboard_section' ) ) {
		xiv_wkn_render_dashboard_section();
	}
	?>
</div>
