<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $modal_title ) :
	?>
	<h3 class="hp-modal__title"><?php echo esc_html( $modal_title ); ?></h3>
	<?php
endif;
