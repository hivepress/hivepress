<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-listing__created-date" datetime="<?php echo esc_attr( $listing->get_created_date() ); ?>"><?php printf( esc_html__( 'Added on %s', 'hivepress' ), $listing->display_created_date() ); ?></time>
