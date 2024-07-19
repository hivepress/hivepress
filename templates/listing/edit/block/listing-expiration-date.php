<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-listing__created-date hp-listing__date">
	<?php if ( $listing->get_expired_time() ) : ?>
		<time datetime="<?php echo esc_attr( $listing->get_expired_time() ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), $listing->get_expired_time() ) ); ?></time>
	<?php endif; ?>
</td>
