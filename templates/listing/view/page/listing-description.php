<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_description() ) :
	?>
	<div class="hp-listing__description"><?php echo $listing->display_description(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized with wp_kses() on save, rendered via the_content filter. ?></div>
	<?php
endif;
