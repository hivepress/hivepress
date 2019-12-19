<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<h4 class="hp-vendor__name"><a href="<?php echo esc_url( hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor->get_id() ] ) ); ?>"><?php echo esc_html( $vendor->get_name() ); ?></a></h4>
