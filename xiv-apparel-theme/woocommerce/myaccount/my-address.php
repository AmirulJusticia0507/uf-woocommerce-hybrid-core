<?php
/**
 * Addresses list.
 *
 * @package XIV_Apparel
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters( 'woocommerce_my_account_get_addresses', array(
		'billing'  => __( 'Billing address', 'woocommerce' ),
		'shipping' => __( 'Shipping address', 'woocommerce' ),
	), $customer_id );
} else {
	$get_addresses = apply_filters( 'woocommerce_my_account_get_addresses', array(
		'billing' => __( 'Billing address', 'woocommerce' ),
	), $customer_id );
}
?>

<div class="xiv-myaccount-addresses xiv-max-w-2xl xiv-mt-4">
	<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-mb-6">
		<?php esc_html_e( 'The following addresses will be used on the checkout page by default.', 'woocommerce' ); ?>
	</p>

	<div class="xiv-grid xiv-grid-cols-1 md:xiv-grid-cols-2 xiv-gap-6">
		<?php foreach ( $get_addresses as $name => $address_title ) : ?>
			<div class="woocommerce-Address xiv-border xiv-border-xiv-gray-light xiv-p-6">
				<header class="woocommerce-Address-title title xiv-flex xiv-items-center xiv-justify-between xiv-mb-3">
					<h3 class="xiv-font-display xiv-text-sm xiv-font-black xiv-uppercase xiv-tracking-widest xiv-m-0">
						<?php echo esc_html( $address_title ); ?>
					</h3>
					<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="edit xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black">
						<?php esc_html_e( 'Edit', 'woocommerce' ); ?>
					</a>
				</header>
				<address class="xiv-text-sm xiv-not-italic xiv-text-xiv-gray-text">
					<?php
					$address = wc_get_account_formatted_address( $name );
					echo $address ? wp_kses_post( $address ) : esc_html_e( 'You have not set up this type of address yet.', 'woocommerce' );
					?>
				</address>
			</div>
		<?php endforeach; ?>
	</div>
</div>
