<?php
/**
 * XIV_QRIS_Gateway - WooCommerce payment gateway untuk QRIS statis.
 *
 * Verifikasi pembayaran dilakukan manual oleh admin (order on-hold).
 *
 * @package XIV_QRIS
 */

defined( 'ABSPATH' ) || exit;

class XIV_QRIS_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                 = 'xiv_qris';
		$this->method_title       = __( 'QRIS', 'xiv-qris' );
		$this->method_description = __( 'Pembayaran QRIS statis: menampilkan kode QR dan instruksi bayar kepada pelanggan. Order di-hold sampai pembayaran diverifikasi manual oleh admin.', 'xiv-qris' );
		$this->has_fields         = false;
		$this->supports           = array( 'products' );

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled       = $this->get_option( 'enabled' );
		$this->title         = $this->get_option( 'title' );
		$this->description   = $this->get_option( 'description' );
		$this->merchant_name = $this->get_option( 'merchant_name' );
		$this->merchant_id   = $this->get_option( 'merchant_id' );
		$this->qris_image    = $this->get_option( 'qris_image' );
		$this->instruction   = $this->get_option( 'instruction' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	/**
	 * Field pengaturan gateway.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'xiv-qris' ),
				'type'    => 'checkbox',
				'label'   => __( 'Aktifkan pembayaran QRIS', 'xiv-qris' ),
				'default' => 'no',
			),
			'title' => array(
				'title'       => __( 'Title', 'xiv-qris' ),
				'type'        => 'text',
				'description' => __( 'Nama metode yang tampil di checkout.', 'xiv-qris' ),
				'default'     => __( 'QRIS', 'xiv-qris' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'xiv-qris' ),
				'type'        => 'textarea',
				'description' => __( 'Deskripsi di bawah pilihan QRIS saat checkout.', 'xiv-qris' ),
				'default'     => __( 'Bayar dengan scan QRIS dari aplikasi e-wallet atau mobile banking.', 'xiv-qris' ),
				'desc_tip'    => true,
			),
			'merchant_name' => array(
				'title'       => __( 'Merchant Name', 'xiv-qris' ),
				'type'        => 'text',
				'description' => __( 'Nama merchant yang tampil saat QR di-scan.', 'xiv-qris' ),
				'default'     => get_bloginfo( 'name' ),
				'desc_tip'    => true,
			),
			'merchant_id' => array(
				'title'       => __( 'QRIS Merchant ID (PAN)', 'xiv-qris' ),
				'type'        => 'text',
				'description' => __( 'Nomor PAN merchant QRIS statis dari bank / penyedia QRIS.', 'xiv-qris' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'qris_image' => array(
				'title'       => __( 'QR Code Image', 'xiv-qris' ),
				'type'        => 'image',
				'description' => __( 'Unggah gambar QRIS statis atau paste URL gambar. Ditampilkan di step PAYMENT dan halaman terima kasih.', 'xiv-qris' ),
				'default'     => '',
			),
			'instruction' => array(
				'title'       => __( 'Payment Instruction', 'xiv-qris' ),
				'type'        => 'textarea',
				'description' => __( 'Langkah pembayaran untuk pelanggan. Bisa memakai placeholder: {merchant_name}, {order_total}, {order_id}.', 'xiv-qris' ),
				'default'     => "1. Buka aplikasi e-wallet / mobile banking.\n2. Pilih menu QRIS / Scan.\n3. Scan kode QR di atas.\n4. Periksa nama merchant: {merchant_name}\n5. Konfirmasi dan selesaikan pembayaran.\n\nTotal tagihan: {order_total}",
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Render field tipe 'image' (upload QR code via media library).
	 *
	 * @param string $key  Key field.
	 * @param array  $data Data field.
	 * @return string HTML.
	 */
	public function generate_image_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array( 'title' => '', 'description' => '' );
		$data      = wp_parse_args( $data, $defaults );
		$value     = $this->get_option( $key );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $data['title'] ); ?></label>
			</th>
			<td class="forminp forminp-image">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo esc_html( $data['title'] ); ?></span></legend>
					<input
						class="input-text regular-input xiv-qris-image-input"
						type="url"
						id="<?php echo esc_attr( $field_key ); ?>"
						name="<?php echo esc_attr( $field_key ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						placeholder="https://…"
					/>
					<button type="button" class="button xiv-qris-image-upload" data-target="<?php echo esc_attr( $field_key ); ?>">
						<?php esc_html_e( 'Choose Image', 'xiv-qris' ); ?>
					</button>
					<span class="description"><?php echo wp_kses_post( $data['description'] ); ?></span>
				</fieldset>
				<?php if ( $value ) : ?>
					<div class="xiv-qris-preview" style="margin-top:8px;max-width:220px;">
						<img src="<?php echo esc_url( $value ); ?>" alt="QRIS preview" style="width:100%;border:1px solid #dcdcde;border-radius:4px;" />
					</div>
				<?php endif; ?>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Perkaya deskripsi checkout dengan gambar QR.
	 *
	 * @return string
	 */
	public function get_description() {
		$description = parent::get_description();

		if ( $this->qris_image && function_exists( 'is_checkout' ) && is_checkout() ) {
			$description .= '<span class="xiv-qris-checkout-code" style="display:block;margin-top:10px;">';
			$description .= '<img src="' . esc_url( $this->qris_image ) . '" alt="QRIS" style="max-width:180px;width:100%;border:1px solid #e5e5e0;border-radius:4px;" />';
			$description .= '</span>';
		}

		return $description;
	}

	/**
	 * Proses pembayaran: hold order, kurangi stok, kosongkan keranjang.
	 *
	 * @param int $order_id ID order.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			wc_add_notice( __( 'Order tidak ditemukan.', 'xiv-qris' ), 'error' );
			return array( 'result' => 'failure' );
		}

		$order->update_status( 'on-hold', __( 'Menunggu pembayaran QRIS (verifikasi manual).', 'xiv-qris' ) );
		$order->add_order_note(
			sprintf(
				/* translators: %s: QRIS merchant ID (PAN). */
				__( 'Pelanggan memilih pembayaran QRIS. Merchant ID: %s', 'xiv-qris' ),
				$this->merchant_id ? $this->merchant_id : '—'
			)
		);

		wc_reduce_stock_levels( $order_id );

		if ( isset( WC()->cart ) ) {
			WC()->cart->empty_cart();
		}

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Hook halaman terima kasih (thankyou) untuk gateway ini.
	 *
	 * @param int $order_id ID order.
	 */
	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$this->render_qris_info( $order );
	}

	/**
	 * Render kartu QRIS di halaman terima kasih.
	 *
	 * @param WC_Order $order Object order.
	 */
	public function render_qris_info( $order ) {
		$image = $this->get_option( 'qris_image' );
		$name  = $this->get_option( 'merchant_name' );
		$pan   = $this->get_option( 'merchant_id' );
		$instr = (string) $this->get_option( 'instruction' );

		$instr = str_replace(
			array( '{merchant_name}', '{order_total}', '{order_id}' ),
			array( $name, wp_strip_all_tags( wc_price( $order->get_total() ) ), '#' . $order->get_order_number() ),
			$instr
		);
		?>
		<div class="xiv-qris-thankyou" style="max-width:560px;margin:32px 0;padding:24px;border:1px solid #e5e5e0;border-radius:8px;background:#fff;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'QRIS Payment', 'xiv-qris' ); ?></h3>
			<p style="margin-bottom:8px;">
				<strong><?php esc_html_e( 'Total tagihan:', 'xiv-qris' ); ?></strong>
				<?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?>
			</p>

			<?php if ( $image ) : ?>
				<img src="<?php echo esc_url( $image ); ?>" alt="QRIS" style="max-width:200px;width:100%;border:1px solid #e5e5e0;border-radius:4px;" />
			<?php elseif ( $pan ) : ?>
				<p><strong><?php esc_html_e( 'QRIS ID:', 'xiv-qris' ); ?></strong> <code><?php echo esc_html( $pan ); ?></code></p>
			<?php endif; ?>

			<?php if ( $name ) : ?>
				<p style="margin-bottom:8px;"><strong><?php esc_html_e( 'Merchant:', 'xiv-qris' ); ?></strong> <?php echo esc_html( $name ); ?></p>
			<?php endif; ?>

			<?php if ( $instr ) : ?>
				<div class="xiv-qris-instruction"><?php echo wp_kses_post( wpautop( $instr ) ); ?></div>
			<?php endif; ?>

			<p style="margin-bottom:0;">
				<em><?php esc_html_e( 'Pesanan akan dikonfirmasi setelah pembayaran diverifikasi secara manual oleh admin.', 'xiv-qris' ); ?></em>
			</p>
		</div>
		<?php
	}
}
