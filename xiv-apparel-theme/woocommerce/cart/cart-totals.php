<?php
/**
 * Cart totals (custom editorial summary card).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package XIV_Apparel
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="cart_totals xiv-border xiv-border-xiv-gray-light xiv-bg-white xiv-p-6 <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2 class="xiv-font-display xiv-text-xl xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-m-0 xiv-mb-6"><?php esc_html_e( 'Order Summary', 'woocommerce' ); ?></h2>

	<table cellspacing="0" class="shop_table shop_table_responsive xiv-w-full xiv-text-sm">

		<tr class="cart-subtotal xiv-border-b xiv-border-xiv-gray-light">
			<th class="xiv-py-3 xiv-text-left xiv-font-bold xiv-uppercase xiv-text-xs xiv-tracking-widest xiv-text-xiv-gray-text"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>" class="xiv-py-3 xiv-text-right"><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?> xiv-border-b xiv-border-xiv-gray-light">
				<th class="xiv-py-3 xiv-text-left xiv-font-bold xiv-uppercase xiv-text-xs xiv-tracking-widest xiv-text-xiv-gray-text"><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>" class="xiv-py-3 xiv-text-right"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>

		<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>

			<tr class="shipping xiv-border-b xiv-border-xiv-gray-light">
				<th class="xiv-py-3 xiv-text-left xiv-font-bold xiv-uppercase xiv-text-xs xiv-tracking-widest xiv-text-xiv-gray-text"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></th>
				<td data-title="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>" class="xiv-py-3 xiv-text-right"><?php woocommerce_shipping_calculator(); ?></td>
			</tr>

		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee xiv-border-b xiv-border-xiv-gray-light">
				<th class="xiv-py-3 xiv-text-left xiv-font-bold xiv-uppercase xiv-text-xs xiv-tracking-widest xiv-text-xiv-gray-text"><?php echo esc_html( $fee->name ); ?></th>
				<td data-title="<?php echo esc_attr( $fee->name ); ?>" class="xiv-py-3 xiv-text-right"><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php
		if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = '';

			if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
				/* translators: %s location. */
				$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
			}

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?> xiv-border-b xiv-border-xiv-gray-light">
						<th class="xiv-py-3 xiv-text-left xiv-font-bold xiv-uppercase xiv-text-xs xiv-tracking-widest xiv-text-xiv-gray-text"><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
						<td data-title="<?php echo esc_attr( $tax->label ); ?>" class="xiv-py-3 xiv-text-right"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr class="tax-total xiv-border-b xiv-border-xiv-gray-light">
					<th class="xiv-py-3 xiv-text-left xiv-font-bold xiv-uppercase xiv-text-xs xiv-tracking-widest xiv-text-xiv-gray-text"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
					<td data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>" class="xiv-py-3 xiv-text-right"><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
				<?php
			}
		}
		?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

		<tr class="order-total xiv-border-t-2 xiv-border-xiv-black">
			<th class="xiv-py-4 xiv-text-left xiv-font-black xiv-uppercase xiv-text-xs xiv-tracking-widest"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>" class="xiv-py-4 xiv-text-right xiv-font-display xiv-text-lg xiv-font-extrabold"><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	</table>

	<div class="wc-proceed-to-checkout xiv-mt-6">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>
