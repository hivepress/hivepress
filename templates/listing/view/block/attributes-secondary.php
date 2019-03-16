<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_todos() ) :
	?>
	<div class="hp-listing__attributes hp-listing__attributes--secondary">
		<div class="hp-row">
			<?php foreach ( $listing::get_fields() as $todo ) : ?>
				<div class="hp-col-lg-6 hp-col-xs-12">
					<div class="hp-listing__attribute">
						<strong><?php echo esc_html( $todo->get_label() ); ?>:</strong>
						<span><?php echo esc_html( $todo->get_value() ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
endif;
