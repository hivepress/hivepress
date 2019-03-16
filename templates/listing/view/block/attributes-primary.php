<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_todos() ) :
	?>
	<div class="hp-listing__attributes hp-listing__attributes--primary">
		<?php foreach ( $listing->get_todos() as $todo ) : ?>
			<div class="hp-listing__attribute"><?php echo esc_html( $todo->get_value() ); ?></div>
		<?php endforeach; ?>
	</div>
	<?php
endif;
