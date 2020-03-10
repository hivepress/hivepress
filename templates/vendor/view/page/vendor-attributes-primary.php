<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $vendor->_get_fields( 'view_page_primary' ) ) :
	?>
	<div class="hp-vendor__attributes hp-vendor__attributes--primary hp-widget widget">
		<?php
		foreach ( $vendor->_get_fields( 'view_page_primary' ) as $field ) :
			if ( ! is_null( $field->get_value() ) ) :
				?>
				<div class="hp-vendor__attribute"><?php echo $field->display(); ?></div>
				<?php
			endif;
		endforeach;
		?>
	</div>
	<?php
endif;
