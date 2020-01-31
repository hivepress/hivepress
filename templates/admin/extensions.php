<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use HivePress\Helpers as hp;
?>
<div class="wrap hp-page">
	<h1 class="hp-page__title"><?php echo esc_html( hivepress()->get_name() ); ?> <span>/</span> <?php esc_html_e( 'Extensions', 'hivepress' ); ?></h1>
	<hr class="wp-header-end">
	<?php if ( count( $tabs ) > 1 ) : ?>
		<ul class="subsubsub">
			<?php foreach ( $tabs as $active_tab => $tab_args ) : ?>
				<li>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=hp_extensions&tab=' . $active_tab ) ); ?>" <?php if ( $active_tab === $current_tab ) : ?>class="current"<?php endif; ?>>
						<?php echo esc_html( $tab_args['name'] ); ?>
						<span class="count">(<?php echo esc_html( $tab_args['count'] ); ?>)</span>
					</a>
					<?php if ( hp\get_last_array_value( $tabs ) !== $tab_args ) : ?> |<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<div class="wp-list-table widefat plugin-install">
		<?php foreach ( $extensions as $extension ) : ?>
			<div class="plugin-card">
				<div class="plugin-card-top">
					<div class="name column-name">
						<h3>
							<?php echo esc_html( $extension['name'] ); ?>
							<img src="<?php echo esc_url( hp\get_last_array_value( $extension['icons'] ) ); ?>" class="plugin-icon" alt="<?php echo esc_attr( $extension['name'] ); ?>">
						</h3>
					</div>
					<div class="action-links">
						<ul class="plugin-action-buttons">
							<li>
								<?php if ( 'install' === $extension['status'] ) : ?>
									<a href="<?php echo esc_url( $extension['url'] ); ?>" class="install-now button"><?php esc_html_e( 'Install Now' ); ?></a>
								<?php elseif ( 'update_available' === $extension['status'] ) : ?>
									<a href="<?php echo esc_url( $extension['url'] ); ?>" class="update-now button"><?php esc_html_e( 'Update Now' ); ?></a>
								<?php elseif ( 'activate' === $extension['status'] ) : ?>
									<a href="<?php echo esc_url( $extension['url'] ); ?>" class="button activate-now"><?php esc_html_e( 'Activate' ); ?></a>
								<?php else : ?>
									<button type="button" class="button button-disabled" disabled="disabled"><?php echo esc_html_x( 'Active', 'plugin' ); ?></button>
								<?php endif; ?>
							</li>
						</ul>
					</div>
					<div class="desc column-description">
						<p><?php echo esc_html( $extension['short_description'] ); ?></p>
						<p class="authors"><cite><?php printf( esc_html__( 'By %s' ), $extension['author'] ); ?></cite></p>
					</div>
				</div>
				<div class="plugin-card-bottom">
					<div class="column-downloaded"><?php printf( esc_html__( 'Version %s' ), $extension['version'] ); ?></div>
					<div class="column-compatibility">
						<?php if ( version_compare( substr( get_bloginfo( 'version' ), 0, strlen( $extension['requires'] ) ), $extension['requires'] ) >= 0 ) : ?>
							<span class="compatibility-compatible"><?php echo hp\sanitize_html( __( '<strong>Compatible</strong> with your version of WordPress' ) ); ?></span>
						<?php else : ?>
							<span class="compatibility-incompatible"><?php echo hp\sanitize_html( __( '<strong>Incompatible</strong> with your version of WordPress' ) ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="clear"></div>
	</div>
</div>
