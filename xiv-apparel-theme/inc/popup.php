<?php
/**
 * Newsletter signup popup.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

define( 'XIV_POPUP_COOKIE', 'xiv_popup_seen' );
define( 'XIV_POPUP_LIFETIME', 7 * DAY_IN_SECONDS );

/**
 * Render the popup once per week (cookie-gated).
 */
function xiv_render_newsletter_popup() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	if ( '0' === xiv_get_option( 'popup_enabled' ) ) {
		return;
	}

	if ( isset( $_COOKIE[ XIV_POPUP_COOKIE ] ) ) {
		return;
	}

	$heading = xiv_t( 'JOIN THE COLLECTION' );
	$body    = xiv_t( 'Sign up for early access, private drops and member pricing.' );
	$subs    = xiv_t( 'SUBSCRIBE' );
	$dismiss = xiv_t( 'NO THANKS' );
	$close   = xiv_t( 'Close newsletter popup' );
	?>
	<div id="xiv-newsletter-popup" class="xiv-hidden xiv-fixed xiv-inset-0 xiv-z-50 xiv-flex xiv-items-center xiv-justify-center xiv-px-4" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $heading ); ?>">
		<button type="button" class="xiv-absolute xiv-inset-0 xiv-bg-xiv-black/50 xiv-backdrop-blur-sm xiv-w-full xiv-h-full xiv-cursor-default" data-xiv-popup-close aria-hidden="true" tabindex="-1"></button>
		<div class="xiv-relative xiv-w-full xiv-max-w-md xiv-bg-xiv-bg xiv-border xiv-border-xiv-gray-light xiv-p-8 sm:xiv-p-10">
			<button type="button" class="xiv-absolute xiv-top-4 xiv-right-4 xiv-text-xs xiv-font-mono xiv-uppercase xiv-text-xiv-gray-text hover:xiv-text-xiv-black" data-xiv-popup-close>
				<?php echo esc_html( $close ); ?> &times;
			</button>
			<p class="xiv-text-xs xiv-font-mono xiv-text-xiv-gray-text xiv-uppercase xiv-tracking-widest">XIV</p>
			<h2 class="xiv-font-display xiv-font-extrabold xiv-uppercase xiv-tracking-tighter xiv-text-3xl xiv-mt-2"><?php echo esc_html( $heading ); ?></h2>
			<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-mt-3"><?php echo esc_html( $body ); ?></p>
			<form id="xiv-popup-newsletter-form" class="xiv-mt-6">
				<label class="xiv-sr-only" for="xiv-popup-newsletter-email"><?php echo esc_html( xiv_t( 'Email address' ) ); ?></label>
				<div class="xiv-flex xiv-border-b xiv-border-xiv-black">
					<input id="xiv-popup-newsletter-email" type="email" required placeholder="<?php echo esc_attr( xiv_t( 'YOUR EMAIL' ) ); ?>"
						   class="xiv-flex-1 xiv-bg-transparent xiv-border-0 xiv-text-sm xiv-uppercase xiv-tracking-widest xiv-font-bold placeholder:xiv-text-xiv-gray-text focus:xiv-outline-none" />
					<button type="submit" class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-black xiv-pl-4"><?php echo esc_html( $subs ); ?></button>
				</div>
				<p class="xiv-popup-newsletter-msg xiv-hidden xiv-text-xs xiv-uppercase xiv-tracking-widest xiv-mt-3" aria-live="polite"></p>
			</form>
			<button type="button" class="xiv-mt-6 xiv-text-xs xiv-font-mono xiv-uppercase xiv-text-xiv-gray-text hover:xiv-text-xiv-black" data-xiv-popup-dismiss>
				<?php echo esc_html( $dismiss ); ?>
			</button>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'xiv_render_newsletter_popup', 50 );
