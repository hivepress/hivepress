<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) :
	?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'user_account_page' ) ); ?>" class="hp-menu__item hp-menu__item--user-account hp-link">
		<i class="hp-icon fas fa-user"></i>
		<span><?php echo esc_html( hivepress()->request->get_user()->get_username() ); ?></span>
		<?php if ( hivepress()->request->get_context( 'notice_count' ) ) : ?>
			<small><?php echo esc_html( hivepress()->request->get_context( 'notice_count' ) ); ?></small>
		<?php endif; ?>
	</a>
<?php elseif ( get_option( 'hp_user_enable_registration', true ) ) : ?>
	<a href="#user_login_modal" class="hp-menu__item hp-menu__item--user-login hp-link">
		<i class="hp-icon fas fa-sign-in-alt"></i>
		<span><?php esc_html_e( 'Sign In', 'hivepress' ); ?></span>
	</a>
	<?php
endif;
