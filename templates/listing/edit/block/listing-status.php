<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-listing__status hp-status hp-status--<?php echo esc_attr( $listing->get_status() ); ?>">
	<?php if ( $listing->display_status() ) : ?>
		<span><?php echo esc_html( $listing->display_status() ); ?></span>
	<?php endif; ?>
</td>
