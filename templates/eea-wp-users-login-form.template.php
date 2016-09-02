<?php
/**
 * This is the template for EE WPUsers addon login form.
 */

$registration_url = ! EE_Registry::instance()->CFG->addons->user_integration->registration_page ? wp_registration_url() : EE_Registry::instance()->CFG->addons->user_integration->registration_page;
?>
<div id="ee-login-form-container" style="display:none">
	<form name="ee_login_form" class="ee-login-form" action="" method="post">
		<p>
			<label for="log"><?php _e('Username') ?><br />
			<input type="text" name="log"  class="user_login input" value="" size="20" /></label>
		</p>
		<p>
			<label for="pwd"><?php _e('Password') ?><br />
			<input type="password" name="pwd" class="user_pass input" value="" size="20" /></label>
		</p>
		<?php do_action( 'login_form' ); ?>
		<p class="forgetmenot"><label for="rememberme"><input name="rememberme" type="checkbox" class="rememberme" value="forever" /> <?php esc_attr_e('Remember Me'); ?></label></p>
		<p class="submit">
			<?php if ( get_option( 'users_can_register' ) ) : ?>
				<a class="wp_register_link" href="<?php echo $registration_url; ?>"><?php _e( 'Register', 'event_espresso' ); ?></a>
			<?php endif; ?>
			<input type="submit" name="wp-submit" class="button button-primary button-large wp-submit" value="<?php esc_attr_e('Log In'); ?>" />
		</p>
	</form>
	<p><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" title="<?php esc_attr_e( 'Password Lost and Found' ); ?>"><?php _e( 'Lost your password?' ); ?></a></p>
	<div class="login_error_notice"></div>
</div>
