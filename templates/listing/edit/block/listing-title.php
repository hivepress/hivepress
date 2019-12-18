<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<td class="hp-listing__title">
	<?php
	if ( $listing->get_status() === 'pending' ) :
		the_title();
	else :
		?>
		<a href="<?php echo esc_url( hivepress()->router->get_url( 'edit_listing', [ 'listing_id' => $listing->get_id() ] ) ); ?>" class="hp-link"><i class="hp-icon fas fa-edit"></i><span><?php the_title(); ?></span></a>
	<?php endif; ?>
</td>
