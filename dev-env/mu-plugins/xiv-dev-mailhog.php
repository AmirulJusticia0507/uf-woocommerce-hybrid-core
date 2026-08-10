<?php
/**
 * Dev only: route WordPress mail to the local MailHog SMTP (docker-compose).
 *
 * Hanya aktif ketika environment type = local, jadi aman jika mu-plugin ini
 * ikut ter-deploy ke produksi (environment type != local akan di-skip).
 *
 * @package XIV_Dev
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type() ) {
	add_action( 'phpmailer_init', function ( $phpmailer ) {
		$phpmailer->isSMTP();
		$phpmailer->Host       = 'mailhog';
		$phpmailer->Port       = 1025;
		$phpmailer->SMTPAuth   = false;
		$phpmailer->SMTPSecure = '';
		$phpmailer->Timeout    = 5;
	} );
}
