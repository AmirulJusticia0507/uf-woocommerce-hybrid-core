<?php
/**
 * WebAuthn / Passkey login (biometrik: fingerprint / Face ID).
 *
 * Alur:
 * 1. User login biasa dulu, daftarkan perangkat di My Account > Biometrik.
 * 2. Setelah itu tombol "Login dengan fingerprint" muncul di form login
 *    (discoverable/resident key, tidak perlu ketik username).
 * 3. Verifikasi ECDSA P-256 (ES256) murni PHP tanpa dependency.
 *
 * Catatan: attestation statement tidak diverifikasi (hanya rpIdHash,
 * origin, challenge, dan signature assertion). Berlaku untuk MVP.
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------- helpers

function xiv_wkn_b64url_encode( $data ) {
	return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
}

function xiv_wkn_b64url_decode( $data ) {
	$data = strtr( $data, '-_', '+/' );
	$pad  = strlen( $data ) % 4;
	if ( $pad ) {
		$data .= str_repeat( '=', 4 - $pad );
	}
	return base64_decode( $data );
}

/**
 * Decoder CBOR minimal (cukup untuk attestation/assertion authenticator).
 */
function xiv_wkn_cbor_len( $data, $info, &$offset ) {
	if ( $info < 24 ) {
		return $info;
	}
	$nbytes = 1;
	if ( 25 === $info ) {
		$nbytes = 2;
	} elseif ( 26 === $info ) {
		$nbytes = 4;
	} elseif ( 27 === $info ) {
		$nbytes = 8;
	}
	$value = 0;
	for ( $i = 0; $i < $nbytes; $i++ ) {
		$value = ( $value << 8 ) | ord( $data[ $offset++ ] );
	}
	return $value;
}

function xiv_wkn_cbor_decode( $data, &$offset = 0 ) {
	$length   = strlen( $data );
	$initial  = isset( $data[ $offset ] ) ? ord( $data[ $offset ] ) : -1;
	if ( $initial < 0 || $offset >= $length ) {
		return null;
	}
	$major = ( $initial >> 5 ) & 0x07;
	$info  = $initial & 0x1f;
	$offset++;

	$read_int = function () use ( $data, $info, &$offset ) {
		return xiv_wkn_cbor_len( $data, $info, $offset );
	};

	switch ( $major ) {
		case 0:
			return $read_int();
		case 1:
			return -1 - $read_int();
		case 2:
			$len    = $read_int();
			$str    = substr( $data, $offset, $len );
			$offset += $len;
			return $str;
		case 3:
			$len    = $read_int();
			$str    = substr( $data, $offset, $len );
			$offset += $len;
			return $str;
		case 4:
			$count = $read_int();
			$arr   = array();
			for ( $i = 0; $i < $count; $i++ ) {
				$arr[] = xiv_wkn_cbor_decode( $data, $offset );
			}
			return $arr;
		case 5:
			$count = $read_int();
			$map   = array();
			for ( $i = 0; $i < $count; $i++ ) {
				$key        = xiv_wkn_cbor_decode( $data, $offset );
				$map[ $key ] = xiv_wkn_cbor_decode( $data, $offset );
			}
			return $map;
		case 6:
			xiv_wkn_cbor_decode( $data, $offset );
			return xiv_wkn_cbor_decode( $data, $offset );
	}
	return null;
}

function xiv_wkn_der_len( $len ) {
	if ( $len < 0x80 ) {
		return chr( $len );
	}
	if ( $len <= 0xff ) {
		return "\x81" . chr( $len );
	}
	return "\x82" . chr( $len >> 8 ) . chr( $len & 0xff );
}

function xiv_wkn_der_seq( $contents ) {
	return "\x30" . xiv_wkn_der_len( strlen( $contents ) ) . $contents;
}

/**
 * Konversi COSE EC2 (P-256, ES256) menjadi PEM SubjectPublicKeyInfo.
 */
