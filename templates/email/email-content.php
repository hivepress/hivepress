<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( is_rtl() ) :
	?>
	<div dir="auto"><?php echo $email->get_body(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Email HTML rendered from sanitized content. ?></div>
	<?php
else :
	echo $email->get_body(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Email HTML rendered from sanitized content.
endif;
