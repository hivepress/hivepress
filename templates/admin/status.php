<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap hp-page">
	<h1 class="hp-page__title"><?php echo esc_html( hivepress()->get_name() ); ?> <span>/</span> <?php esc_html_e( 'Status', 'hivepress' ); ?></h1>
	<hr class="wp-header-end">
	<table class="form-table" cellspacing="0">
		<?php foreach ( $status['sections'] as $section ) : ?>
			<tbody>
				<tr>
					<th colspan="2"><h2><?php echo hivepress()->helper->get_array_value( $section, 'label' ); ?></h2></th>
				</tr>
				<?php foreach ( hivepress()->helper->get_array_value( $section, 'values' ) as $value ) : ?>
				<tr>
					<?php if ( hivepress()->helper->get_array_value( $value, 'url' ) ) : ?>
					<th><a href="<?php echo esc_attr( hivepress()->helper->get_array_value( $value, 'url' ) ); ?>" target="_blank"><?php echo hivepress()->helper->get_array_value( $value, 'label' ); ?></a></th>
					<?php else : ?>
					<th><?php echo hivepress()->helper->get_array_value( $value, 'label' ); ?></th>
					<?php endif; ?>
					<td>
					<?php if ( in_array( hivepress()->helper->get_array_value( $value, 'value' ), [ '+', '-' ] ) ) : ?>
					<span class="dashicons dashicons-<?php echo '+' === hivepress()->helper->get_array_value( $value, 'value' ) ? 'yes' : 'no'; ?>"></span>
					<?php else : ?>
						<?php echo hivepress()->helper->get_array_value( $value, 'value' ); ?>
					</td>
					<?php endif; ?>
				</tr>
				<?php endforeach; ?>
			</tbody>
		<?php endforeach; ?>
	<tbody>
	<tr>
		<th colspan="2">
			<p><?php echo esc_html__( 'Please click on the field to copy the text for support.', 'hivepress' ); ?></p>
			<form>
				<textarea rows="10" class="large-text" data-component="support-status-table" readonly="readonly"><?php echo $status['content']; ?></textarea>
			</form>
		</th>
	</tr>
</tbody>
</table>
</div>
