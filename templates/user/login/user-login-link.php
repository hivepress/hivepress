<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) :
	?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'user_account_page' ) ); ?>" class="hp-menu__item hp-link"><i class="hp-icon fas fa-user"></i><span><?php esc_html_e( 'My Account', 'hivepress' ); ?></span></a>
<?php else : ?>
	<a href="#user_login_modal" class="hp-menu__item hp-link"><i class="hp-icon fas fa-sign-in-alt"></i><span><?php esc_html_e( 'Sign In', 'hivepress' ); ?></span></a>
	<?php
endif;
