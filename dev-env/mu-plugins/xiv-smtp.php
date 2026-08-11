<?php
/**
 * Send WordPress mail via Gmail SMTP (app password).
 *
 * Aktif ketika environment type != local. Credential diisi lewat
 * Settings > XIV SMTP. Di environment local, email dev tetap lewat
 * MailHog (lihat xiv-dev-mailhog.php) supaya credential Gmail tidak
 * kepakai saat pengembangan.
 *
 * @package XIV_Dev
 */

defined( 'ABSPATH' ) || exit;

$xiv_smtp_is_local = 'local' === wp_get_environment_type();

/**
 * Ambil pengaturan SMTP (opsi > konstanta).
 */
function xiv_smtp_get_option( $key ) {
	$consts = array(
		'from_email'  => 'XIV_SMTP_FROM_EMAIL',
		'from_name'   => 'XIV_SMTP_FROM_NAME',
		'username'    => 'XIV_SMTP_USERNAME',
		'password'    => 'XIV_SMTP_PASSWORD',
		'host'        => 'XIV_SMTP_HOST',
		'port'        => 'XIV_SMTP_PORT',
		'secure'      => 'XIV_SMTP_SECURE',
		'auth'        => 'XIV_SMTP_AUTH',
	);
	$defaults = array(
		'from_email' => '',
		'from_name'  => '',
		'username'   => '',
		'password'   => '',
		'host'       => 'smtp.gmail.com',
		'port'       => '465',
		'secure'     => 'ssl',
		'auth'       => 'yes',
	);

	$constant = $consts[ $key ] ?? '';
	if ( $constant && defined( $constant ) ) {
		$value = constant( $constant );
		if ( '' !== $value && null !== $value && false !== $value ) {
			return $value;
		}
	}

	$saved = get_option( 'xiv_smtp_settings', array() );
	if ( isset( $saved[ $key ] ) && '' !== $saved[ $key ] ) {
		return $saved[ $key ];
	}

	return $defaults[ $key ];
}

/**
 * Apakah SMTP aktif pada environment ini.
 */
function xiv_smtp_active() {
	return 'local' !== wp_get_environment_type();
}

if ( ! $xiv_smtp_is_local ) {

add_filter( 'wp_mail_from', function () {
	return xiv_smtp_get_option( 'from_email' );
} );

add_filter( 'wp_mail_from_name', function () {
	return xiv_smtp_get_option( 'from_name' );
} );

add_action( 'phpmailer_init', function ( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->Host       = xiv_smtp_get_option( 'host' );
	$phpmailer->Port       = (int) xiv_smtp_get_option( 'port' );
	$phpmailer->SMTPSecure = xiv_smtp_get_option( 'secure' );
	$phpmailer->Timeout    = 15;
	$phpmailer->SMTPAutoTLS = true;

	if ( 'yes' === xiv_smtp_get_option( 'auth' ) ) {
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = xiv_smtp_get_option( 'username' );
		$phpmailer->Password = xiv_smtp_get_option( 'password' );
	}
} );

} // endif: ! local.

/**
 * Halaman settings: Settings > XIV SMTP.
 */add_action( 'admin_menu', function () {
	add_options_page(
		'XIV SMTP',
		'XIV SMTP',
		'manage_options',
		'xiv-smtp',
		'xiv_smtp_render_page'
	);
} );

