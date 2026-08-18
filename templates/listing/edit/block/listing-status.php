<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$expired_time = $listing->get_expired_time();

if ( 'publish' === $listing->get_status() && $expired_time ) :
	$status = 'pending';
	$text   = sprintf( hivepress()->translator->get_string( 'expires_on' ), date_i18n( get_option( 'date_format' ), $expired_time ) );
elseif ( 'draft' === $listing->get_status() && $expired_time && $expired_time < time() ) :
	$status = 'trash';
	$text   = sprintf( hivepress()->translator->get_string( 'expired_on' ), date_i18n( get_option( 'date_format' ), $expired_time ) );
else :
	$status = $listing->get_status();
	$text   = $listing->display_status();
endif;
?>
<td class="hp-listing__status hp-status hp-status--<?php echo esc_attr( $status ); ?>">
	<?php if ( $text ) : ?>
		<span><?php echo esc_html( $text ); ?></span>
	<?php endif; ?>
</td>
