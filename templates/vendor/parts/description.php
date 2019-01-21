<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( '' !== $vendor->description ) :
	?>
	<div class="hp-vendor__description">
		<?php echo esc_html( $vendor->description ); ?>
	</div>
	<?php
endif;
