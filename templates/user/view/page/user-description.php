<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $user->get_description() ) :
	?>
	<div class="hp-vendor__description"><?php echo $user->display_description(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- User text escaped with esc_html(), then formatted by the_content filter like post content. ?></div>
	<?php
endif;
