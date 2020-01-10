<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->_get_fields( 'view_block_secondary' ) ) :
	?>
	<div class="hp-listing__attributes hp-listing__attributes--secondary">
		<div class="hp-row">
			<?php
			foreach ( $listing->_get_fields( 'view_block_secondary' ) as $field ) :
				if ( ! is_null( $field->get_value() ) ) :
					?>
					<div class="hp-col-lg-6 hp-col-xs-12">
						<div class="hp-listing__attribute">
							<strong><?php echo esc_html( $field->get_label() ); ?>:</strong>
							<span><?php echo $field->display(); ?></span>
						</div>
					</div>
					<?php
				endif;
			endforeach;
			?>
		</div>
	</div>
	<?php
endif;
