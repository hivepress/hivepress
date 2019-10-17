<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-listing__status hp-listing__status--<?php echo esc_attr( $listing->get_status() ); ?>">
	<?php if ( $listing->get_status() === 'pending' ) : ?>
		<span><?php esc_html_e( 'Pending', 'hivepress' ); ?></span>
	<?php elseif ( $listing->get_status() === 'draft' ) : ?>
		<span><?php esc_html_e( 'Hidden', 'hivepress' ); ?></span>
	<?php endif; ?>
</td>
