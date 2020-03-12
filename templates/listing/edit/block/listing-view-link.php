<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_status() === 'publish' ) :
	?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'listing_view_page', [ 'listing_id' => $listing->get_id() ] ) ); ?>" title="<?php esc_attr_e( 'View', 'hivepress' ); ?>" class="hp-listing__action hp-listing__action--view hp-link"><i class="hp-icon fas fa-eye"></i></a>
	<?php
endif;
