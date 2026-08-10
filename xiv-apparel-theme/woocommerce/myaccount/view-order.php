<?php
/**
 * Single order view.
 *
 * @package XIV_Apparel
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$notes = $order->get_customer_order_notes();
?>

<div class="xiv-max-w-2xl">
	<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-mb-4">
		<?php
		/* translators: %s: order number. */
		printf( esc_html__( 'Order #%s details:', 'woocommerce' ), '<strong class="xiv-text-xiv-black">' . esc_html( $order->get_order_number() ) . '</strong>' );
		?>
	</p>

	<?php wc_get_template( 'order/order-details.php', array( 'order' => $order ) ); ?>

	<?php if ( $notes ) : ?>
		<h2 class="xiv-font-display xiv-text-lg xiv-font-black xiv-uppercase xiv-tracking-widest xiv-mt-8 xiv-mb-4">
			<?php esc_html_e( 'Order updates', 'woocommerce' ); ?>
		</h2>
		<ol class="woocommerce-OrderUpdates commentlist notes xiv-list-none xiv-m-0 xiv-p-0 xiv-mb-4">
			<?php foreach ( $notes as $note ) : ?>
				<li class="woocommerce-OrderUpdate comment note xiv-border xiv-border-xiv-gray-light xiv-p-4 xiv-mb-3 xiv-text-sm">
					<div class="woocommerce-OrderUpdate-inner comment_container">
						<div class="woocommerce-OrderUpdate-text comment-text">
							<p class="woocommerce-OrderUpdate-meta meta xiv-text-xs xiv-text-xiv-gray-text xiv-mb-1">
								<?php echo esc_html( date_i18n( __( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ) ); ?>
							</p>
							<div class="woocommerce-OrderUpdate-description description"><?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?></div>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>

	<?php do_action( 'woocommerce_view_order', $order_id ); ?>
</div>
