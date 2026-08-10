<?php
/**
 * WhatsApp-style OTP login (password tetap ada, OTP opsional).
 *
 * Alur:
 * 1. User memilih tab "Nomor HP + OTP" di form login.
 * 2. Isi nomor HP -> AJAX xiv_otp_send -> OTP dikirim (email default,
 *    WhatsApp via filter xiv_otp_transport / xiv_otp_send_whatsapp).
 * 3. Isi kode -> AJAX xiv_otp_verify -> auto login + redirect.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normalisasi nomor HP ke format internasional (62...).
 */
function xiv_otp_normalize_phone( $phone ) {
	$digits = preg_replace( '/\D+/', '', $phone );
	if ( strpos( $digits, '0' ) === 0 ) {
		$digits = '62' . substr( $digits, 1 );
	} elseif ( strpos( $digits, '8' ) === 0 ) {
		$digits = '62' . $digits;
	}
	return $digits;
}

/**
 * Temukan user berdasarkan nomor HP (billing_phone).
 */
function xiv_otp_find_user_by_phone( $phone ) {
	$phone  = xiv_otp_normalize_phone( $phone );
	$users  = get_users( array(
		'meta_key' => 'billing_phone',
		'number'   => -1,
	) );
	$found  = null;

	foreach ( $users as $user ) {
		$stored = xiv_otp_normalize_phone( get_user_meta( $user->ID, 'billing_phone', true ) );
		if ( $stored === $phone ) {
			$found = $user;
			break;
		}
	}

	return $found;
}

/**
 * Transport pengiriman OTP. Default email (MailHog di dev).
 * Untuk WhatsApp produksi: add_filter('xiv_otp_transport', fn() => 'whatsapp')
 * dan hook xiv_otp_send_whatsapp($phone, $code, $user_id).
 */
function xiv_otp_get_transport() {
	return (string) apply_filters( 'xiv_otp_transport', 'email' );
}

/**
 * Kirim kode OTP ke user.
 */
function xiv_otp_send_code( $user_id, $phone, $code ) {
	$transport = xiv_otp_get_transport();

	if ( 'whatsapp' === $transport ) {
		do_action( 'xiv_otp_send_whatsapp', $phone, $code, $user_id );
		return;
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return;
	}

	/* translators: %s: kode OTP */
	$subject = sprintf( __( '[XIV Apparel] Kode OTP: %s', 'xiv-apparel' ), $code );
	$message = sprintf(
		__( "Halo %s,\n\nKode OTP kamu untuk login adalah: %s\nKode berlaku selama 5 menit.\n\nJangan bagikan kode ini ke siapa pun.\n\n— XIV Apparel", 'xiv-apparel' ),
		$user->display_name,
		$code
	);

	wp_mail( $user->user_email, $subject, $message );
}

/**
 * Endpoint: kirim OTP.
 */
function xiv_otp_ajax_send() {
	check_ajax_referer( 'xiv_otp_nonce', 'nonce' );

	$phone = xiv_otp_normalize_phone( sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ) );
	if ( strlen( $phone ) < 9 ) {
		wp_send_json_error( array( 'message' => __( 'Nomor HP tidak valid.', 'xiv-apparel' ) ) );
	}

	$user = xiv_otp_find_user_by_phone( $phone );
	if ( ! $user ) {
		wp_send_json_error( array( 'message' => __( 'Nomor HP tidak terdaftar. Silakan register terlebih dahulu.', 'xiv-apparel' ) ) );
	}

	$transient_key = 'xiv_otp_' . md5( $phone );
	$existing      = get_transient( $transient_key );

	if ( $existing && ! empty( $existing['sent_at'] ) && ( time() - (int) $existing['sent_at'] ) < 60 ) {
		wp_send_json_error( array( 'message' => __( 'Kode sudah dikirim. Coba lagi dalam 1 menit.', 'xiv-apparel' ) ) );
	}

	$code = (string) wp_rand( 100000, 999999 );

	set_transient( $transient_key, array(
		'user_id'  => $user->ID,
		'hash'     => wp_hash( $phone . '|' . $code ),
		'expires'  => time() + 5 * MINUTE_IN_SECONDS,
		'attempts' => 0,
		'sent_at'  => time(),
	), 5 * MINUTE_IN_SECONDS );

	xiv_otp_send_code( $user->ID, $phone, $code );

	$response = array(
		'message' => __( 'Kode OTP terkirim.', 'xiv-apparel' ),
		'phone'   => $phone,
	);

	// Dev hanya: tampilkan kode agar mudah diuji di localhost.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && 'local' === wp_get_environment_type() ) {
		$response['dev_code'] = $code;
	}

	wp_send_json_success( $response );
}
add_action( 'wp_ajax_xiv_otp_send', 'xiv_otp_ajax_send' );
add_action( 'wp_ajax_nopriv_xiv_otp_send', 'xiv_otp_ajax_send' );

