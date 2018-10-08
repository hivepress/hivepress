<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-categories">
	<div class="hp-row">
		<?php foreach ( $categories as $category ) : ?>
		<div class="hp-col-sm-<?php echo esc_attr( $column_width ); ?> hp-col-xs-12">
			<div class="hp-category">
				<div class="hp-category__header">
					<a href="<?php echo esc_url( hivepress()->template->get_url( 'listing__submission_category', [ $category->term_id ] ) ); ?>"><?php echo hivepress()->listing->render_category_image( $category->term_id ); ?></a>
				</div>
				<div class="hp-category__content">
					<h4 class="hp-category__title"><a href="<?php echo esc_url( hivepress()->template->get_url( 'listing__submission_category', [ $category->term_id ] ) ); ?>"><?php echo esc_html( $category->name ); ?></a></h4>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
