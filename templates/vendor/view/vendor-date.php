<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
// todo rename?
?>
<time class="hp-vendor__date" datetime="<?php echo esc_attr( get_the_time( 'Y-m-d' ) ); ?>"><?php printf( esc_html__( 'Member since %s', 'hivepress' ), get_the_date() ); ?></time>
