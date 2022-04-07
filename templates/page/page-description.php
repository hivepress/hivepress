<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( isset( $page_description ) ) :
	?>
	<div class="hp-page__description">
		<?php echo $page_description; ?>
	</div>
	<?php
endif;
