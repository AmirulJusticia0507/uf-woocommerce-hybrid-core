<?php
/**
 * Test crypto WebAuthn tanpa browser: simulasi authenticator ES256.
 */

function t_cbor($v) {
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
		foreach ( array_keys( $v ) as $k ) {
			if ( $i !== $k ) { $is_map = true; break; }
			$i++;
		}
		if ( $is_map ) {
			$n = count( $v );
			$head = $n < 24 ? chr( 0xa0 + $n ) : chr( 0xb8 ) . chr( $n );
			$out  = $head;
			foreach ( $v as $k => $val ) { $out .= t_cbor( $k ) . t_cbor( $val ); }
			return $out;
		}
		$n = count( $v );
		$head = $n < 24 ? chr( 0x80 + $n ) : chr( 0x98 ) . chr( $n );
		$out  = $head;
		foreach ( $v as $val ) { $out .= t_cbor( $val ); }
		return $out;
	}
	return '';
}

function t_der_to_raw( $der ) {
	$p   = 2; // SEQUENCE header
	$rlen = ord( $der[ $p + 1 ] );
	$r    = substr( $der, $p + 2, $rlen );
	$p    = $p + 2 + $rlen;
	$slen = ord( $der[ $p + 1 ] );
	$s    = substr( $der, $p + 2, $slen );
	return str_pad( ltrim( $r, "\x00" ), 32, "\x00", STR_PAD_LEFT ) .
		str_pad( ltrim( $s, "\x00" ), 32, "\x00", STR_PAD_LEFT );
}

function t_check( $label, $cond ) {
	echo ( $cond ? 'PASS' : 'FAIL' ) . " - {$label}\n";
	if ( ! $cond ) { $GLOBALS['__fail'] = true; }
}

$GLOBALS['__fail'] = false;

// 1. Rp config
$rp = xiv_wkn_rp();
t_check( 'rp id non-empty', ! empty( $rp['id'] ) );
t_check( 'rp origin', strpos( $rp['origin'], '://' ) !== false );

// 2. Generate ES256 keypair
$key = openssl_pkey_new( array( 'private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1' ) );
$det = openssl_pkey_get_details( $key );
$x = $det['ec']['x'];
$y = $det['ec']['y'];
t_check( 'ec point 32 bytes', 32 === strlen( $x ) && 32 === strlen( $y ) );

// 3. COSE key
$cose      = array( 1 => 2, 3 => -7, -1 => 1, -2 => $x, -3 => $y );
$cose_raw  = t_cbor( $cose );
$cose_pem  = xiv_wkn_cose_to_ec_pem( xiv_wkn_cbor_decode( $cose_raw ) );
t_check( 'cose -> pem', false !== $cose_pem );
$pub = openssl_pkey_get_public( $cose_pem );
t_check( 'pem valid', false !== $pub );

// 4. Registration: authData + attestationObject
$cred_id    = random_bytes( 16 );
$rp_hash    = hash( 'sha256', $rp['id'], true );
$auth_data  = $rp_hash . chr( 0x45 ) . pack( 'N', 1 ) . str_repeat( "\x00", 16 ) . pack( 'n', strlen( $cred_id ) ) . $cred_id . $cose_raw;
$att_obj    = t_cbor( array( 'fmt' => 'none', 'attStmt' => array(), 'authData' => $auth_data ) );

$att = xiv_wkn_parse_attestation( xiv_wkn_b64url_encode( $att_obj ) );
t_check( 'parse attestation ok', false !== $att );
t_check( 'credential id match', hash_equals( $att['credential_id'], $cred_id ) );
t_check( 'rpIdHash match', hash_equals( $att['rp_id_hash'], $rp_hash ) );
t_check( 'cose_raw present', ! empty( $att['cose_raw'] ) );

// 5. Assertion verify (login)
$challenge      = random_bytes( 32 );
$client_data    = json_encode( array(
	'type'        => 'webauthn.get',
	'challenge'   => xiv_wkn_b64url_encode( $challenge ),
	'origin'      => $rp['origin'],
	'crossOrigin' => false,
) );
$assert_auth    = $rp_hash . chr( 0x05 ) . pack( 'N', 2 );
$signed         = $assert_auth . hash( 'sha256', $client_data, true );
openssl_sign( $signed, $der_sig, $key, OPENSSL_ALGO_SHA256 );
$raw_sig        = t_der_to_raw( $der_sig );

$cred  = array( 'pk' => base64_encode( $cose_raw ) );
$sess  = array( 'type' => 'login', 'challenge' => xiv_wkn_b64url_encode( $challenge ) );

t_check( 'valid assertion accepted', xiv_wkn_verify_assertion( $cred, $client_data, $assert_auth, $raw_sig, $sess ) );
t_check( 'wrong signature rejected', ! xiv_wkn_verify_assertion( $cred, $client_data, $assert_auth, substr( $raw_sig, 0, 63 ) . "\x00", $sess ) );
t_check( 'wrong challenge rejected', ! xiv_wkn_verify_assertion( $cred, $client_data, $assert_auth, $raw_sig, array( 'challenge' => xiv_wkn_b64url_encode( random_bytes( 32 ) ) ) ) );
t_check( 'wrong origin rejected', ! xiv_wkn_verify_assertion( $cred, json_encode( array(
	'type'        => 'webauthn.get',
	'challenge'   => xiv_wkn_b64url_encode( $challenge ),
	'origin'      => 'http://evil.example',
	'crossOrigin' => false,
) ), $assert_auth, $raw_sig, $sess ) );
t_check( 'wrong rpIdHash rejected', ! xiv_wkn_verify_assertion( $cred, $client_data, str_repeat( "\xaa", 32 ) . substr( $assert_auth, 32 ), $raw_sig, $sess ) );

echo $GLOBALS['__fail'] ? "\n=== FAIL ===" : "\n=== ALL PASS ===";
echo "\n";
