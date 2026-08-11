<?php
/**
 * Bilingual front-end layer (English / Bahasa Indonesia).
 *
 * A lightweight string dictionary keyed by the English source string.
 * Usage: `xiv_e( 'ADD TO BAG' )` echoes the current-language string;
 * `xiv_t( $string )` returns it. Language is chosen via `?lang=en|id`,
 * persisted in the `xiv_lang` cookie (default: en).
 *
 * @package XIV_Apparel
 */

defined( 'ABSPATH' ) || exit;

define( 'XIV_LANG_COOKIE', 'xiv_lang' );

/**
 * @return string 'en'|'id'
 */
function xiv_lang() {
	$lang = ( 'id' === xiv_get_option( 'default_lang' ) ) ? 'id' : 'en';
	if ( ! empty( $_COOKIE[ XIV_LANG_COOKIE ] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$candidate = sanitize_key( wp_unslash( $_COOKIE[ XIV_LANG_COOKIE ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( 'id' === $candidate ) {
			$lang = 'id';
		}
	}
	return $lang;
}

/**
 * Handle `?lang=` switch: set cookie and redirect to the clean URL.
 */
function xiv_lang_switch() {
	if ( empty( $_GET['lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$lang = ( 'id' === sanitize_key( wp_unslash( $_GET['lang'] ) ) ) ? 'id' : 'en'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	setcookie( XIV_LANG_COOKIE, $lang, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

	$redirect = remove_query_arg( 'lang' );
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'init', 'xiv_lang_switch', 0 );

/**
 * English → Indonesian dictionary.
 *
 * @return array<string,string>
 */
function xiv_lang_dict() {
	return array(
		// Header.
		'Skip to content'                    => 'Lewati ke konten',
		'Open menu'                          => 'Buka menu',
		'Search'                             => 'Cari',
		'My account'                         => 'Akun saya',
		'Open shopping bag'                  => 'Buka keranjang',
		'Wishlist'                           => 'Wishlist',
		'Search products'                    => 'Cari produk',
		'SEARCH COLLECTION'                  => 'CARI KOLEKSI',
		'GO'                                 => 'GO',
		'Close bag'                          => 'Tutup keranjang',
		'Shopping bag'                       => 'Keranjang belanja',
		'SHOPPING BAG'                       => 'KERANJANG BELANJA',
		// PDP.
		'ADD TO BAG'                         => 'TAMBAH KE KERANJANG',
		'ADDED TO BAG'                       => 'DITAMBAHKAN',
		'SELECT SIZE'                        => 'PILIH UKURAN',
		'UNAVAILABLE'                        => 'TIDAK TERSEDIA',
		'SAVE TO WISHLIST'                   => 'SIMPAN KE WISHLIST',
		'SAVED TO WISHLIST'                  => 'TERSIMPAN DI WISHLIST',
		'FIND YOUR SIZE'                     => 'TEMUKAN UKURAN ANDA',
		'MEASUREMENT GUIDE'                  => 'PANDUAN PENGUKURAN',
		'LOADING…'                           => 'MEMUAT…',
		'Close'                              => 'Tutup',
		'SIZE'                               => 'UKURAN',
		'CHEST'                              => 'DADA',
		'SHOULDER'                           => 'BAHU',
		'WAIST'                              => 'PINGGANG',
		'LENGTH'                             => 'PANJANG',
		'Measurements in centimetres (cm).'  => 'Ukuran dalam sentimeter (cm).',
		'YOU MAY ALSO LIKE'                  => 'ANDA MUNGKIN SUKA',
		'Product not found.'                 => 'Produk tidak ditemukan.',
		// Rating.
		'NO REVIEWS'                         => 'BELUM ADA ULASAN',
		'Rating summary'                     => 'Ringkasan rating',
		// PLP / filters.
		'FILTERS'                            => 'FILTER',
		'Close filters'                      => 'Tutup filter',
		'AVAILABILITY'                       => 'KETERSEDIAAN',
		'All'                                => 'Semua',
		'In Stock'                           => 'Tersedia',
		'CATEGORY'                           => 'KATEGORI',
		'PRICE'                              => 'HARGA',
		'MIN'                                => 'MIN',
		'MAX'                                => 'MAX',
		'APPLY FILTERS'                      => 'TERAPKAN FILTER',
		'Products'                           => 'Produk',
		'Sort products'                      => 'Urutkan produk',
		'DEFAULT'                            => 'DEFAULT',
		'NEWEST'                             => 'TERBARU',
		'PRICE: LOW TO HIGH'                 => 'HARGA: TERENDAH',
		'PRICE: HIGH TO LOW'                 => 'HARGA: TERTINGGI',
		'POPULARITY'                         => 'POPULARITAS',
		'NO PRODUCTS FOUND'                  => 'TIDAK ADA PRODUK',
		'PRODUCTS'                           => 'PRODUK',
		'%s PRODUCTS'                        => '%s PRODUK',
		'PAGE'                               => 'HALAMAN',
		'OF'                                 => 'DARI',
		'SALE'                               => 'DISKON',
		'SOLD OUT'                           => 'HABIS',
		'NO MATCHES'                         => 'TIDAK ADA HASIL',
		'VIEW ALL RESULTS'                   => 'LIHAT SEMUA HASIL',
		'RESULTS FOR'                        => 'HASIL UNTUK',
		'JOIN THE COLLECTION'                => 'GABUNG KOLEKSI',
		'Sign up for early access, private drops and member pricing.' => 'Daftar untuk akses awal, rilis privat dan harga khusus member.',
		'NO THANKS'                          => 'TIDAK, TERIMA KASIH',
		'Close newsletter popup'             => 'Tutup popup newsletter',
		'NO STORES YET'                      => 'BELUM ADA TOKO',
		'FIND OUR STORES'                    => 'TEMUKAN TOKO KAMI',
		'CITY'                               => 'KOTA',
		'PHONE'                              => 'TELEPON',
		'HOURS'                              => 'JAM BUKA',
		'GET DIRECTIONS'                     => 'PETUNJUK ARAH',
		'NOTIFY ME WHEN BACK IN STOCK'       => 'BERITAHU SAYA SAAT STOK ADA',
		'NOTIFY ME'                          => 'BERITAHU SAYA',
		'WE WILL NOTIFY YOU WHEN BACK IN STOCK' => 'KAMI AKAN MEMBERITAHU ANDA SAAT STOK TERSEDIA',
		// Login / OTP / WebAuthn.
		'Login method'                       => 'Metode login',
		'Password'                           => 'Kata Sandi',
		'Phone No. + OTP'                    => 'No. HP + OTP',
		'Phone number (WhatsApp)'            => 'Nomor HP (WhatsApp)',
		'OTP code'                           => 'Kode OTP',
		'Login with OTP'                     => 'Login dengan OTP',
		'Send code'                          => 'Kirim kode',
		'or'                                 => 'atau',
		'Login with fingerprint / Face ID'   => 'Login dengan fingerprint / Face ID',
		'Your bag is empty'                  => 'Keranjang Anda kosong',
		'Looks like you have not added anything yet. Browse the collection and find your next piece.' => 'Sepertinya Anda belum menambahkan apa pun. Jelajahi koleksi dan temukan barang Anda berikutnya.',
		// OTP login.
		'Invalid phone number.'              => 'Nomor HP tidak valid.',
		'Phone number not registered. Please register first.' => 'Nomor HP tidak terdaftar. Silakan register terlebih dahulu.',
		'Code already sent. Try again in 1 minute.' => 'Kode sudah dikirim. Coba lagi dalam 1 menit.',
		'OTP code sent.'                     => 'Kode OTP terkirim.',
		'Invalid phone number or code.'      => 'Nomor HP atau kode tidak valid.',
		'Code not found. Resend code.'       => 'Kode tidak ditemukan. Kirim ulang kode.',
		'Code expired. Resend code.'         => 'Kode kedaluwarsa. Kirim ulang kode.',
		'Too many attempts. Resend code.'    => 'Terlalu banyak percobaan. Kirim ulang kode.',
		'Wrong code. Try again.'             => 'Kode salah. Coba lagi.',
		'Login successful.'                  => 'Login berhasil.',
		'Phone number (WhatsApp) is required.' => 'Nomor HP (WhatsApp) wajib diisi.',
		'Phone number already registered. Please login.' => 'Nomor HP sudah terdaftar. Silakan login.',
		'SENDING…'                           => 'MENGIRIM…',
		'CODE SENT'                          => 'KODE TERKIRIM',
		'VERIFYING…'                         => 'MEMVERIFIKASI…',
		'RESEND'                             => 'KIRIM ULANG',
		// WebAuthn / biometrik.
		'Biometric device'                   => 'Perangkat biometrik',
		'Invalid session.'                   => 'Session tidak valid.',
		'Session expired. Try again.'        => 'Sesi kedaluwarsa. Coba lagi.',
		'Invalid device.'                    => 'Perangkat tidak valid.',
		'Verification failed.'               => 'Verifikasi gagal.',
		'Origin mismatch.'                   => 'Origin tidak cocok.',
		'Challenge mismatch.'                => 'Challenge tidak cocok.',
		'Domain mismatch.'                   => 'Domain tidak cocok.',
		'Device already registered.'         => 'Perangkat sudah terdaftar.',
		'Biometric device successfully registered.' => 'Perangkat biometrik berhasil didaftarkan.',
		'Device not registered.'             => 'Perangkat tidak terdaftar.',
		'Verification failed. Try again.'    => 'Verifikasi gagal. Coba lagi.',
		'Device not found.'                  => 'Perangkat tidak ditemukan.',
		'REGISTERING…'                       => 'MENDAFTARKAN…',
		'DEVICE REGISTERED'                  => 'PERANGKAT TERDAFTAR',
		'WAIT…'                              => 'TUNGGU…',
		'CANCELLED'                          => 'DI BATALKAN',
		'REGISTER THIS DEVICE'               => 'DAFTARKAN PERANGKAT INI',
		'DELETE'                             => 'HAPUS',
		'Delete this device?'                => 'Hapus perangkat ini?',
		'This browser/device does not yet support biometrics (needs HTTPS + modern browser).' => 'Browser/perangkat ini belum mendukung biometrik (butuh HTTPS + browser modern).',
		'Biometrics (Fingerprint / Face ID)' => 'Biometrik (Fingerprint / Face ID)',
		'One-tap login without password. Each device must be registered first.' => 'Login sekali sentuh tanpa password. Setiap perangkat harus didaftarkan terlebih dahulu.',
		'Register this device'               => 'Daftarkan perangkat ini',
		'No devices registered yet.'         => 'Belum ada perangkat terdaftar.',
		'CHAT US'                            => 'CHAT KAMI',
		'QUICK VIEW'                         => 'LIHAT CEPAT',
		'Quick view'                         => 'Lihat cepat',
		'Close quick view'                   => 'Tutup lihat cepat',
		'VIEW FULL DETAILS'                  => 'LIHAT DETAIL LENGKAP',
		'Recently viewed'                    => 'Baru dilihat',
		'RECENTLY VIEWED'                    => 'BARU DILIHAT',
		'Compare'                            => 'Bandingkan',
		'Toggle compare'                     => 'Bandingkan produk',
		'NOTHING TO COMPARE YET'             => 'BELUM ADA PRODUK DIBANDINGKAN',
		'BROWSE PRODUCTS'                    => 'TELUSURI PRODUK',
		'PRODUCT'                            => 'PRODUK',
		'NAME'                               => 'NAMA',
		'RATING'                             => 'RATING',
		'DETAILS'                            => 'DETAIL',
		'ATTRIBUTES'                         => 'ATRIBUT',
		'AVAILABILITY'                       => 'KETERSEDIAAN',
		'IN STOCK'                           => 'TERSEDIA',
		'ACTION'                             => 'AKSI',
		'REMOVE'                             => 'HAPUS',
		'QUICK ADD'                          => 'TAMBAH CEPAT',
		'Your bag is limited to %d items.'   => 'Keranjangmu dibatasi maksimal %d item.',
		// Cart drawer.
		'YOUR BAG IS EMPTY'                  => 'KERANJANG ANDA KOSONG',
		'Quantity'                           => 'Jumlah',
		'Remove item'                        => 'Hapus item',
		'Remove'                             => 'Hapus',
		'SUBTOTAL'                           => 'SUBTOTAL',
		'CHECKOUT'                           => 'CHECKOUT',
		'VIEW FULL BAG'                      => 'LIHAT KERANJANG',
		'Size'                               => 'Ukuran',
		'ALL'                                => 'SEMUA',
		'Choose language'                    => 'Pilih bahasa',
		'Nothing found.'                     => 'Tidak ditemukan.',
		'Invalid product.'                   => 'Produk tidak valid.',
		'Category missing.'                  => 'Kategori tidak ada.',
		'Invalid email.'                     => 'Email tidak valid.',
		'Subscribed.'                        => 'Berlangganan.',
		// Front page.
		'New collection'                     => 'Koleksi baru',
		'XIV COLLECTIONS 23-24'              => 'KOLEKSI XIV 23-24',
		'NEW THIS WEEK'                      => 'BARU MINGGU INI',
		'SHOP NEW'                           => 'BELANJA BARU',
		'Categories'                         => 'Kategori',
		'New arrivals'                       => 'Produk baru',
		'NEW ARRIVALS'                       => 'PRODUK BARU',
		'VIEW ALL'                           => 'LIHAT SEMUA',
		// Wishlist.
		'YOUR WISHLIST IS EMPTY'             => 'WISHLIST ANDA KOSONG',
		'BROWSE PRODUCTS'                    => 'LIHAT PRODUK',
		'Remove from wishlist'               => 'Hapus dari wishlist',
		'Toggle wishlist'                    => 'Ubah wishlist',
		// Footer.
		'Footer'                             => 'Footer',
		'NEWSLETTER'                         => 'NEWSLETTER',
		'Email address'                      => 'Alamat email',
		'YOUR EMAIL'                         => 'EMAIL ANDA',
		'SUBSCRIBE'                          => 'BERLANGGANAN',
		'MRP incl. of all taxes'             => 'Harga sudah termasuk pajak',
		// Misc.
		'LOADING'                            => 'MEMUAT',
		'SOMETHING WENT WRONG'               => 'TERJADI KESALAHAN',
	);
}

/**
 * Translate a string.
 *
 * @param string $string English source string.
 * @return string
 */
function xiv_t( $string ) {
	if ( 'id' !== xiv_lang() ) {
		return $string;
	}
	$dict = xiv_lang_dict();
	return isset( $dict[ $string ] ) ? $dict[ $string ] : $string;
}

/**
 * Echo an escaped translated string.
 *
 * @param string $string English source string.
 */
function xiv_e( $string ) {
	echo esc_html( xiv_t( $string ) );
}

/**
 * Return an escaped (attribute) translated string.
 *
 * @param string $string English source string.
 * @return string
 */
function xiv_et( $string ) {
	return esc_attr( xiv_t( $string ) );
}

/**
 * Render the EN / ID language switcher.
 */
function xiv_language_switcher() {
	$current = xiv_lang();
	$options = array( 'en' => 'EN', 'id' => 'ID' );
	?>
	<div class="xiv-flex xiv-items-center xiv-gap-0.5 xiv-text-[11px] xiv-font-bold xiv-uppercase xiv-tracking-widest" aria-label="<?php echo esc_attr( xiv_t( 'Choose language' ) ); ?>">
		<?php foreach ( $options as $code => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'lang', $code ) ); ?>" class="xiv-px-1 <?php echo $current === $code ? 'xiv-text-xiv-black' : 'xiv-text-xiv-gray-text hover:xiv-text-xiv-black'; ?>"><?php echo esc_html( $label ); ?></a>
			<?php if ( 'en' === $code ) : ?><span class="xiv-text-xiv-gray-light xiv-select-none">/</span><?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php
}
