<?php
/**
 * Cart item data (when outputting non-flat).
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     XIV_Apparel
 * @version     2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<dl class="variation xiv-m-0 xiv-mt-2 xiv-space-y-0.5">
	<?php foreach ( $item_data as $data ) : ?>
		<dt class="<?php echo sanitize_html_class( 'variation-' . $data['key'] ); ?> xiv-inline xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text"><?php echo wp_kses_post( $data['key'] ); ?>:</dt>
		<dd class="<?php echo sanitize_html_class( 'variation-' . $data['key'] ); ?> xiv-inline xiv-text-xs xiv-uppercase xiv-text-xiv-black xiv-m-0"><?php echo wp_kses_post( wpautop( $data['display'] ) ); ?></dd>
	<?php endforeach; ?>
</dl>
