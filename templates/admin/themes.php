<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap hp-page">
	<h1 class="hp-page__title"><?php echo esc_html( hivepress()->get_name() ); ?> <span>/</span> <?php esc_html_e( 'Themes', 'hivepress' ); ?></h1>
	<hr class="wp-header-end">
	<div class="theme-browser rendered">
		<div class="themes wp-clearfix">
			<?php foreach ( $themes as $theme ) : ?>
				<div class="hp-theme theme" tabindex="0">
					<div class="theme-screenshot">
						<img src="<?php echo esc_url( $theme['image_url'] ); ?>" alt="<?php echo esc_attr( $theme['name'] ); ?>" loading="lazy">
					</div>
					<a href="<?php echo esc_url( $theme['buy_url'] ); ?>" target="_blank" class="hp-theme__link more-details"><?php esc_html_e( 'Theme Details', 'hivepress' ); ?></a>
					<div class="hp-theme__content theme-id-container">
						<h2 class="hp-theme__name theme-name"><?php echo esc_html( $theme['name'] ); ?></h2>
						<strong class="hp-theme__price"><?php echo esc_html( hivepress()->helper->get_array_value( $theme, 'price', hivepress()->translator->get_string( 'free' ) ) ); ?></strong>
						<div class="hp-theme__actions theme-actions">
							<a href="<?php echo esc_url( $theme['preview_url'] ); ?>" target="_blank" class="button preview install-theme-preview"><?php esc_html_e( 'Preview', 'hivepress' ); ?></a>
							<?php
							if ( 'install' === $theme['status'] ) :
								if ( isset( $theme['price'] ) ) :
									?>
									<a href="<?php echo esc_url( $theme['buy_url'] ); ?>" target="_blank" class="button button-primary theme-install"><?php esc_html_e( 'Purchase', 'hivepress' ); ?></a>
								<?php else : ?>
									<a href="<?php echo esc_url( $theme['url'] ); ?>" class="button button-primary theme-install"><?php esc_html_e( 'Install', 'hivepress' ); ?></a>
								<?php
								endif;
							elseif ( 'activate' === $theme['status'] ) :
								?>
								<a href="<?php echo esc_url( $theme['url'] ); ?>" class="button button-primary activate"><?php esc_html_e( 'Activate', 'hivepress' ); ?></a>
							<?php else : ?>
								<button type="button" class="button button-disabled" disabled="disabled"><?php echo esc_html_x( 'Active', 'theme', 'hivepress' ); ?></button>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
