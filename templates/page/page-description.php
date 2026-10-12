<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( isset( $page_description ) ) :
	?>
	<div class="hp-page__description">
		<?php echo $page_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Term description rendered via the_content filter. ?>
	</div>
	<?php
endif;
