<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-listing__created-date hp-listing__date hp-meta" datetime="<?php echo esc_attr( $listing->get_created_date() ); ?>">
	<?php printf( hivepress()->translator->get_string( 'added_on_date' ), $listing->display_created_date() ); ?>
</time>
