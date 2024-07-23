<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-listing__created-date hp-listing__date">
    <div>
        <?php echo esc_html__( 'Created', 'hivepress' ); ?>:
    </div>
    <div>
        <time datetime="<?php echo esc_attr( $listing->get_created_date() ); ?>"><?php echo esc_html( $listing->display_created_date() ); ?></time>
    </div>

	<?php if ( $listing->get_expired_time() ) : ?>
        <div>
			<?php echo esc_html__( 'Expires on', 'hivepress' ); ?>:
        </div>
        <div>
            <time datetime="<?php echo esc_attr( $listing->get_expired_time() ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), $listing->get_expired_time() ) ); ?></time>
        </div>
	<?php endif; ?>

	<?php if ( $listing->get_featured_time() ) : ?>
        <div>
            <?php echo esc_html__( 'Featured until', 'hivepress' ); ?>:
        </div>
        <div>
            <time datetime="<?php echo esc_attr( $listing->get_featured_time() ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), $listing->get_featured_time() ) ); ?></time>
        </div>
	<?php endif; ?>
</td>
