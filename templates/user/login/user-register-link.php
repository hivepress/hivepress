<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_option( 'hp_user_enable_registration', true ) ) :
	?>
	<p class="hp-form__action hp-form__action--user-register"><?php esc_html_e( 'Don\'t have an account yet?', 'hivepress' ); ?> <a href="#user_register_modal"><?php esc_html_e( 'Register', 'hivepress' ); ?></a></p>
	<?php
endif;
