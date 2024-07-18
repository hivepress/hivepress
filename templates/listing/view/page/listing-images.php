<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_images__id() ) :
	$image_urls = [];

	if ( get_option( 'hp_listing_enable_image_zoom' ) ) :
		$image_urls = $listing->get_images__url( 'large' );
	endif;
	?>
	<div class="hp-listing__images" data-component="carousel-slider">
		<?php
		foreach ( $listing->get_images() as $image_index => $image ) :
			$image_url = hivepress()->helper->get_array_value( $image_urls, $image_index, '' );

			if ( strpos( $image->get_mime_type(), 'video' ) === 0 ) :
				?>
				<video data-zoom="<?php echo esc_url( $image_url ); ?>" controls>
					<source src="<?php echo esc_url( $image->get_url() ); ?>#t=0.001" type="<?php echo esc_attr( $image->get_mime_type() ); ?>">
				</video>
			<?php else : ?>
				<img src="<?php echo esc_url( $image->get_url( 'hp_landscape_large' ) ); ?>" data-zoom="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>" loading="lazy">
				<?php
			endif;
		endforeach;
		?>
	</div>
	<?php
endif;