/**
 * Endpoint: verifikasi OTP + auto login.
 */
function xiv_otp_ajax_verify() {
	check_ajax_referer( 'xiv_otp_nonce', 'nonce' );

	$phone = xiv_otp_normalize_phone( sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ) );
	$code  = preg_replace( '/\D+/', '', sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) ) );

	if ( strlen( $phone ) < 9 || ! $code ) {
		wp_send_json_error( array( 'message' => __( 'Nomor HP atau kode tidak valid.', 'xiv-apparel' ) ) );
	}

	$transient_key = 'xiv_otp_' . md5( $phone );
	$data          = get_transient( $transient_key );

	if ( ! $data || empty( $data['user_id'] ) || empty( $data['hash'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Kode tidak ditemukan. Kirim ulang kode.', 'xiv-apparel' ) ) );
	}

	if ( time() > (int) $data['expires'] ) {
		delete_transient( $transient_key );
		wp_send_json_error( array( 'message' => __( 'Kode kedaluwarsa. Kirim ulang kode.', 'xiv-apparel' ) ) );
	}

	if ( (int) $data['attempts'] >= 5 ) {
		delete_transient( $transient_key );
		wp_send_json_error( array( 'message' => __( 'Terlalu banyak percobaan. Kirim ulang kode.', 'xiv-apparel' ) ) );
	}

	if ( ! hash_equals( $data['hash'], wp_hash( $phone . '|' . $code ) ) ) {
		$data['attempts'] = (int) $data['attempts'] + 1;
		set_transient( $transient_key, $data, $data['expires'] - time() );
		wp_send_json_error( array( 'message' => __( 'Kode salah. Coba lagi.', 'xiv-apparel' ) ) );
	}

	delete_transient( $transient_key );

	$user_id = (int) $data['user_id'];
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );
	do_action( 'wp_login', get_userdata( $user_id )->user_login, get_userdata( $user_id ) );

	wp_send_json_success( array(
		'message'  => __( 'Login berhasil.', 'xiv-apparel' ),
		'redirect' => apply_filters( 'xiv_otp_login_redirect', wc_get_page_permalink( 'myaccount' ), $user_id ),
	) );
}
add_action( 'wp_ajax_xiv_otp_verify', 'xiv_otp_ajax_verify' );
add_action( 'wp_ajax_nopriv_xiv_otp_verify', 'xiv_otp_ajax_verify' );

/**
 * Simpan billing_phone dari form register.
 */
function xiv_otp_save_register_phone( $customer_id, $new_customer_data, $password_generated ) {
	if ( ! empty( $_POST['billing_phone'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce WC sudah dicek.
		$phone = sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) );
		update_user_meta( $customer_id, 'billing_phone', xiv_otp_normalize_phone( $phone ) );
	}
}
add_action( 'woocommerce_created_customer', 'xiv_otp_save_register_phone', 10, 3 );

/**
 * Validasi nomor HP saat register.
 */
function xiv_otp_validate_register_phone( $username, $email, $validation_errors ) {
	if ( empty( $_POST['billing_phone'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce WC sudah dicek.
		$validation_errors->add( 'billing_phone_error', __( 'Nomor HP (WhatsApp) wajib diisi.', 'xiv-apparel' ) );
		return;
	}

	$phone = xiv_otp_normalize_phone( sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) );
	if ( strlen( $phone ) < 9 ) {
		$validation_errors->add( 'billing_phone_error', __( 'Nomor HP tidak valid.', 'xiv-apparel' ) );
		return;
	}

	if ( xiv_otp_find_user_by_phone( $phone ) ) {
		$validation_errors->add( 'billing_phone_error', __( 'Nomor HP sudah terdaftar. Silakan login.', 'xiv-apparel' ) );
	}
}
add_action( 'woocommerce_register_post', 'xiv_otp_validate_register_phone', 10, 3 );

/**
 * Localize untuk JS login.
 */
function xiv_otp_localize() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}
	wp_localize_script( 'xiv-otp-login', 'XIV_OTP', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'xiv_otp_nonce' ),
		'i18n'    => array(
			'sending'     => __( 'MENGIRIM…', 'xiv-apparel' ),
			'sent'        => __( 'KODE TERKIRIM', 'xiv-apparel' ),
			'verifying'   => __( 'MEMVERIFIKASI…', 'xiv-apparel' ),
			'resendIn'    => __( 'KIRIM ULANG', 'xiv-apparel' ),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'xiv_otp_localize', 30 );
