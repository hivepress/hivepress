<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$last_seen = hivepress()->cache->get_user_cache( $vendor->get_user__id(), 'last_seen' );

if ( $last_seen && $last_seen < time() ) : ?>
    <time class="hp-vendor__registered-date hp-vendor__date hp-meta" datetime="<?php echo esc_attr( $last_seen ); ?>">
        <?php
        /* translators: %s: date. */
        printf( esc_html__( 'Last seen %s ago', 'hivepress' ), human_time_diff( time(), $last_seen ) );
        ?>
    </time>
    <?php
endif;
