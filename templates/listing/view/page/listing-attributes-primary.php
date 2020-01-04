<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->_get_fields( 'view_page_primary' ) ) :
	?>
	<div class="hp-listing__attributes hp-listing__attributes--primary hp-widget widget">
		<?php foreach ( $listing->_get_fields( 'view_page_primary' ) as $field ) : ?>
			<div class="hp-listing__attribute"><?php echo $field->display(); ?></div>
		<?php endforeach; ?>
	</div>
	<?php
endif;
