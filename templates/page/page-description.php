<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( isset( $page_description ) ) :
	?>
	<div class="hp-page__description">
		<?php echo $page_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Term description sanitized by WordPress on save, then formatted by the_content filter like post content. ?>
	</div>
	<?php
endif;
