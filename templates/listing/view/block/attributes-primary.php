<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_display_fields( 'view_block_primary' ) ) :
	?>
	<div class="hp-listing__attributes hp-listing__attributes--primary">
		<?php foreach ( $listing->get_display_fields( 'view_block_primary' ) as $field ) : ?>
			<div class="hp-listing__attribute"><?php echo esc_html( $field->get_value() ); ?></div>
		<?php endforeach; ?>
	</div>
	<?php
endif;
