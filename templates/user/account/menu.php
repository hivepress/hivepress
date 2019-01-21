<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-widget widget widget_nav_menu">
	<?php echo hivepress()->template->render_menu( 'user_account' ); ?>
	<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="hp-logout-link"><i class="hp-icon fas fa-sign-out-alt"></i><span><?php esc_html_e( 'Sign Out', 'hivepress' ); ?></span></a>
</div>