function xiv_wkn_cose_to_ec_pem( $cose ) {
	if ( ! is_array( $cose ) || 2 !== (int) ( $cose[1] ?? 0 ) || -7 !== (int) ( $cose[3] ?? 0 ) || 1 !== (int) ( $cose[-1] ?? 0 ) ) {
		return false;
	}
	$x = $cose[-2] ?? '';
	$y = $cose[-3] ?? '';
	if ( strlen( $x ) !== 32 || strlen( $y ) !== 32 ) {
		return false;
	}

	$point = "\x04" . $x . $y;
	$alg_id = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01" . "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
	$spki   = xiv_wkn_der_seq( xiv_wkn_der_seq( $alg_id ) . "\x03" . xiv_wkn_der_len( strlen( $point ) + 1 ) . "\x00" . $point );

	return "-----BEGIN PUBLIC KEY-----\n" . chunk_split( base64_encode( $spki ), 64, "\n" ) . "-----END PUBLIC KEY-----";
}

function xiv_wkn_raw_sig_to_der( $raw ) {
	if ( strlen( $raw ) !== 64 ) {
		return $raw;
	}
	$r = ltrim( substr( $raw, 0, 32 ), "\x00" );
	$s = ltrim( substr( $raw, 32, 32 ), "\x00" );
	if ( '' === $r || ( ord( $r[0] ) & 0x80 ) ) {
		$r = "\x00" . $r;
	}
	if ( '' === $s || ( ord( $s[0] ) & 0x80 ) ) {
		$s = "\x00" . $s;
	}
	$int = function ( $v ) {
		return "\x02" . xiv_wkn_der_len( strlen( $v ) ) . $v;
	};
	return xiv_wkn_der_seq( $int( $r ) . $int( $s ) );
}

/**
 * Konfigurasi Relying Party (hostname dari home_url; origin termasuk port).
 */
function xiv_wkn_rp() {
	$parts  = parse_url( home_url() );
	$host   = ! empty( $parts['host'] ) ? $parts['host'] : 'localhost';
	$scheme = ! empty( $parts['scheme'] ) ? $parts['scheme'] : 'https';
	$port   = ! empty( $parts['port'] ) ? ':' . $parts['port'] : '';
	return array(
		'id'     => $host,
		'name'   => get_bloginfo( 'name' ),
		'origin' => $scheme . '://' . $host . $port,
	);
}

/**
 * Challenge disimpan di transient (10 menit) supaya aman antar request.
 * Disimpan sebagai base64url (ASCII) karena wpdb memblokir byte biner.
 */
function xiv_wkn_new_session( $type, $extra = array() ) {
	$session_id = bin2hex( random_bytes( 16 ) );
	$data       = array_merge( array( 'type' => $type, 'challenge' => xiv_wkn_b64url_encode( random_bytes( 32 ) ), 'time' => time() ), $extra );
	set_transient( 'xiv_wkn_' . $session_id, $data, 10 * MINUTE_IN_SECONDS );
	return $session_id;
}

function xiv_wkn_get_credentials( $user_id ) {
	$creds = get_user_meta( $user_id, 'xiv_webauthn_credentials', true );
	return is_array( $creds ) ? $creds : array();
}

function xiv_wkn_save_credentials( $user_id, $creds ) {
	update_user_meta( $user_id, 'xiv_webauthn_credentials', $creds );
}

/**
 * Indeks global credential ID -> user ID (untuk login resident key tanpa username).
 */
function xiv_wkn_index() {
	$index = get_option( 'xiv_webauthn_index', array() );
	return is_array( $index ) ? $index : array();
}

function xiv_wkn_index_user( $cred_id, $user_id ) {
	$index              = xiv_wkn_index();
	$index[ $cred_id ]  = (int) $user_id;
	update_option( 'xiv_webauthn_index', $index );
}

function xiv_wkn_unindex( $cred_id ) {
	$index = xiv_wkn_index();
	unset( $index[ $cred_id ] );
	update_option( 'xiv_webauthn_index', $index );
}

function xiv_wkn_parse_auth_data( $auth_data ) {
	$result = array(
		'rp_id_hash'    => substr( $auth_data, 0, 32 ),
		'flags'         => isset( $auth_data[32] ) ? ord( $auth_data[32] ) : 0,
		'counter'       => 0,
		'credential_id' => '',
		'cose'          => null,
		'cose_raw'      => '',
	);
	if ( strlen( $auth_data ) >= 37 ) {
		$result['counter'] = unpack( 'N', substr( $auth_data, 33, 4 ) )[1];
	}
	if ( ( $result['flags'] & 0x40 ) && strlen( $auth_data ) >= 55 ) {
		$cred_id_len              = unpack( 'n', substr( $auth_data, 53, 2 ) )[1];
		$result['credential_id']  = substr( $auth_data, 55, $cred_id_len );
		$result['cose_raw']       = substr( $auth_data, 55 + $cred_id_len );
		$result['cose']           = xiv_wkn_cbor_decode( $result['cose_raw'] );
	}
	return $result;
}

