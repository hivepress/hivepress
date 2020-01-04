<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing__image">
	<a href="<?php the_permalink(); ?>">
		<?php
		if ( has_post_thumbnail() ) :
			the_post_thumbnail( 'hp_landscape_small' );
		else :
			?>
			<img src="<?php echo esc_url( hivepress()->get_url() . '/assets/images/placeholders/image-landscape.svg' ); ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>">
		<?php endif; ?>
	</a>
</div>
