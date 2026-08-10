<?php
/**
 * Edit address form.
 *
 * @package XIV_Apparel
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Billing address', 'woocommerce' ) : esc_html__( 'Shipping address', 'woocommerce' );

do_action( 'woocommerce_before_edit_account_address_form' );
?>

<?php if ( ! $load_address ) : ?>

	<?php wc_get_template( 'myaccount/my-address.php' ); ?>

<?php else : ?>

	<form method="post" class="xiv-max-w-xl xiv-mt-4">
		<h3 class="xiv-font-display xiv-text-lg xiv-font-black xiv-uppercase xiv-tracking-widest xiv-mb-4">
			<?php echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</h3>

		<div class="woocommerce-address-fields">
			<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

			<div class="woocommerce-address-fields__field-wrapper">
				<?php
				foreach ( $address as $key => $field ) {
					woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
				}
				?>
			</div>

			<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

			<p class="xiv-mt-6">
				<button type="submit" class="button xiv-btn" name="save_address"
					value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>">
					<?php esc_html_e( 'Save address', 'woocommerce' ); ?>
				</button>
				<?php wp_nonce_field( 'woocommerce-address', 'woocommerce-address-nonce' ); ?>
				<input type="hidden" name="action" value="edit_address" />
			</p>
		</div>
	</form>

<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
