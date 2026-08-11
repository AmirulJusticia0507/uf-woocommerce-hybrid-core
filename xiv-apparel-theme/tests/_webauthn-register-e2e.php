<?php
/**
 * E2E WebAuthn REGISTER melalui HTTP (simulasi authenticator).
 * Butuh cookie login dari _wkn-cookie.txt (hasil login E2E sebelumnya).
 */

function r_cbor($v) {
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
			foreach ( $v as $k => $val ) { $out .= r_cbor( $k ) . r_cbor( $val ); }
			return $out;
		}
		$n = count( $v );
		$head = $n < 24 ? chr( 0x80 + $n ) : chr( 0x98 ) . chr( $n );
		$out = $head;
		foreach ( $v as $val ) { $out .= r_cbor( $val ); }
		return $out;
	}
	return '';
}

function r_check( $label, $cond ) {
	echo ( $cond ? 'PASS' : 'FAIL' ) . " - {$label}\n";
	if ( ! $cond ) { $GLOBALS['__fail'] = true; }
}

$GLOBALS['__fail'] = false;
$base   = 'http://wordpress';
$ajax   = str_replace( 'http://localhost:8080', $base, admin_url( 'admin-ajax.php' ) );
$cookie = trim( file_get_contents( __DIR__ . '/_wkn-cookie.txt' ) );
r_check( 'cookie login tersedia', false !== $cookie );

list( $cookie_name, $cookie_val ) = explode( '=', $cookie, 2 );
$_COOKIE[ $cookie_name ] = urldecode( $cookie_val );
$user_id = username_exists( 'wkn_test' );
wp_set_current_user( $user_id );
$nonce = wp_create_nonce( 'xiv_webauthn_nonce' );

// 1. register_options
$res = wp_remote_post( $ajax . '?action=xiv_wkn_register_options', array(
	'timeout' => 15,
	'headers' => array( 'Content-Type' => 'application/json', 'Cookie' => $cookie ),
	'body'    => wp_json_encode( array( 'nonce' => $nonce ) ),
) );
$opt = json_decode( wp_remote_retrieve_body( $res ), true );
r_check( 'register_options success', ! empty( $opt['success'] ) );
r_check( 'register_options challenge', ! empty( $opt['data']['options']['publicKey']['challenge'] ) );
r_check( 'register_options user id', ! empty( $opt['data']['options']['publicKey']['user']['id'] ) );
$session  = $opt['data']['session'];
$rp       = xiv_wkn_rp();

// 2. Bangun attestation
$key = openssl_pkey_new( array( 'private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1' ) );
$det = openssl_pkey_get_details( $key );
$cose_raw = r_cbor( array( 1 => 2, 3 => -7, -1 => 1, -2 => $det['ec']['x'], -3 => $det['ec']['y'] ) );
$cred_id  = random_bytes( 16 );
$auth_data = hash( 'sha256', $rp['id'], true ) . chr( 0x45 ) . pack( 'N', 1 ) . str_repeat( "\x00", 16 ) . pack( 'n', strlen( $cred_id ) ) . $cred_id . $cose_raw;
$att_obj   = r_cbor( array( 'fmt' => 'none', 'attStmt' => array(), 'authData' => $auth_data ) );
$client    = json_encode( array(
	'type'        => 'webauthn.create',
	'challenge'   => $opt['data']['options']['publicKey']['challenge'],
	'origin'      => $rp['origin'],
	'crossOrigin' => false,
) );

// 3. register
$body = array(
	'nonce'   => $nonce,
	'session' => $session,
	'id'      => xiv_wkn_b64url_encode( $cred_id ),
	'rawId'   => xiv_wkn_b64url_encode( $cred_id ),
	'type'    => 'public-key',
	'name'    => 'Android Test',
	'response' => array(
		'clientDataJSON'    => xiv_wkn_b64url_encode( $client ),
		'attestationObject' => xiv_wkn_b64url_encode( $att_obj ),
	),
);
$res = wp_remote_post( $ajax . '?action=xiv_wkn_register', array(
	'timeout' => 15,
	'headers' => array( 'Content-Type' => 'application/json', 'Cookie' => $cookie ),
	'body'    => wp_json_encode( $body ),
) );
$reg = json_decode( wp_remote_retrieve_body( $res ), true );
echo 'DEBUG register body: ' . wp_remote_retrieve_body( $res ) . "\n";
r_check( 'register success', ! empty( $reg['success'] ) );
r_check( 'register devices list', ! empty( $reg['data']['devices'] ) );

// 4. Data tersimpan + terindeks
clean_user_cache( $user_id );
$stored = xiv_wkn_get_credentials( $user_id );
$found  = false;
foreach ( $stored as $c ) {
	if ( hash_equals( $c['id'], xiv_wkn_b64url_encode( $cred_id ) ) ) {
		$found = true;
	}
}
r_check( 'credential tersimpan di meta', $found );
wp_cache_flush();
$idx = xiv_wkn_index();
r_check( 'credential terindeks', isset( $idx[ xiv_wkn_b64url_encode( $cred_id ) ] ) );

// 5. Duplikat ditolak
$res = wp_remote_post( $ajax . '?action=xiv_wkn_register', array(
	'timeout' => 15,
	'headers' => array( 'Content-Type' => 'application/json', 'Cookie' => $cookie ),
	'body'    => wp_json_encode( $body ),
) );
$dup = json_decode( wp_remote_retrieve_body( $res ), true );
r_check( 'duplikat ditolak', empty( $dup['success'] ) );

// 6. Delete
$del = wp_remote_post( $ajax . '?action=xiv_wkn_delete', array(
	'timeout' => 15,
	'headers' => array( 'Content-Type' => 'application/json', 'Cookie' => $cookie ),
	'body'    => wp_json_encode( array( 'nonce' => $nonce, 'id' => xiv_wkn_b64url_encode( $cred_id ) ) ),
) );
$del_json = json_decode( wp_remote_retrieve_body( $del ), true );
r_check( 'delete success', ! empty( $del_json['success'] ) );
wp_cache_flush();
clean_user_cache( $user_id );
$stored = xiv_wkn_get_credentials( $user_id );
$found  = false;
foreach ( $stored as $c ) {
	if ( hash_equals( $c['id'], xiv_wkn_b64url_encode( $cred_id ) ) ) {
		$found = true;
	}
}
r_check( 'credential terhapus', ! $found );

echo $GLOBALS['__fail'] ? "\n=== REGISTER E2E FAIL ===" : "\n=== REGISTER E2E PASS ===";
echo "\n";
