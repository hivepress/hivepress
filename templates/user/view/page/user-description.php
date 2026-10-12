<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $user->get_description() ) :
	?>
	<div class="hp-vendor__description"><?php echo $user->display_description(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized with wp_kses() on save, rendered via the_content filter. ?></div>
	<?php
endif;
