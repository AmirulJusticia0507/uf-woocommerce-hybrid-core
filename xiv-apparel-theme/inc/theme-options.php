<?php
/**
 * Theme options: admin panel + front-end getter.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default theme options.
 */
function xiv_theme_options_defaults() {
	return array(
		'support_whatsapp' => '',
		'support_phone'    => '',
		'social_instagram' => '',
		'social_tiktok'    => '',
		'social_facebook'  => '',
		'social_youtube'   => '',
		'social_x'         => '',
		'popup_enabled'    => '1',
		'default_lang'     => 'en',
	);
}

/**
 * Get a theme option with default fallback.
 */
function xiv_get_option( string $key, $default = '' ) {
	$opts = get_option( 'xiv_theme_options', array() );
	$defs = xiv_theme_options_defaults();

	if ( isset( $opts[ $key ] ) && '' !== $opts[ $key ] ) {
		return $opts[ $key ];
	}
	return $defs[ $key ] ?? $default;
}

/**
 * Render social links row in footer (if any are set).
 */
function xiv_social_links() {
	$links = array(
		'instagram' => xiv_get_option( 'social_instagram' ),
		'tiktok'    => xiv_get_option( 'social_tiktok' ),
		'facebook'  => xiv_get_option( 'social_facebook' ),
		'youtube'   => xiv_get_option( 'social_youtube' ),
		'x'         => xiv_get_option( 'social_x' ),
	);
	$links = array_filter( $links );

	if ( empty( $links ) ) {
		return;
	}

	echo '<div class="xiv-flex xiv-flex-wrap xiv-gap-x-4 xiv-gap-y-2 xiv-mt-4">';
	foreach ( $links as $platform => $url ) {
		$label = ucfirst( 'x' === $platform ? 'X (Twitter)' : $platform );
		echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer" class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black">' . esc_html( $label ) . '</a>';
	}
	echo '</div>';
}

/**
 * WhatsApp support link.
 */
function xiv_support_whatsapp_url() {
	$phone = xiv_get_option( 'support_whatsapp' );
	if ( ! $phone ) {
		return '';
	}
	$phone = preg_replace( '/[^0-9]/', '', $phone );
	if ( 0 === strpos( $phone, '0' ) ) {
		$phone = '62' . substr( $phone, 1 );
	}
	return 'https://wa.me/' . $phone;
}

