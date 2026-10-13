<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_description() ) :
	?>
	<div class="hp-listing__description"><?php echo $listing->display_description(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized on save (HTML is stripped in front-end forms and filtered by WordPress in the back-end), then formatted by the_content filter like post content. ?></div>
	<?php
endif;
