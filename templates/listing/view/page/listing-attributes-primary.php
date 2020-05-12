<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->_get_fields( 'view_page_primary' ) ) :
	?>
	<div class="hp-listing__attributes hp-listing__attributes--primary hp-widget widget">
		<?php
		foreach ( $listing->_get_fields( 'view_page_primary' ) as $field ) :
			if ( ! is_null( $field->get_value() ) ) :
				?>
				<div class="hp-listing__attribute hp-listing__attribute--<?php echo esc_attr( hivepress()->helper->sanitize_slug( $field->get_name() ) ); ?>">
					<?php echo $field->display(); ?>
				</div>
				<?php
			endif;
		endforeach;
		?>
	</div>
	<?php
endif;