if ( is_admin() ) {

	add_action( 'admin_menu', 'xiv_theme_options_menu', 11 );
	add_action( 'admin_init', 'xiv_theme_options_save' );

	/**
	 * Register submenu page.
	 */
	function xiv_theme_options_menu() {
		$cap = defined( 'XIV_ADMIN_CAP' ) ? XIV_ADMIN_CAP : 'manage_woocommerce_products';

		add_submenu_page( 'xiv-dashboard', 'Theme Options', 'Theme Options', $cap, 'xiv-theme-options', 'xiv_theme_options_page' );
	}

	/**
	 * Save options.
	 */
	function xiv_theme_options_save() {
		if ( ! isset( $_POST['xiv_theme_options_submit'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce_products' ) ) {
			wp_die( 'Nope.' );
		}
		check_admin_referer( 'xiv_theme_options_save' );

		$defs   = xiv_theme_options_defaults();
		$fields = array_keys( $defs );
		$opts   = array();

		foreach ( $fields as $key ) {
			if ( 'popup_enabled' === $key ) {
				$opts[ $key ] = isset( $_POST[ $key ] ) ? '1' : '0';
			} elseif ( 'default_lang' === $key ) {
				$opts[ $key ] = ( 'id' === ( $_POST[ $key ] ?? 'en' ) ) ? 'id' : 'en';
			} else {
				$opts[ $key ] = sanitize_text_field( wp_unslash( $_POST[ $key ] ?? '' ) );
			}
		}

		update_option( 'xiv_theme_options', $opts );
		wp_safe_redirect( admin_url( 'admin.php?page=xiv-theme-options&saved=1' ) );
		exit;
	}

	/**
	 * Render options page.
	 */
	function xiv_theme_options_page() {
		$saved = isset( $_GET['saved'] );
		?>
		<div class="wrap xiv-pb-8">
			<h1 class="xiv-mb-1 xiv-text-[22px] xiv-font-black xiv-uppercase xiv-tracking-tight"><?php esc_html_e( 'Theme Options', 'xiv-apparel' ); ?></h1>
			<p class="xiv-mt-0 xiv-text-[13px] xiv-text-xiv-gray-text"><?php esc_html_e( 'Pengaturan umum tema.', 'xiv-apparel' ); ?></p>

			<?php if ( $saved ) : ?>
				<div class="xiv-bg-green-50 xiv-border xiv-border-green-200 xiv-text-green-800 xiv-rounded xiv-px-4 xiv-py-3 xiv-my-4 xiv-text-sm"><?php esc_html_e( 'Tersimpan.', 'xiv-apparel' ); ?></div>
			<?php endif; ?>

			<form method="post" action="" class="xiv-max-w-2xl xiv-mt-4">
				<input type="hidden" name="xiv_theme_options_submit" value="1" />
				<?php wp_nonce_field( 'xiv_theme_options_save' ); ?>

				<h2 class="xiv-text-base xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mt-6"><?php esc_html_e( 'Support', 'xiv-apparel' ); ?></h2>
				<table class="form-table">
					<tr>
						<th><label for="support_whatsapp">WhatsApp (format: 081234567890)</label></th>
						<td><input id="support_whatsapp" name="support_whatsapp" type="text" class="regular-text" value="<?php echo esc_attr( xiv_get_option( 'support_whatsapp' ) ); ?>" /></td>
					</tr>
					<tr>
						<th><label for="support_phone">Telepon</label></th>
						<td><input id="support_phone" name="support_phone" type="text" class="regular-text" value="<?php echo esc_attr( xiv_get_option( 'support_phone' ) ); ?>" /></td>
					</tr>
				</table>

				<h2 class="xiv-text-base xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mt-6"><?php esc_html_e( 'Social Media', 'xiv-apparel' ); ?></h2>
				<table class="form-table">
					<tr>
						<th><label for="social_instagram">Instagram</label></th>
						<td><input id="social_instagram" name="social_instagram" type="url" class="regular-text" value="<?php echo esc_attr( xiv_get_option( 'social_instagram' ) ); ?>" placeholder="https://instagram.com/xiv" /></td>
					</tr>
					<tr>
						<th><label for="social_tiktok">TikTok</label></th>
						<td><input id="social_tiktok" name="social_tiktok" type="url" class="regular-text" value="<?php echo esc_attr( xiv_get_option( 'social_tiktok' ) ); ?>" /></td>
					</tr>
					<tr>
						<th><label for="social_facebook">Facebook</label></th>
						<td><input id="social_facebook" name="social_facebook" type="url" class="regular-text" value="<?php echo esc_attr( xiv_get_option( 'social_facebook' ) ); ?>" /></td>
					</tr>
					<tr>
						<th><label for="social_youtube">YouTube</label></th>
						<td><input id="social_youtube" name="social_youtube" type="url" class="regular-text" value="<?php echo esc_attr( xiv_get_option( 'social_youtube' ) ); ?>" /></td>
					</tr>
					<tr>
						<th><label for="social_x">X (Twitter)</label></th>
						<td><input id="social_x" name="social_x" type="url" class="regular-text" value="<?php echo esc_attr( xiv_get_option( 'social_x' ) ); ?>" /></td>
					</tr>
				</table>

				<h2 class="xiv-text-base xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mt-6"><?php esc_html_e( 'Behavior', 'xiv-apparel' ); ?></h2>
				<table class="form-table">
					<tr>
						<th><label for="popup_enabled"><?php esc_html_e( 'Aktifkan popup newsletter', 'xiv-apparel' ); ?></label></th>
						<td><input id="popup_enabled" name="popup_enabled" type="checkbox" value="1" <?php checked( '1', xiv_get_option( 'popup_enabled' ) ); ?> /></td>
					</tr>
					<tr>
						<th><label for="default_lang"><?php esc_html_e( 'Bahasa default', 'xiv-apparel' ); ?></label></th>
						<td>
							<select id="default_lang" name="default_lang">
								<option value="en" <?php selected( 'en', xiv_get_option( 'default_lang' ) ); ?>>English</option>
								<option value="id" <?php selected( 'id', xiv_get_option( 'default_lang' ) ); ?>>Indonesia</option>
							</select>
						</td>
					</tr>
				</table>

				<p class="submit"><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Simpan', 'xiv-apparel' ); ?>" /></p>
			</form>
		</div>
		<?php
	}
}
