<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
if ( is_user_logged_in() ) :
?>
	<ul data-component="menu" class="hp-menu__item header-user-account header-navbar__menu"><?php echo hivepress()->template->get_account_menu_item(); ?></ul>
<?php elseif ( get_option( 'hp_user_enable_registration', true ) ) : ?>
	<a href="#user_login_modal" class="hp-menu__item hp-menu__item--user-login hp-link">
		<i class="hp-icon fas fa-sign-in-alt"></i>
		<span><?php esc_html_e( 'Sign In', 'hivepress' ); ?></span>
	</a>
<?php
endif;
?>
