<?php
/**
 * Updater component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Updater component class.
 *
 * @class Updater
 */
final class Updater extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Check license key.
		if ( ! get_option( 'hp_hivepress_license_key' ) ) {
			return;
		}

		// Check theme updates.
		add_filter( 'pre_set_site_transient_update_themes', [ $this, 'check_theme_updates' ] );

		// Check plugin updates.
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_plugin_updates' ] );

		// Set plugin details.
		add_filter( 'plugins_api', [ $this, 'set_plugin_details' ], 10, 3 );

		parent::__construct( $args );
	}

	/**
	 * Gets license key.
	 *
	 * @return string
	 */
	protected function get_license_key() {
		return implode( ',', explode( "\n", get_option( 'hp_hivepress_license_key' ) ) );
	}

	/**
	 * Gets themes.
	 *
	 * @return array
	 */
	protected function get_themes() {

		// Get license key.
		$license_key = $this->get_license_key();

		// Get cache key.
		$cache_key = 'themes_' . md5( $license_key );

		// Get cached themes.
		$themes = hivepress()->cache->get_cache( $cache_key );

		if ( is_null( $themes ) ) {
			$themes = [];

			// Get API response.
			$response = json_decode(
				wp_remote_retrieve_body(
					wp_remote_get(
						'https://store.hivepress.io/api/v1/products?' . http_build_query(
							[
								'type'        => 'theme',
								'license_key' => $license_key,
							]
						)
					)
				),
				true
			);

			if ( is_array( $response ) && isset( $response['data'] ) ) {
				foreach ( $response['data'] as $theme ) {

					// Add theme.
					$themes[ $theme['slug'] ] = [
						'theme'        => $theme['slug'],
						'new_version'  => $theme['version'],
						'requires'     => $theme['wp_min_version'],
						'requires_php' => $theme['php_version'],
						'url'          => $theme['buy_url'],
						'package'      => $theme['download_url'],
					];
				}

				// Cache themes.
				hivepress()->cache->set_cache( $cache_key, null, $themes, HOUR_IN_SECONDS );
			}
		}

		return $themes;
	}

	/**
	 * Gets plugins.
	 *
	 * @return array
	 */
	protected function get_plugins() {

		// Get license key.
		$license_key = $this->get_license_key();

		// Get cache key.
		$cache_key = 'plugins_' . md5( $license_key );

		// Get cached plugins.
		$plugins = hivepress()->cache->get_cache( $cache_key );

		if ( is_null( $plugins ) ) {
			$plugins = [];

			// Get API response.
			$response = json_decode(
				wp_remote_retrieve_body(
					wp_remote_get(
						'https://store.hivepress.io/api/v1/products?' . http_build_query(
							[
								'type'        => 'extension',
								'license_key' => $license_key,
							]
						)
					)
				),
				true
			);

			if ( is_array( $response ) && isset( $response['data'] ) ) {
				foreach ( $response['data'] as $extension ) {

					// Add plugin.
					$plugins[ $extension['slug'] ] = (object) [
						'name'          => $extension['name'],
						'slug'          => $extension['slug'],
						'version'       => $extension['version'],
						'new_version'   => $extension['version'],
						'tested'        => $extension['wp_max_version'],
						'requires'      => $extension['wp_min_version'],
						'requires_php'  => $extension['php_version'],
						'id'            => $extension['buy_url'],
						'url'           => $extension['buy_url'],
						'package'       => $extension['download_url'],
						'download_link' => $extension['download_url'],
						'plugin'        => $extension['slug'] . '/' . $extension['slug'] . '.php',
						'author'        => '<a href="https://hivepress.io/" target="_blank">HivePress</a>',
						'banners'       => [],
						'banners_rtl'   => [],
						'compatibility' => (object) [],

						'icons'         => [
							'1x' => $extension['image_url'],
							'2x' => $extension['image_url'],
						],

						'sections'      => [
							'description' => $extension['description'],
						],
					];
				}

				// Cache plugins.
				hivepress()->cache->set_cache( $cache_key, null, $plugins, HOUR_IN_SECONDS );
			}
		}

		return $plugins;
	}

	/**
	 * Checks theme updates.
	 *
	 * @param object $transient Transient object.
	 * @return object
	 */
	public function check_theme_updates( $transient ) {
		if ( ! empty( $transient->checked ) ) {

			// Get themes.
			$themes = $this->get_themes();

			foreach ( $themes as $theme ) {

				// Get version.
				$version = hp\get_array_value( $transient->checked, $theme['theme'] );

				if ( $version && version_compare( $version, $theme['new_version'], '<' ) ) {

					// Add update.
					$transient->response[ $theme['theme'] ] = $theme;
				}
			}
		}

		return $transient;
	}

	/**
	 * Checks plugin updates.
	 *
	 * @param object $transient Transient object.
	 * @return object
	 */
	public function check_plugin_updates( $transient ) {
		if ( ! empty( $transient->checked ) ) {

			// Get plugins.
			$plugins = $this->get_plugins();

			foreach ( $plugins as $plugin ) {

				// Get version.
				$version = hp\get_array_value( $transient->checked, $plugin->plugin );

				if ( $version && version_compare( $version, $plugin->version, '<' ) ) {

					// Add update.
					$transient->response[ $plugin->plugin ] = $plugin;
				}
			}
		}

		return $transient;
	}

	/**
	 * Sets plugin details.
	 *
	 * @param object $response API response.
	 * @param string $action API action.
	 * @param array  $args Request arguments.
	 * @return object
	 */
	public function set_plugin_details( $response, $action, $args ) {
		if ( 'plugin_information' === $action ) {

			// Get plugin.
			$plugin = hp\get_array_value( $this->get_plugins(), $args->slug );

			if ( $plugin ) {

				// Set details.
				$response = $plugin;
			}
		}

		return $response;
	}
}