function xiv_smtp_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$updated = false;
	if ( isset( $_POST['xiv_smtp_save'], $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'xiv_smtp_save' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$settings = array(
			'from_email' => sanitize_email( wp_unslash( $_POST['from_email'] ?? '' ) ),
			'from_name'  => sanitize_text_field( wp_unslash( $_POST['from_name'] ?? '' ) ),
			'username'   => sanitize_text_field( wp_unslash( $_POST['username'] ?? '' ) ),
			'password'   => (string) wp_unslash( $_POST['password'] ?? '' ),
			'host'       => sanitize_text_field( wp_unslash( $_POST['host'] ?? 'smtp.gmail.com' ) ),
			'port'       => (int) ( $_POST['port'] ?? 465 ),
			'secure'     => in_array( $_POST['secure'] ?? '', array( 'ssl', 'tls' ), true ) ? sanitize_text_field( $_POST['secure'] ) : 'ssl',
			'auth'       => isset( $_POST['auth'] ) ? 'yes' : 'no',
		);
		update_option( 'xiv_smtp_settings', $settings );
		$updated = true;
	}

	$current = array(
		'from_email' => xiv_smtp_get_option( 'from_email' ),
		'from_name'  => xiv_smtp_get_option( 'from_name' ),
		'username'   => xiv_smtp_get_option( 'username' ),
		'password'   => get_option( 'xiv_smtp_settings', array() )['password'] ?? '',
		'host'       => xiv_smtp_get_option( 'host' ),
		'port'       => xiv_smtp_get_option( 'port' ),
		'secure'     => xiv_smtp_get_option( 'secure' ),
		'auth'       => xiv_smtp_get_option( 'auth' ),
	);

	$active = xiv_smtp_active();
	?>
	<div class="wrap">
		<h1>XIV SMTP</h1>
		<?php if ( $updated ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Settings saved.', 'xiv-apparel' ); ?></p></div>
		<?php endif; ?>

		<?php if ( $active ) : ?>
			<div class="notice notice-info"><p><?php esc_html_e( 'SMTP aktif: mail dikirim lewat Gmail.', 'xiv-apparel' ); ?></p></div>
		<?php else : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'SMTP nonaktif: environment saat ini local, mail dev lewat MailHog.', 'xiv-apparel' ); ?></p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'xiv_smtp_save' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="from_email"><?php esc_html_e( 'From email', 'xiv-apparel' ); ?></label></th>
					<td><input name="from_email" id="from_email" type="email" class="regular-text" value="<?php echo esc_attr( $current['from_email'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="from_name"><?php esc_html_e( 'From name', 'xiv-apparel' ); ?></label></th>
					<td><input name="from_name" id="from_name" type="text" class="regular-text" value="<?php echo esc_attr( $current['from_name'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="username"><?php esc_html_e( 'SMTP username (email Gmail)', 'xiv-apparel' ); ?></label></th>
					<td><input name="username" id="username" type="email" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $current['username'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="password"><?php esc_html_e( 'App password', 'xiv-apparel' ); ?></label></th>
					<td>
						<input name="password" id="password" type="password" class="regular-text" autocomplete="new-password" value="<?php echo esc_attr( $current['password'] ); ?>" />
						<p class="description"><?php esc_html_e( 'App Password 16 karakter (bukan password akun). Buat di myaccount.google.com > Keamanan > App passwords.', 'xiv-apparel' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="host"><?php esc_html_e( 'SMTP host', 'xiv-apparel' ); ?></label></th>
					<td><input name="host" id="host" type="text" class="regular-text" value="<?php echo esc_attr( $current['host'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="port"><?php esc_html_e( 'Port', 'xiv-apparel' ); ?></label></th>
					<td><input name="port" id="port" type="number" class="small-text" value="<?php echo esc_attr( $current['port'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Encryption', 'xiv-apparel' ); ?></th>
					<td>
						<label><input type="radio" name="secure" value="ssl" <?php checked( $current['secure'], 'ssl' ); ?> /> SSL (465)</label>
						<label style="margin-left:1em"><input type="radio" name="secure" value="tls" <?php checked( $current['secure'], 'tls' ); ?> /> TLS (587)</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Authentication', 'xiv-apparel' ); ?></th>
					<td><label><input type="checkbox" name="auth" value="1" <?php checked( $current['auth'], 'yes' ); ?> /> <?php esc_html_e( 'Gunakan SMTP authentication', 'xiv-apparel' ); ?></label></td>
				</tr>
			</table>
			<p class="submit"><button type="submit" name="xiv_smtp_save" class="button button-primary"><?php esc_html_e( 'Save settings', 'xiv-apparel' ); ?></button></p>
		</form>
	</div>
	<?php
}
