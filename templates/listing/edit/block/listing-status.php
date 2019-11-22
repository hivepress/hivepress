<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-listing__status hp-listing__status--<?php echo esc_attr( $listing->get_status() ); ?>">
	<?php if ( $listing->get_status() === 'pending' ) : ?>
		<span><?php echo esc_html_x( 'Pending', 'listing', 'hivepress' ); ?></span>
	<?php elseif ( $listing->get_status() === 'draft' ) : ?>
		<span><?php echo esc_html_x( 'Hidden', 'listing', 'hivepress' ); ?></span>
	<?php endif; ?>
</td>
