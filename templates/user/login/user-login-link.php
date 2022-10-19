<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) :
	?>
	<div class="hp-menu__item hp-menu__item--user-account" data-component="account-menu">
		<a href="#" class="hp-link">
			<i class="hp-icon fas fa-user"></i>
			<span><?php echo esc_html( hivepress()->request->get_user()->get_username() ); ?></span>
			<?php if ( hivepress()->request->get_context( 'notice_count' ) ) : ?>
				<small><?php echo esc_html( hivepress()->request->get_context( 'notice_count' ) ); ?></small>
			<?php endif; ?>
		</a>
		<?php echo ( new \HivePress\Menus\User_Account() )->render(); ?>
	</div>
<?php elseif ( get_option( 'hp_user_enable_registration', true ) ) : ?>
	<a href="#user_login_modal" class="hp-menu__item hp-menu__item--user-login hp-link">
		<i class="hp-icon fas fa-sign-in-alt"></i>
		<span><?php esc_html_e( 'Sign In', 'hivepress' ); ?></span>
	</a>
	<?php
endif;
