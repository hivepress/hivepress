<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-listing__created-date hp-listing__date">
	<time datetime="<?php echo esc_attr( $listing->get_created_date() ); ?>"><?php echo esc_html( $listing->display_created_date() ); ?></time>
</td>
