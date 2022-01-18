<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap hp-page">
	<h1 class="hp-page__title"><?php echo esc_html( hivepress()->get_name() ); ?> <span>/</span> <?php esc_html_e( 'Tools', 'hivepress' ); ?></h1>
	<?php if ( $tabs ) : ?>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $active_tab => $tab_name ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=hp_tools&tab=' . $active_tab ) ); ?>" class="nav-tab <?php if ( $active_tab === $current_tab ) : ?>nav-tab-active<?php endif; ?>"><?php echo esc_html( $tab_name ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php if ( $system_info_items ) : ?>
		<table class="form-table hp-form" data-model="listing" data-id="495">
			<tbody>
				<?php
					foreach ($system_info_items as $system_info_item => $system_info_item_value) {
				 ?>
				<tr class="hp-form__field hp-form__field--textarea">
					<th scope="row">
						<div>
							<label class="hp-field__label">
								<span><?php esc_html_e($system_info_item); ?></span>
							</label>
						</div>
					</th>
					<td>
						<p class="hp-field"><?php esc_html_e($system_info_item_value); ?></p>
					</td>
				</tr>
				<?php
					}
				 ?>
			</tbody>
		</table>
	<?php endif; ?>
	<?php endif; ?>
	<form method="POST" action="<?php echo esc_url( admin_url( 'options.php?tab=' . $current_tab ) ); ?>" class="hp-form" data-component="form">
		<?php
		do_settings_sections( 'hp_tools' );
		settings_fields( 'hp_tools' );
		submit_button();
		?>
	</form>
</div>
