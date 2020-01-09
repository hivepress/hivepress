<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-listing__date" datetime="<?php echo esc_attr( $listing->get_date_created() ); ?>"><?php printf( esc_html__( 'Added on %s', 'hivepress' ), $listing->display_date_created() ); ?></time>
