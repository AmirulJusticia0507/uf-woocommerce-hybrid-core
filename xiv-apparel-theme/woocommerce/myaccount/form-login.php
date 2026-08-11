<?php
/**
 * Login + Register forms (My Account, WC 9.9+ menggabungkan keduanya).
 *
 * @package XIV_Apparel
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );
?>

<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

<div class="u-columns col2-set xiv-grid xiv-grid-cols-1 md:xiv-grid-cols-2 xiv-gap-12 xiv-mt-6" id="customer_login">

	<div class="u-column1 col-1">

<?php endif; ?>

		<h2 class="xiv-font-display xiv-text-2xl xiv-font-black xiv-uppercase xiv-tracking-widest xiv-mb-6">
			<?php esc_html_e( 'Login', 'woocommerce' ); ?>
		</h2>

		<div class="xiv-otp-tabs xiv-flex xiv-border xiv-border-xiv-gray-light xiv-mb-6" role="tablist" aria-label="<?php esc_attr_e( 'Login method', 'xiv-apparel' ); ?>">
			<button type="button" class="xiv-otp-tab xiv-otp-tab--active xiv-flex-1 xiv-bg-transparent xiv-border-0 xiv-py-3 xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-cursor-pointer xiv-text-white xiv-bg-xiv-black" data-panel="password" role="tab" aria-selected="true">
				<?php esc_html_e( 'Password', 'xiv-apparel' ); ?>
			</button>
			<button type="button" class="xiv-otp-tab xiv-flex-1 xiv-bg-transparent xiv-border-0 xiv-py-3 xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-cursor-pointer xiv-text-xiv-gray-text" data-panel="otp" role="tab" aria-selected="false">
				<?php esc_html_e( 'No. HP + OTP', 'xiv-apparel' ); ?>
			</button>
		</div>

		<div id="xiv-otp-panel" class="xiv-otp-panel xiv-hidden">
			<form class="xiv-otp-form woocommerce-form xiv-mb-8" method="post" novalidate>
				<?php do_action( 'xiv_otp_form_start' ); ?>

				<p class="woocommerce-form-row form-row xiv-mb-4">
					<label for="xiv_otp_phone" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
						<?php esc_html_e( 'Nomor HP (WhatsApp)', 'xiv-apparel' ); ?>
						<span class="required" aria-hidden="true">*</span>
					</label>
					<input type="tel" class="woocommerce-Input input-text xiv-input" name="phone" id="xiv_otp_phone" autocomplete="tel"
						inputmode="tel" placeholder="08xxxxxxxxxx" required aria-required="true" />
				</p>

				<div class="xiv-otp-code-row xiv-hidden xiv-mb-4">
					<p class="woocommerce-form-row form-row xiv-mb-2">
						<label for="xiv_otp_code" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
							<?php esc_html_e( 'Kode OTP', 'xiv-apparel' ); ?>
							<span class="required" aria-hidden="true">*</span>
						</label>
						<input type="text" class="woocommerce-Input input-text xiv-input xiv-otp-code" name="code" id="xiv_otp_code"
							inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="000000" required aria-required="true" />
					</p>
					<button type="submit" class="woocommerce-button button xiv-btn xiv-w-full"
						name="verify_otp" value="1">
						<?php esc_html_e( 'Login dengan OTP', 'xiv-apparel' ); ?>
					</button>
				</div>

				<p class="xiv-otp-status xiv-text-xs xiv-font-mono xiv-uppercase xiv-mb-3 xiv-text-xiv-gray-text" aria-live="polite"></p>

				<button type="button" class="xiv-otp-send woocommerce-button button xiv-btn xiv-btn--ghost xiv-w-full"
					data-text="<?php esc_attr_e( 'Kirim kode', 'xiv-apparel' ); ?>">
					<?php esc_html_e( 'Kirim kode', 'xiv-apparel' ); ?>
				</button>

				<?php do_action( 'xiv_otp_form_end' ); ?>
			</form>
		</div>

		<form class="woocommerce-form woocommerce-form-login login xiv-mb-8" method="post" novalidate>

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide xiv-mb-4">
				<label for="username" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
					<?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>
					<span class="required" aria-hidden="true">*</span>
				</label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text xiv-input" name="username" id="username" autocomplete="username"
					value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide xiv-mb-4">
				<label for="password" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
					<?php esc_html_e( 'Password', 'woocommerce' ); ?>
					<span class="required" aria-hidden="true">*</span>
				</label>
				<input class="woocommerce-Input woocommerce-Input--text input-text xiv-input" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<p class="form-row xiv-flex xiv-items-center xiv-gap-4 xiv-mb-4">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme xiv-flex xiv-items-center xiv-gap-2 xiv-text-xs xiv-font-medium xiv-cursor-pointer">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
					<span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
				</label>
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit"
					class="woocommerce-button button woocommerce-form-login__submit xiv-btn xiv-ml-auto"
					name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
					<?php esc_html_e( 'Log in', 'woocommerce' ); ?>
				</button>
			</p>

			<p class="woocommerce-LostPassword lost_password xiv-text-xs">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black">
					<?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?>
				</a>
			</p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>

		<div class="xiv-wkn-login xiv-hidden xiv-mb-8">
			<div class="xiv-flex xiv-items-center xiv-gap-3 xiv-mb-3">
				<span class="xiv-h-px xiv-flex-1 xiv-bg-xiv-gray-light"></span>
				<span class="xiv-text-[10px] xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text">
					<?php esc_html_e( 'atau', 'xiv-apparel' ); ?>
				</span>
				<span class="xiv-h-px xiv-flex-1 xiv-bg-xiv-gray-light"></span>
			</div>
			<button type="button" class="xiv-wkn-login-btn xiv-btn xiv-btn--ghost xiv-w-full">
				<?php esc_html_e( 'Login dengan fingerprint / Face ID', 'xiv-apparel' ); ?>
			</button>
			<p class="xiv-wkn-status xiv-text-xs xiv-font-mono xiv-uppercase xiv-mt-2 xiv-text-xiv-gray-text" aria-live="polite"></p>
		</div>

<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

	</div>

	<div class="u-column2 col-2">

		<h2 class="xiv-font-display xiv-text-2xl xiv-font-black xiv-uppercase xiv-tracking-widest xiv-mb-6">
			<?php esc_html_e( 'Register', 'woocommerce' ); ?>
		</h2>

		<form method="post" class="woocommerce-form woocommerce-form-register register xiv-mb-8" <?php do_action( 'woocommerce_register_form_tag' ); ?>>

			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide xiv-mb-4">
					<label for="reg_username" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
						<?php esc_html_e( 'Username', 'woocommerce' ); ?>
						<span class="required" aria-hidden="true">*</span>
					</label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text xiv-input" name="username" id="reg_username" autocomplete="username"
						value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
				</p>
			<?php endif; ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide xiv-mb-4">
				<label for="reg_email" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
					<?php esc_html_e( 'Email address', 'woocommerce' ); ?>
					<span class="required" aria-hidden="true">*</span>
				</label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text xiv-input" name="email" id="reg_email" autocomplete="email"
					value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide xiv-mb-4">
				<label for="reg_billing_phone" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
					<?php esc_html_e( 'Nomor HP (WhatsApp)', 'xiv-apparel' ); ?>
					<span class="required" aria-hidden="true">*</span>
				</label>
				<input type="tel" class="woocommerce-Input woocommerce-Input--text input-text xiv-input" name="billing_phone" id="reg_billing_phone"
					autocomplete="tel" inputmode="tel" placeholder="08xxxxxxxxxx"
					value="<?php echo ( ! empty( $_POST['billing_phone'] ) ) ? esc_attr( wp_unslash( $_POST['billing_phone'] ) ) : ''; ?>" required aria-required="true" />
			</p>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide xiv-mb-4">
					<label for="reg_password" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
						<?php esc_html_e( 'Password', 'woocommerce' ); ?>
						<span class="required" aria-hidden="true">*</span>
					</label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text xiv-input" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
				</p>
			<?php else : ?>
				<p class="xiv-text-sm xiv-text-xiv-gray-text xiv-mb-4"><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
			<?php endif; ?>

			<?php do_action( 'woocommerce_register_form' ); ?>

			<p class="woocommerce-form-row form-row xiv-mb-4">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit"
					class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit xiv-btn"
					name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
					<?php esc_html_e( 'Register', 'woocommerce' ); ?>
				</button>
			</p>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>

	</div>

</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
