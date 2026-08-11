<?php
/**
 * E2E WebAuthn login melalui HTTP: simulasi authenticator ES256 penuh.
 */

function e_cbor($v) {
	if ( is_int( $v ) ) {
		if ( $v >= 0 ) {
			if ( $v < 24 ) return chr( $v );
			if ( $v < 256 ) return chr( 0x18 ) . chr( $v );
			return chr( 0x19 ) . chr( $v >> 8 ) . chr( $v & 0xff );
		}
		$n = -1 - $v;
		if ( $n < 24 ) return chr( 0x20 + $n );
		return chr( 0x38 ) . chr( $n );
	}
	if ( is_string( $v ) ) {
		$len = strlen( $v );
		$head = $len < 24 ? chr( 0x40 + $len ) : chr( 0x58 ) . chr( $len );
		return $head . $v;
	}
	if ( is_array( $v ) ) {
		$is_map = false;
		$i = 0;
		foreach ( array_keys( $v ) as $k ) { if ( $i !== $k ) { $is_map = true; break; } $i++; }
		if ( $is_map ) {
			$n = count( $v );
			$head = $n < 24 ? chr( 0xa0 + $n ) : chr( 0xb8 ) . chr( $n );
			$out = $head;
			foreach ( $v as $k => $val ) { $out .= e_cbor( $k ) . e_cbor( $val ); }
			return $out;
		}
		$n = count( $v );
		$head = $n < 24 ? chr( 0x80 + $n ) : chr( 0x98 ) . chr( $n );
		$out = $head;
		foreach ( $v as $val ) { $out .= e_cbor( $val ); }
		return $out;
	}
	return '';
}

function e_der_to_raw( $der ) {
	$p = 2;
	$rlen = ord( $der[ $p + 1 ] );
	$r = substr( $der, $p + 2, $rlen );
	$p = $p + 2 + $rlen;
	$slen = ord( $der[ $p + 1 ] );
	$s = substr( $der, $p + 2, $slen );
	return str_pad( ltrim( $r, "\x00" ), 32, "\x00", STR_PAD_LEFT ) .
		str_pad( ltrim( $s, "\x00" ), 32, "\x00", STR_PAD_LEFT );
}

function e_check( $label, $cond ) {
	echo ( $cond ? 'PASS' : 'FAIL' ) . " - {$label}\n";
	if ( ! $cond ) { $GLOBALS['__fail'] = true; }
}

$GLOBALS['__fail'] = false;
$base = 'http://wordpress';
$ajax = str_replace( 'http://localhost:8080', $base, admin_url( 'admin-ajax.php' ) );

// 1. Pastikan user test ada
$user_id = username_exists( 'wkn_test' );
if ( ! $user_id ) {
	$user_id = wp_create_user( 'wkn_test', 'wkn_pass_123', 'wkntest@example.com' );
}
e_check( 'user test ada', ! empty( $user_id ) );

// 2. Keypair ES256 + credential terdaftar (reset dulu biar idempotent)
foreach ( xiv_wkn_get_credentials( $user_id ) as $old ) {
	xiv_wkn_unindex( $old['id'] );
}
xiv_wkn_save_credentials( $user_id, array() );

$key = openssl_pkey_new( array( 'private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1' ) );
$det = openssl_pkey_get_details( $key );
$x   = $det['ec']['x'];
$y   = $det['ec']['y'];
$cose_raw = e_cbor( array( 1 => 2, 3 => -7, -1 => 1, -2 => $x, -3 => $y ) );
$cred_id  = random_bytes( 16 );
$cred_id_b64 = xiv_wkn_b64url_encode( $cred_id );

$creds = xiv_wkn_get_credentials( $user_id );
$creds[] = array( 'id' => $cred_id_b64, 'pk' => base64_encode( $cose_raw ), 'counter' => 0, 'name' => 'Test', 'created' => time(), 'last' => time() );
xiv_wkn_save_credentials( $user_id, $creds );
xiv_wkn_index_user( $cred_id_b64, $user_id );
e_check( 'credential tersimpan', count( xiv_wkn_get_credentials( $user_id ) ) >= 1 );

