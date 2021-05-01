<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $vendor->_get_fields( 'view_page_secondary' ) ) :
	?>
	<div class="hp-vendor__attributes hp-vendor__attributes--secondary">
		<div class="hp-row">
			<?php
			foreach ( $vendor->_get_fields( 'view_page_secondary' ) as $field ) :
				if ( ! is_null( $field->get_value() ) ) :
					?>
					<div class="hp-col-lg-6 hp-col-xs-12">
						<div class="hp-vendor__attribute hp-vendor__attribute--<?php echo esc_attr( $field->get_slug() ); ?>">
							<?php echo $field->display(); ?>
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
