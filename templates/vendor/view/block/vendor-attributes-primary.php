<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-vendor__attributes hp-vendor__attributes--primary">
	<?php
	foreach ( $vendor->_get_fields( 'view_block_primary' ) as $field ) :
		if ( ! is_null( $field->get_value() ) ) :
			?>
			<div class="hp-vendor__attribute hp-vendor__attribute--<?php echo esc_attr( hivepress()->helper->sanitize_slug( $field->get_name() ) ); ?>">
				<?php echo $field->display(); ?>
			</div>
			<?php
		endif;
	endforeach;
	?>
</div>
