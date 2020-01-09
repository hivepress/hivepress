<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-listing__title">
	<?php if ( $listing->get_status() === 'pending' ) : ?>
		<span><?php echo esc_html( $listing->get_title() ); ?></span>
	<?php else : ?>
		<a href="<?php echo esc_url( hivepress()->router->get_url( 'listing_edit_page', [ 'listing_id' => $listing->get_id() ] ) ); ?>" class="hp-link"><i class="hp-icon fas fa-edit"></i><span><?php echo esc_html( $listing->get_title() ); ?></span></a>
	<?php endif; ?>
</td>
