<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap hp-page">
	<h1 class="hp-page__title"><?php echo esc_html( hivepress()->get_name() ); ?> <span>/</span> <?php esc_html_e( 'Settings', 'hivepress' ); ?></h1>
	<?php if ( $tabs ) : ?>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $active_tab => $tab_name ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=hp_settings&tab=' . $active_tab ) ); ?>" class="nav-tab <?php if ( $active_tab === $current_tab ) : ?>nav-tab-active<?php endif; ?>"><?php echo esc_html( $tab_name ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<form method="POST" action="<?php echo esc_url( admin_url( 'options.php?tab=' . $current_tab ) ); ?>" class="hp-form hp-form--table" data-component="form">
		<?php
		do_settings_sections( 'hp_settings' );
		settings_fields( 'hp_settings' );
		submit_button();
		?>
	</form>
</div>
