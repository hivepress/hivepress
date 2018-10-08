<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-listing__date" datetime="<?php the_time( 'Y-m-d' ); ?>"><?php printf( esc_html__( 'Added on %s', 'hivepress' ), get_the_date() ); ?></time>
