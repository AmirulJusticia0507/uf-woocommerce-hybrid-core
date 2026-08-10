<?php
/**
 * Edit account details form.
 *
 * @package XIV_Apparel
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$user = wp_get_current_user();
?>

<form action="" method="post" class="xiv-max-w-xl xiv-mt-4" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>

	<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

	<p class="woocommerce-form-row form-row form-row-first xiv-mb-4">
		<label for="account_first_name" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
			<?php esc_html_e( 'First name', 'woocommerce' ); ?>
			<span class="required" aria-hidden="true">*</span>
		</label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text xiv-input" name="account_first_name" id="account_first_name" autocomplete="given-name"
			value="<?php echo esc_attr( $user->first_name ); ?>" />
	</p>

	<p class="woocommerce-form-row form-row form-row-last xiv-mb-4">
		<label for="account_last_name" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
			<?php esc_html_e( 'Last name', 'woocommerce' ); ?>
			<span class="required" aria-hidden="true">*</span>
		</label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text xiv-input" name="account_last_name" id="account_last_name" autocomplete="family-name"
			value="<?php echo esc_attr( $user->last_name ); ?>" />
	</p>

	<p class="woocommerce-form-row form-row form-row-wide xiv-mb-4">
		<label for="account_display_name" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
			<?php esc_html_e( 'Display name', 'woocommerce' ); ?>
			<span class="required" aria-hidden="true">*</span>
		</label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text xiv-input" name="account_display_name" id="account_display_name"
			value="<?php echo esc_attr( $user->display_name ); ?>" />
		<span class="xiv-text-xs xiv-text-xiv-gray-text xiv-block xiv-mt-1">
			<?php esc_html_e( 'This will be how your name will be displayed in your account section and in reviews', 'woocommerce' ); ?>
		</span>
	</p>

	<p class="woocommerce-form-row form-row form-row-wide xiv-mb-4">
		<label for="account_email" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
			<?php esc_html_e( 'Email address', 'woocommerce' ); ?>
			<span class="required" aria-hidden="true">*</span>
		</label>
		<input type="email" class="woocommerce-Input woocommerce-Input--email input-text xiv-input" name="account_email" id="account_email" autocomplete="email"
			value="<?php echo esc_attr( $user->user_email ); ?>" />
	</p>

	<fieldset class="xiv-border xiv-border-xiv-gray-light xiv-p-5 xiv-mb-6">
		<legend class="xiv-text-xs xiv-font-black xiv-uppercase xiv-tracking-widest xiv-px-2">
			<?php esc_html_e( 'Password change', 'woocommerce' ); ?>
		</legend>

		<p class="woocommerce-form-row form-row form-row-wide xiv-mb-4">
			<label for="password_current" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
				<?php esc_html_e( 'Current password (leave blank to leave unchanged)', 'woocommerce' ); ?>
			</label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text xiv-input" name="password_current" id="password_current" autocomplete="off" />
		</p>

		<p class="woocommerce-form-row form-row form-row-wide xiv-mb-4">
			<label for="password_1" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
				<?php esc_html_e( 'New password (leave blank to leave unchanged)', 'woocommerce' ); ?>
			</label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text xiv-input" name="password_1" id="password_1" autocomplete="off" />
		</p>

		<p class="woocommerce-form-row form-row form-row-wide">
			<label for="password_2" class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-mb-1">
				<?php esc_html_e( 'Confirm new password', 'woocommerce' ); ?>
			</label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text xiv-input" name="password_2" id="password_2" autocomplete="off" />
		</p>
	</fieldset>

	<?php do_action( 'woocommerce_edit_account_form' ); ?>

	<p class="xiv-mt-6">
		<button type="submit" class="woocommerce-Button button xiv-btn" name="save_account"
			value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>">
			<?php esc_html_e( 'Save changes', 'woocommerce' ); ?>
		</button>
		<?php wp_nonce_field( 'save_account', 'save-account-nonce' ); ?>
		<input type="hidden" name="action" value="save_account" />
	</p>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>
