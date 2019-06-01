<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing_query->have_posts() ) :
	?>
	<div class="hp-listings hp-content hp-block hp-block--listings">
		<div class="hp-row">
			<?php
			while ( $listing_query->have_posts() ) :
				$listing_query->the_post();
				?>
				<div class="hp-col-sm-<?php echo esc_attr( $column_width ); ?> hp-col-xs-12">
					<?php echo hivepress()->template->render_template( 'archive_listing' ); ?>
				</div>
			<?php endwhile; ?>
		</div>
	</div>
<?php else : ?>
	<div class="hp-no-results">
		<p><?php esc_html_e( 'No listings found.', 'hivepress' ); ?></p>
	</div>
	<?php
endif;
