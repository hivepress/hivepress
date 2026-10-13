<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( is_rtl() ) :
	?>
	<div dir="auto"><?php echo $email->get_body(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Email HTML from the default or admin-edited email template, with token values from data sanitized on save. ?></div>
	<?php
else :
	echo $email->get_body(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Email HTML from the default or admin-edited email template, with token values from data sanitized on save.
endif;
