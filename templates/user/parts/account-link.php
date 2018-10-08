<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) :
	?>
	<a href="<?php echo esc_url( hivepress()->template->get_url( 'user__account' ) ); ?>" class="hp-menu__link"><i class="hp-icon fas fa-user"></i><?php esc_html_e( 'My Account', 'hivepress' ); ?></a>
<?php else : ?>
	<a href="#hp-user-login" class="hp-menu__link hp-js-link" data-type="popup"><i class="hp-icon fas fa-sign-in-alt"></i><?php esc_html_e( 'Sign In', 'hivepress' ); ?></a>
	<?php
endif;