function xiv_wkn_parse_attestation( $attestation_object_b64url ) {
	$att = xiv_wkn_cbor_decode( xiv_wkn_b64url_decode( $attestation_object_b64url ) );
	if ( ! is_array( $att ) || empty( $att['authData'] ) ) {
		return false;
	}
	return xiv_wkn_parse_auth_data( $att['authData'] );
}

/**
 * Verifikasi assertion WebAuthn (login): origin, challenge, rpIdHash, ECDSA.
 */
function xiv_wkn_verify_assertion( $cred, $client_data_json, $auth_data, $signature, $session ) {
	if ( strlen( $auth_data ) < 37 || ! $signature ) {
		return false;
	}
	$rp = xiv_wkn_rp();
	$cd = json_decode( $client_data_json, true );
	if ( ! is_array( $cd ) || 'webauthn.get' !== ( $cd['type'] ?? '' ) ) {
		return false;
	}
	if ( ( $cd['origin'] ?? '' ) !== $rp['origin'] ) {
		return false;
	}
	if ( ! hash_equals( (string) ( $cd['challenge'] ?? '' ), $session['challenge'] ) ) {
		return false;
	}
	if ( ! hash_equals( hash( 'sha256', $rp['id'], true ), substr( $auth_data, 0, 32 ) ) ) {
		return false;
	}

	$pem = xiv_wkn_cose_to_ec_pem( xiv_wkn_cbor_decode( base64_decode( (string) $cred['pk'] ) ) );
	if ( ! $pem ) {
		return false;
	}

	$signed = $auth_data . hash( 'sha256', $client_data_json, true );
	$der    = xiv_wkn_raw_sig_to_der( $signature );

	return 1 === openssl_verify( $signed, $der, $pem, OPENSSL_ALGO_SHA256 );
}

function xiv_wkn_devices_data( $user_id ) {
	$out = array();
	foreach ( xiv_wkn_get_credentials( $user_id ) as $c ) {
		$out[] = array(
			'id'      => $c['id'],
			'name'    => $c['name'] ? $c['name'] : __( 'Perangkat biometrik', 'xiv-apparel' ),
			'created' => gmdate( get_option( 'date_format' ), $c['created'] ),
			'last'    => gmdate( get_option( 'date_format' ), $c['last'] ),
		);
	}
	return $out;
}

// ---------------------------------------------------------------- AJAX

function xiv_wkn_json_body() {
	$raw  = file_get_contents( 'php://input' );
	$data = json_decode( $raw, true );
	return is_array( $data ) ? $data : array();
}

function xiv_wkn_check_nonce( $data ) {
	return ! empty( $data['nonce'] ) && wp_verify_nonce( sanitize_key( $data['nonce'] ), 'xiv_webauthn_nonce' );
}

/**
 * Register: ambil options (challenge) untuk perangkat baru.
 */
function xiv_wkn_ajax_register_options() {
	$data = xiv_wkn_json_body();
	if ( ! xiv_wkn_check_nonce( $data ) || ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Session tidak valid.', 'xiv-apparel' ) ) );
	}
	$user_id = get_current_user_id();
	$user    = wp_get_current_user();
	$session = xiv_wkn_new_session( 'register', array( 'user_id' => $user_id ) );
	$data    = get_transient( 'xiv_wkn_' . $session );

	$exclude = array();
	foreach ( xiv_wkn_get_credentials( $user_id ) as $c ) {
		$exclude[] = array( 'type' => 'public-key', 'id' => $c['id'] );
	}

	$rp = xiv_wkn_rp();
	wp_send_json_success( array(
		'session' => $session,
		'origin'  => $rp['origin'],
		'options' => array(
			'publicKey' => array(
				'rp'          => array( 'id' => $rp['id'], 'name' => $rp['name'] ),
				'user'        => array(
					'id'          => xiv_wkn_b64url_encode( (string) $user_id ),
					'name'        => $user->user_email,
					'displayName' => $user->display_name ? $user->display_name : $user->user_login,
				),
				'challenge'      => $data['challenge'],
				'pubKeyCredParams' => array(
					array( 'type' => 'public-key', 'alg' => -7 ),
					array( 'type' => 'public-key', 'alg' => -257 ),
				),
				'timeout'              => 60000,
				'attestation'          => 'none',
				'authenticatorSelection' => array(
					'authenticatorAttachment' => 'platform',
					'residentKey'             => 'preferred',
					'userVerification'        => 'preferred',
				),
				'excludeCredentials'   => $exclude,
			),
		),
	) );
}
add_action( 'wp_ajax_xiv_wkn_register_options', 'xiv_wkn_ajax_register_options' );

