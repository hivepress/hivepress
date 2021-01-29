<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-listing__categories">
	<?php echo esc_html( $listing->display_categories() ); ?>
</td>
