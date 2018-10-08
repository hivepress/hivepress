<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-vendor__date" datetime="<?php echo date( 'Y-m-d', strtotime( $vendor->user_registered ) ); ?>"><?php printf( esc_html__( 'Member since %s', 'hivepress' ), date_i18n( get_option( 'date_format' ), strtotime( $vendor->user_registered ) ) ); ?></time>
