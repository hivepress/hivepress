<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! empty( $categories ) ) :
	?>
	<div class="hp-categories hp-content hp-block hp-block--listing-categories">
		<div class="hp-row">
			<?php foreach ( $categories as $category ) : ?>
			<div class="hp-col-sm-<?php echo esc_attr( $column_width ); ?> hp-col-xs-12">
				<?php echo hivepress()->template->render_template( 'archive_category', [ 'category' => $category ] ); ?>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php else : ?>
	<div class="hp-no-results">
		<p><?php esc_html_e( 'No categories found.', 'hivepress' ); ?></p>
	</div>
	<?php
endif;
