<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_status() === 'publish' ) :
	?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'listing_view_page', [ 'listing_id' => $listing->get_id() ] ) ); ?>" target="_blank" title="<?php esc_attr_e( 'View', 'hivepress' ); ?>" class="hp-listing__action hp-listing__action--listing-view hp-link"><i class="hp-icon fas fa-external-link-alt"></i></a>
	<?php
endif;
