<?php
/**
 * Cart page (custom editorial layout).
 *
 * Two-column layout: line items left, sticky order summary right.
 * All WooCommerce hooks preserved for compatibility.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package XIV_Apparel
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<div class="xiv-grid xiv-gap-12 lg:xiv-grid-cols-[1fr_22rem] lg:xiv-items-start">

		<section class="xiv-cart-items" aria-label="<?php esc_attr_e( 'Cart items', 'woocommerce' ); ?>">
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
				$visible    = apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key );

				if ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible ) {
					$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail', array( 'class' => 'xiv-h-full xiv-w-full xiv-object-cover' ) ), $cart_item, $cart_item_key );
					?>
					<div class="woocommerce-cart-form__cart-item xiv-flex xiv-gap-5 xiv-py-6 xiv-border-b xiv-border-xiv-gray-light <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<div class="product-thumbnail xiv-w-24 xiv-shrink-0 xiv-aspect-[3/4] xiv-overflow-hidden xiv-bg-white">
							<?php if ( ! $product_permalink ) : ?>
								<?php echo $thumbnail; // PHPCS: XSS ok. ?>
							<?php else : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo $thumbnail; // PHPCS: XSS ok. ?></a>
							<?php endif; ?>
						</div>

						<div class="product-name xiv-flex-1 xiv-min-w-0" role="rowheader" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
							<div class="xiv-flex xiv-items-start xiv-justify-between xiv-gap-4">
								<h3 class="xiv-font-display xiv-text-sm xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-m-0">
									<?php if ( ! $product_permalink ) : ?>
										<?php echo wp_kses_post( $product_name . '&nbsp;' ); ?>
									<?php else : ?>
										<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) ); ?>
									<?php endif; ?>
								</h3>

								<a role="button" href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>" class="remove xiv-shrink-0 xiv-text-lg xiv-leading-none xiv-text-xiv-gray-text hover:xiv-text-xiv-black" aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ); ?>" data-product_id="<?php echo esc_attr( $product_id ); ?>" data-product_sku="<?php echo esc_attr( $_product->get_sku() ); ?>">&times;</a>
							</div>

							<?php
							do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

							// Item meta data.
							echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

							// Backorder notification.
							if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification xiv-text-xs xiv-text-xiv-gray-text xiv-mt-2">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
							}
							?>

							<div class="xiv-mt-4 xiv-flex xiv-items-center xiv-justify-between xiv-gap-6">
								<div class="xiv-flex xiv-items-center xiv-gap-4">
									<span class="product-price xiv-text-xs xiv-font-mono xiv-text-xiv-gray-text xiv-uppercase">
										<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok. ?>
									</span>

									<div class="product-quantity xiv-flex xiv-items-center xiv-gap-3" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
										<?php if ( $_product->is_sold_individually() ) : ?>
											<?php $min_quantity = 1; $max_quantity = 1; ?>
										<?php else : ?>
											<?php $min_quantity = 0; $max_quantity = $_product->get_max_purchase_quantity(); ?>
										<?php endif; ?>

										<?php
										$product_quantity = woocommerce_quantity_input(
											array(
												'input_name'   => "cart[{$cart_item_key}][qty]",
												'input_value'  => $cart_item['quantity'],
												'max_value'    => $max_quantity,
												'min_value'    => $min_quantity,
												'product_name' => $product_name,
											),
											$_product,
											false
										);

										echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
										?>
									</div>
								</div>

								<span class="product-subtotal xiv-text-sm xiv-font-bold xiv-whitespace-nowrap" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
									<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok. ?>
								</span>
							</div>
						</div>
					</div>
					<?php
				}
			}
			?>

			<?php do_action( 'woocommerce_cart_contents' ); ?>

			<div class="actions xiv-flex xiv-flex-wrap xiv-items-center xiv-gap-4 xiv-py-6">
				<?php if ( wc_coupons_enabled() ) : ?>
					<div class="coupon xiv-flex xiv-gap-2">
						<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
						<input type="text" name="coupon_code" class="input-text xiv-input xiv-w-40" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
						<button type="submit" class="button xiv-btn xiv-btn--ghost<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
						<?php do_action( 'woocommerce_cart_coupon' ); ?>
					</div>
				<?php endif; ?>

				<button type="submit" class="button xiv-btn xiv-btn--ghost<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

				<?php do_action( 'woocommerce_cart_actions' ); ?>

				<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
			</div>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		</section>

		<aside class="xiv-cart-summary xiv-order-first lg:xiv-order-last lg:xiv-sticky lg:xiv-top-8">
			<?php woocommerce_cart_totals(); ?>
		</aside>
	</div>

	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals">
	<?php woocommerce_cross_sell_display(); ?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