/**
 * Register: simpan kredensial setelah verifikasi attestation.
 */
function xiv_wkn_ajax_register() {
	$data = xiv_wkn_json_body();
	if ( ! xiv_wkn_check_nonce( $data ) || ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Session tidak valid.', 'xiv-apparel' ) ) );
	}

	$user_id = get_current_user_id();
	$stored  = get_transient( 'xiv_wkn_' . sanitize_key( $data['session'] ?? '' ) );
	if ( ! $stored || 'register' !== $stored['type'] || (int) $stored['user_id'] !== $user_id ) {
		wp_send_json_error( array( 'message' => __( 'Sesi kedaluwarsa. Coba lagi.', 'xiv-apparel' ) ) );
	}
	delete_transient( 'xiv_wkn_' . sanitize_key( $data['session'] ?? '' ) );

	$att = xiv_wkn_parse_attestation( $data['response']['attestationObject'] ?? '' );
	if ( ! $att || empty( $att['credential_id'] ) || empty( $att['cose_raw'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Perangkat tidak valid.', 'xiv-apparel' ) ) );
	}

	$client_data_json = xiv_wkn_b64url_decode( $data['response']['clientDataJSON'] ?? '' );
	$cd               = json_decode( $client_data_json, true );
	$rp               = xiv_wkn_rp();
	if ( ! is_array( $cd ) || 'webauthn.create' !== ( $cd['type'] ?? '' ) ) {
		wp_send_json_error( array( 'message' => __( 'Verifikasi gagal.', 'xiv-apparel' ) ) );
	}
	if ( ( $cd['origin'] ?? '' ) !== $rp['origin'] ) {
		wp_send_json_error( array( 'message' => __( 'Origin tidak cocok.', 'xiv-apparel' ) ) );
	}
	if ( ! hash_equals( (string) ( $cd['challenge'] ?? '' ), $stored['challenge'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Challenge tidak cocok.', 'xiv-apparel' ) ) );
	}
	if ( ! hash_equals( hash( 'sha256', $rp['id'], true ), $att['rp_id_hash'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Domain tidak cocok.', 'xiv-apparel' ) ) );
	}

	$cred_id = xiv_wkn_b64url_encode( $att['credential_id'] );
	$creds   = xiv_wkn_get_credentials( $user_id );
	foreach ( $creds as $c ) {
		if ( hash_equals( $c['id'], $cred_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Perangkat sudah terdaftar.', 'xiv-apparel' ) ) );
		}
	}

	$creds[] = array(
		'id'      => $cred_id,
		'pk'      => base64_encode( $att['cose_raw'] ),
		'counter' => (int) $att['counter'],
		'name'    => sanitize_text_field( $data['name'] ?? '' ),
		'created' => time(),
		'last'    => time(),
	);
	xiv_wkn_save_credentials( $user_id, $creds );
	xiv_wkn_index_user( $cred_id, $user_id );

	wp_send_json_success( array(
		'message' => __( 'Perangkat biometrik berhasil didaftarkan.', 'xiv-apparel' ),
		'devices' => xiv_wkn_devices_data( $user_id ),
	) );
}
add_action( 'wp_ajax_xiv_wkn_register', 'xiv_wkn_ajax_register' );

/**
 * Login: options untuk resident key (tanpa username).
 */
function xiv_wkn_ajax_login_options() {
	$data = xiv_wkn_json_body();
	if ( ! xiv_wkn_check_nonce( $data ) ) {
		wp_send_json_error( array( 'message' => __( 'Session tidak valid.', 'xiv-apparel' ) ) );
	}
	$session = xiv_wkn_new_session( 'login' );
	$stored  = get_transient( 'xiv_wkn_' . $session );
	$rp      = xiv_wkn_rp();

	wp_send_json_success( array(
		'session' => $session,
		'origin'  => $rp['origin'],
		'options' => array(
			'publicKey' => array(
				'challenge'         => $stored['challenge'],
				'rpId'              => $rp['id'],
				'timeout'           => 60000,
				'userVerification'  => 'preferred',
				'allowCredentials'  => array(),
			),
		),
	) );
}
add_action( 'wp_ajax_nopriv_xiv_wkn_login_options', 'xiv_wkn_ajax_login_options' );

/**
 * Login: verifikasi assertion lalu set auth cookie.
 */
function xiv_wkn_ajax_login_verify() {
	$data = xiv_wkn_json_body();
	if ( ! xiv_wkn_check_nonce( $data ) ) {
		wp_send_json_error( array( 'message' => __( 'Session tidak valid.', 'xiv-apparel' ) ) );
	}

	$stored = get_transient( 'xiv_wkn_' . sanitize_key( $data['session'] ?? '' ) );
	if ( ! $stored || 'login' !== $stored['type'] ) {
		wp_send_json_error( array( 'message' => __( 'Sesi kedaluwarsa. Coba lagi.', 'xiv-apparel' ) ) );
	}

	$cred_id = sanitize_text_field( $data['id'] ?? '' );
	$index   = xiv_wkn_index();
	$user_id = isset( $index[ $cred_id ] ) ? (int) $index[ $cred_id ] : 0;
	if ( ! $user_id ) {
		wp_send_json_error( array( 'message' => __( 'Perangkat tidak terdaftar.', 'xiv-apparel' ) ) );
	}

	$creds = xiv_wkn_get_credentials( $user_id );
	$cred  = null;
	foreach ( $creds as $c ) {
		if ( hash_equals( $c['id'], $cred_id ) ) {
			$cred = $c;
			break;
		}
	}
	if ( ! $cred ) {
		wp_send_json_error( array( 'message' => __( 'Perangkat tidak terdaftar.', 'xiv-apparel' ) ) );
	}

	$client_data_json = xiv_wkn_b64url_decode( $data['response']['clientDataJSON'] ?? '' );
	$auth_data        = xiv_wkn_b64url_decode( $data['response']['authenticatorData'] ?? '' );
	$signature        = xiv_wkn_b64url_decode( $data['response']['signature'] ?? '' );

	if ( ! xiv_wkn_verify_assertion( $cred, $client_data_json, $auth_data, $signature, $stored ) ) {
		wp_send_json_error( array( 'message' => __( 'Verifikasi gagal. Coba lagi.', 'xiv-apparel' ) ) );
	}

	delete_transient( 'xiv_wkn_' . sanitize_key( $data['session'] ?? '' ) );

	$counter = unpack( 'N', substr( $auth_data, 33, 4 ) )[1];
	foreach ( $creds as &$c ) {
		if ( hash_equals( $c['id'], $cred_id ) ) {
			$c['counter'] = (int) $counter;
			$c['last']    = time();
		}
	}
	unset( $c );
	xiv_wkn_save_credentials( $user_id, $creds );

	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	wp_send_json_success( array(
		'message'  => __( 'Login berhasil.', 'xiv-apparel' ),
		'redirect' => apply_filters( 'xiv_wkn_login_redirect', wc_get_page_permalink( 'myaccount' ), $user_id ),
	) );
}
add_action( 'wp_ajax_nopriv_xiv_wkn_login_verify', 'xiv_wkn_ajax_login_verify' );

/**
 * Hapus kredensial perangkat.
 */
function xiv_wkn_ajax_delete() {
	$data = xiv_wkn_json_body();
	if ( ! xiv_wkn_check_nonce( $data ) || ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Session tidak valid.', 'xiv-apparel' ) ) );
	}

	$cred_id = sanitize_text_field( $data['id'] ?? '' );
	$user_id = get_current_user_id();
	$creds   = xiv_wkn_get_credentials( $user_id );
	$found   = false;

	foreach ( $creds as $i => $c ) {
		if ( hash_equals( $c['id'], $cred_id ) ) {
			array_splice( $creds, $i, 1 );
			$found = true;
			break;
		}
	}
	if ( ! $found ) {
		wp_send_json_error( array( 'message' => __( 'Perangkat tidak ditemukan.', 'xiv-apparel' ) ) );
	}

	xiv_wkn_save_credentials( $user_id, $creds );
	xiv_wkn_unindex( $cred_id );

	wp_send_json_success( array( 'devices' => xiv_wkn_devices_data( $user_id ) ) );
}
add_action( 'wp_ajax_xiv_wkn_delete', 'xiv_wkn_ajax_delete' );

// ---------------------------------------------------------------- UI

/**
 * Localize untuk JS WebAuthn.
 */
function xiv_webauthn_localize() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}
	$path = get_template_directory() . '/assets/dist/js/webauthn.js';
	if ( ! file_exists( $path ) ) {
		return;
	}
	$rp = xiv_wkn_rp();
	wp_localize_script( 'xiv-webauthn', 'XIV_WKN', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'xiv_webauthn_nonce' ),
		'rpId'     => $rp['id'],
		'origin'   => $rp['origin'],
		'loggedIn' => is_user_logged_in(),
		'devices'  => is_user_logged_in() ? xiv_wkn_devices_data( get_current_user_id() ) : array(),
		'i18n'     => array(
			'registering' => __( 'MENDAFTARKAN…', 'xiv-apparel' ),
			'registered'  => __( 'PERANGKAT TERDAFTAR', 'xiv-apparel' ),
			'waiting'     => __( 'TUNGGU…', 'xiv-apparel' ),
			'cancelled'   => __( 'DI BATALKAN', 'xiv-apparel' ),
			'register'    => __( 'DAFTARKAN PERANGKAT INI', 'xiv-apparel' ),
			'delete'      => __( 'HAPUS', 'xiv-apparel' ),
			'confirm'     => __( 'Hapus perangkat ini?', 'xiv-apparel' ),
			'noSupport'   => __( 'Browser/perangkat ini belum mendukung biometrik (butuh HTTPS + browser modern).', 'xiv-apparel' ),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'xiv_webauthn_localize', 30 );

/**
 * Section "Biometrik" di dashboard My Account.
 */
function xiv_wkn_render_dashboard_section() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$devices = xiv_wkn_devices_data( get_current_user_id() );
	?>
	<div class="xiv-wkn-section xiv-mt-8 xiv-border-t xiv-border-xiv-gray-light xiv-pt-6">
		<h3 class="xiv-font-display xiv-text-sm xiv-font-black xiv-uppercase xiv-tracking-widest xiv-mb-1">
			<?php esc_html_e( 'Biometrik (Fingerprint / Face ID)', 'xiv-apparel' ); ?>
		</h3>
		<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-mb-4">
			<?php esc_html_e( 'Login sekali sentuh tanpa password. Setiap perangkat harus didaftarkan terlebih dahulu.', 'xiv-apparel' ); ?>
		</p>
		<button type="button" class="xiv-wkn-register xiv-btn">
			<?php esc_html_e( 'Daftarkan perangkat ini', 'xiv-apparel' ); ?>
		</button>
		<ul class="xiv-wkn-devices xiv-mt-4 xiv-space-y-2 xiv-text-sm">
			<?php if ( empty( $devices ) ) : ?>
				<li class="xiv-wkn-devices-empty xiv-text-xiv-gray-text">
					<?php esc_html_e( 'Belum ada perangkat terdaftar.', 'xiv-apparel' ); ?>
				</li>
			<?php else : ?>
				<?php foreach ( $devices as $d ) : ?>
					<li class="xiv-wkn-device xiv-flex xiv-items-center xiv-justify-between xiv-gap-3 xiv-border xiv-border-xiv-gray-light xiv-px-3 xiv-py-2"
						data-id="<?php echo esc_attr( $d['id'] ); ?>">
						<span>
							<span class="xiv-font-bold"><?php echo esc_html( $d['name'] ); ?></span>
							<span class="xiv-text-xs xiv-text-xiv-gray-text xiv-block"><?php echo esc_html( $d['created'] ); ?></span>
						</span>
						<button type="button" class="xiv-wkn-delete xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black">
							<?php esc_html_e( 'Hapus', 'xiv-apparel' ); ?>
						</button>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
		<p class="xiv-wkn-status xiv-text-xs xiv-font-mono xiv-uppercase xiv-mt-3 xiv-text-xiv-gray-text" aria-live="polite"></p>
	</div>
	<?php
}
