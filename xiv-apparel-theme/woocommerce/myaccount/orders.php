<?php
/**
 * Order list.
 *
 * @package XIV_Apparel
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
?>

<?php if ( $has_orders ) : ?>

	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table xiv-w-full xiv-border-collapse">
		<thead>
			<tr>
				<th class="xiv-text-left xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-pb-3 xiv-border-b xiv-border-xiv-gray-light"><?php esc_html_e( 'Order', 'woocommerce' ); ?></th>
				<th class="xiv-text-left xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-pb-3 xiv-border-b xiv-border-xiv-gray-light"><?php esc_html_e( 'Date', 'woocommerce' ); ?></th>
				<th class="xiv-text-left xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-pb-3 xiv-border-b xiv-border-xiv-gray-light"><?php esc_html_e( 'Status', 'woocommerce' ); ?></th>
				<th class="xiv-text-left xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-pb-3 xiv-border-b xiv-border-xiv-gray-light"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
				<th class="xiv-text-left xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-pb-3 xiv-border-b xiv-border-xiv-gray-light"><?php esc_html_e( 'Actions', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $customer_orders->orders as $customer_order ) :
				$order      = wc_get_order( $customer_order );
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				?>
				<tr class="woocommerce-orders-table__row order">
					<td class="xiv-py-3 xiv-border-b xiv-border-xiv-gray-light xiv-text-sm" data-title="<?php esc_attr_e( 'Order', 'woocommerce' ); ?>">
						#<?php echo esc_html( $order->get_order_number() ); ?>
					</td>
					<td class="xiv-py-3 xiv-border-b xiv-border-xiv-gray-light xiv-text-sm" data-title="<?php esc_attr_e( 'Date', 'woocommerce' ); ?>">
						<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
					</td>
					<td class="xiv-py-3 xiv-border-b xiv-border-xiv-gray-light xiv-text-sm" data-title="<?php esc_attr_e( 'Status', 'woocommerce' ); ?>">
						<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
					</td>
					<td class="xiv-py-3 xiv-border-b xiv-border-xiv-gray-light xiv-text-sm" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
						<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
						<span class="xiv-text-xs xiv-text-xiv-gray-text"><?php echo esc_html( sprintf( _n( 'for %s item', 'for %s items', $item_count, 'woocommerce' ), $item_count ) ); ?></span>
					</td>
					<td class="xiv-py-3 xiv-border-b xiv-border-xiv-gray-light xiv-text-sm" data-title="<?php esc_attr_e( 'Actions', 'woocommerce' ); ?>">
						<?php
						foreach ( $order->get_actions() as $key => $action ) :
							?>
							<a href="<?php echo esc_url( $action['url'] ); ?>" class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-black hover:xiv-underline">
								<?php echo esc_html( $action['name'] ); ?>
							</a>
						<?php endforeach; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination xiv-flex xiv-gap-4 xiv-mt-6">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="xiv-btn xiv-btn--ghost" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
			<?php endif; ?>
			<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
				<a class="xiv-btn xiv-btn--ghost" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>
	<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info xiv-max-w-lg xiv-mt-4">
		<a class="xiv-btn" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Browse products', 'woocommerce' ); ?>
		</a>
		<p class="xiv-text-sm xiv-mt-3 xiv-mb-0"><?php esc_html_e( 'No order has been made yet.', 'woocommerce' ); ?></p>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
