<?php
/**
 * Single product tabs (Description / Additional information / Reviews).
 *
 * Custom minimal layout for XIV Apparel. Uses the same `$tabs` array from
 * `woocommerce_product_tabs` so all third-party tabs keep working.
 *
 * @package XIV_Apparel
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $tabs ) ) {
	return;
}
?>

<div class="woocommerce-tabs xiv-mt-14 xiv-border-t xiv-border-xiv-gray-light">
	<ul class="tabs wc-tabs xiv-flex xiv-flex-wrap xiv-gap-x-8 xiv-gap-y-2 xiv-border-b xiv-border-xiv-gray-light" role="tablist">
		<?php foreach ( $tabs as $key => $tab ) : ?>
			<li class="<?php echo esc_attr( $key ); ?>_tab xiv--mb-px" id="tab-title-<?php echo esc_attr( $key ); ?>" role="tab" aria-controls="tab-<?php echo esc_attr( $key ); ?>">
				<a href="#tab-<?php echo esc_attr( $key ); ?>" class="xiv-inline-block xiv-py-4 xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black">
					<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $tab['title'], $key ) ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php foreach ( $tabs as $key => $tab ) : ?>
		<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> panel entry-content wc-tab xiv-py-8" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
			<?php
			if ( isset( $tab['callback'] ) ) {
				call_user_func( $tab['callback'], $key, $tab );
			}
			?>
		</div>
	<?php endforeach; ?>
</div>
