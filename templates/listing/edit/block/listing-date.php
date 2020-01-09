<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-listing__date">
	<time datetime="<?php echo esc_attr( $listing->get_date_created() ); ?>"><?php echo esc_html( $listing->display_date_created() ); ?></time>
</td>