// 3. Login options (guest nonce)
$nonce  = wp_create_nonce( 'xiv_webauthn_nonce' );
$res    = wp_remote_post( $ajax . '?action=xiv_wkn_login_options', array(
	'timeout' => 15,
	'headers' => array( 'Content-Type' => 'application/json' ),
	'body'    => wp_json_encode( array( 'nonce' => $nonce ) ),
) );
$opt = json_decode( wp_remote_retrieve_body( $res ), true );
e_check( 'login_options success', ! empty( $opt['success'] ) );
e_check( 'login_options challenge', ! empty( $opt['data']['options']['publicKey']['challenge'] ) );
$session = $opt['data']['session'];
$rp_id   = $opt['data']['options']['publicKey']['rpId'];

// 4. Bangun assertion
$challenge    = xiv_wkn_b64url_decode( $opt['data']['options']['publicKey']['challenge'] );
$rp           = xiv_wkn_rp();
$client_data  = json_encode( array(
	'type'        => 'webauthn.get',
	'challenge'   => $opt['data']['options']['publicKey']['challenge'],
	'origin'      => $rp['origin'],
	'crossOrigin' => false,
) );
$auth_data    = hash( 'sha256', $rp_id, true ) . chr( 0x05 ) . pack( 'N', 4 );
openssl_sign( $auth_data . hash( 'sha256', $client_data, true ), $der_sig, $key, OPENSSL_ALGO_SHA256 );
$raw_sig = e_der_to_raw( $der_sig );

// 5. Kirim login_verify
$body = array(
	'nonce'    => $nonce,
	'session'  => $session,
	'id'       => $cred_id_b64,
	'type'     => 'public-key',
	'rawId'    => $cred_id_b64,
	'response' => array(
		'clientDataJSON'    => xiv_wkn_b64url_encode( $client_data ),
		'authenticatorData' => xiv_wkn_b64url_encode( $auth_data ),
		'signature'         => xiv_wkn_b64url_encode( $raw_sig ),
	),
);
$res = wp_remote_post( $ajax . '?action=xiv_wkn_login_verify', array(
	'timeout' => 15,
	'headers' => array( 'Content-Type' => 'application/json' ),
	'body'    => wp_json_encode( $body ),
) );
$ver = json_decode( wp_remote_retrieve_body( $res ), true );
e_check( 'login_verify success', ! empty( $ver['success'] ) );
e_check( 'login_verify redirect', ! empty( $ver['data']['redirect'] ) );
clean_user_cache( $user_id );
$stored_creds = xiv_wkn_get_credentials( $user_id );
$counter = 0;
foreach ( $stored_creds as $c ) {
	if ( hash_equals( $c['id'], $cred_id_b64 ) ) {
		$counter = (int) $c['counter'];
	}
}
e_check( 'counter ter-update', $counter === 4 );

// 6. Auth cookie harus sudah diset -> cek my-account
$headers = wp_remote_retrieve_headers( $res );
$cookies = isset( $headers['set-cookie'] ) ? (array) $headers['set-cookie'] : array();
echo 'DEBUG set-cookie headers: ' . ( $cookies ? implode( ' | ', $cookies ) : '(none)' ) . "\n";
$cookie_line = array();
foreach ( $cookies as $c ) {
	$parts = explode( ';', $c );
	if ( false !== strpos( $parts[0], 'wordpress_logged_in_' ) ) {
		file_put_contents( __DIR__ . '/_wkn-cookie.txt', $parts[0] );
	}
	$cookie_line[] = $parts[0];
}
echo 'DEBUG cookie file: ' . ( file_exists( __DIR__ . '/_wkn-cookie.txt' ) ? 'written' : 'missing' ) . "\n";
$page = wp_remote_get( str_replace( 'http://localhost:8080', $base, wc_get_page_permalink( 'myaccount' ) ), array(
	'redirection' => 0,
	'timeout'     => 15,
	'headers'     => array( 'Cookie' => implode( '; ', $cookie_line ) ),
) );
$html = wp_remote_retrieve_body( $page );
$loc  = wp_remote_retrieve_header( $page, 'location' );
echo 'DEBUG page code = ' . wp_remote_retrieve_response_code( $page ) . ' len = ' . strlen( $html ) . ' loc = ' . $loc . "\n";
e_check( 'my-account menampilkan dashboard (logged in)', strpos( $html, 'Biometrik' ) !== false );

echo $GLOBALS['__fail'] ? "\n=== FAIL ===" : "\n=== E2E PASS ===";
echo "\n";
