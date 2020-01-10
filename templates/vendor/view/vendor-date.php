<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-vendor__date" datetime="<?php echo esc_attr( $vendor->get_registered_date() ); ?>"><?php printf( esc_html__( 'Member since %s', 'hivepress' ), $vendor->display_registered_date() ); ?></time>
