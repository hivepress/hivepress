<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php printf( esc_html__( '%s Add-ons', 'hivepress' ), HP_CORE_NAME ); ?></h1>
	<hr class="wp-header-end">
	<?php if ( count( $tabs ) > 1 ) : ?>
	<ul class="subsubsub">
		<?php foreach ( $tabs as $active_tab => $tab_info ) : ?>
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=hp_addons&addon_status=' . $active_tab ) ); ?>" <?php if ( $active_tab === $current_tab ) : ?>class="current"<?php endif; ?>>
				<?php echo esc_html( $tab_info['name'] ); ?>
				<span class="count">(<?php echo esc_html( $tab_info['count'] ); ?>)</span>
			</a>
			<?php if ( end( $tabs ) !== $tab_info ) : ?> |<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
	<div class="wp-list-table widefat plugin-install">
		<?php foreach ( $addons as $addon ) : ?>
		<div class="plugin-card">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<?php echo esc_html( $addon->name ); ?>
						<img src="<?php echo esc_url( end( $addon->icons ) ); ?>" class="plugin-icon" alt="<?php echo esc_attr( $addon->name ); ?>">
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons">
						<li>
							<?php if ( 'install' === $addon->status ) : ?>
								<a href="<?php echo esc_url( $addon->url ); ?>" class="install-now button"><?php esc_html_e( 'Install Now', 'hivepress' ); ?></a>
							<?php elseif ( 'update_available' === $addon->status ) : ?>
								<a href="<?php echo esc_url( $addon->url ); ?>" class="update-now button"><?php esc_html_e( 'Update Now', 'hivepress' ); ?></a>
							<?php elseif ( 'activate' === $addon->status ) : ?>
								<a href="<?php echo esc_url( $addon->url ); ?>" class="button activate-now"><?php esc_html_e( 'Activate', 'hivepress' ); ?></a>
							<?php else : ?>
								<button type="button" class="button button-disabled" disabled="disabled"><?php esc_html_e( 'Active', 'hivepress' ); ?></button>
							<?php endif; ?>
						</li>
					</ul>
				</div>
				<div class="desc column-description">
					<p><?php echo esc_html( $addon->short_description ); ?></p>
					<p class="authors"><cite><?php printf( esc_html__( 'By %s', 'hivepress' ), $addon->author ); ?></cite></p>
				</div>
			</div>
			<div class="plugin-card-bottom">
				<div class="column-downloaded"><?php printf( esc_html__( 'Version %s', 'hivepress' ), $addon->version ); ?></div>
				<div class="column-compatibility">
					<?php if ( version_compare( substr( get_bloginfo( 'version' ), 0, strlen( $addon->requires ) ), $addon->requires ) >= 0 ) : ?>
					<span class="compatibility-compatible"><?php echo sanitize_html( __( '<strong>Compatible</strong> with your version of WordPress', 'hivepress' ) ); ?></span>
					<?php else : ?>
					<span class="compatibility-incompatible"><?php echo sanitize_html( __( '<strong>Incompatible</strong> with your version of WordPress', 'hivepress' ) ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
		<div class="clear"></div>
	</div>
</div>
