<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<h4 class="hp-vendor__name"><a href="<?php echo esc_url( hivepress()->router->get_url( 'user_view_page', [ 'username' => $user->get_username() ] ) ); ?>"><?php echo esc_html( $user->get_display_name() ); ?></a></h4>