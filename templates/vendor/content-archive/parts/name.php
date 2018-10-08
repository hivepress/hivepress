<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<h4 class="hp-vendor__name"><a href="<?php echo esc_url( hivepress()->template->get_url( 'listing__vendor', [ $vendor->user_login ] ) ); ?>"><?php echo esc_html( $vendor->display_name ); ?></a></h4>
