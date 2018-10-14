<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php printf( esc_html__( '%s Add-ons', 'hivepress' ), HP_CORE_NAME ); ?></h1>
	<hr class="wp-header-end">
	<ul class="subsubsub">
		<li><a href="todo" class="current">All <span class="count">(4)</span></a> |</li>
		<li><a href="#">Installed <span class="count">(4)</span></a> |</li>
		<li><a href="#">Active <span class="count">(4)</span></a></li>
	</ul>
	<div class="wp-list-table widefat plugin-install">
		<?php foreach ( $plugins as $plugin ) : ?>
		<div class="plugin-card">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<?php echo esc_html( $plugin->name ); ?>
						<img src="<?php echo esc_url( end( $plugin->icons ) ); ?>" class="plugin-icon" alt="<?php echo esc_attr( $plugin->name ); ?>">
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons">
						<li>
							<?php if ( 'install' === $plugin->status ) : ?>
								<a href="<?php echo esc_url( $plugin->url ); ?>" class="install-now button"><?php esc_html_e( 'Install Now', 'hivepress' ); ?></a>
							<?php elseif ( 'update_available' === $plugin->status ) : ?>
								<a href="<?php echo esc_url( $plugin->url ); ?>" class="update-now button"><?php esc_html_e( 'Update Now', 'hivepress' ); ?></a>
							<?php elseif ( 'activate' === $plugin->status ) : ?>
								<a href="<?php echo esc_url( $plugin->url ); ?>" class="button activate-now"><?php esc_html_e( 'Activate', 'hivepress' ); ?></a>
							<?php else : ?>
								<button type="button" class="button button-disabled" disabled="disabled"><?php esc_html_e( 'Active', 'hivepress' ); ?></button>
							<?php endif; ?>
						</li>
					</ul>
				</div>
				<div class="desc column-description">
					<p><?php echo esc_html( $plugin->short_description ); ?></p>
					<p class="authors"><cite><?php printf( esc_html__( 'By %s', 'hivepress' ), $plugin->author ); ?></cite></p>
				</div>
			</div>
			<div class="plugin-card-bottom">
				<div class="column-downloaded"><?php printf( esc_html__( 'Version %s', 'hivepress' ), $plugin->version ); ?></div>
				<div class="column-compatibility">
					<?php if ( version_compare( substr( get_bloginfo( 'version' ), 0, strlen( $plugin->requires ) ), $plugin->requires ) >= 0 ) : ?>
					<span class="compatibility-compatible"><?php echo hp_sanitize_html( __( '<strong>Compatible</strong> with your version of WordPress', 'hivepress' ) ); ?></span>
					<?php else : ?>
					<span class="compatibility-incompatible"><?php echo hp_sanitize_html( __( '<strong>Incompatible</strong> with your version of WordPress', 'hivepress' ) ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
