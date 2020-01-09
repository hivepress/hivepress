<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
// todo rename?
?>
<time class="hp-vendor__date" datetime="<?php echo esc_attr( $vendor->get_date_registered() ); ?>"><?php printf( esc_html__( 'Member since %s', 'hivepress' ), $vendor->display_date_registered() ); ?></time>
