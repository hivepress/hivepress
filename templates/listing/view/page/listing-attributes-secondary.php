<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_display_fields( 'view_page_secondary' ) ) :
	?>
	<div class="hp-listing__attributes hp-listing__attributes--secondary">
		<div class="hp-row">
			<?php foreach ( $listing->get_display_fields( 'view_page_secondary' ) as $field ) : ?>
				<div class="hp-col-lg-6 hp-col-xs-12">
					<div class="hp-listing__attribute">
						<strong><?php echo esc_html( $field->get_label() ); ?>:</strong>
						<span><?php echo $field->get_value(); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
endif;
