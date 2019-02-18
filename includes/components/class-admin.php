<?php
/**
 * Admin component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin component class.
 *
 * @class Admin
 */
final class Admin {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Register post types.
		add_action( 'init', [ $this, 'register_post_types' ] );

		// Register taxonomies.
		add_action( 'init', [ $this, 'register_taxonomies' ] );

		if ( is_admin() ) {

			// Manage admin pages.
			add_action( 'admin_menu', [ $this, 'add_pages' ] );
			add_filter( 'custom_menu_order', [ $this, 'order_pages' ] );
			add_filter( 'menu_order', [ $this, 'order_pages' ] );

			// Manage meta boxes.
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 2 );
			add_action( 'save_post', [ $this, 'update_meta_box' ], 10, 2 );
		}
	}

	/**
	 * Registers post types.
	 */
	public function register_post_types() {
		foreach ( hivepress()->get_config( 'post_types' ) as $post_type => $post_type_args ) {
			register_post_type( hp_prefix( $post_type ), $post_type_args );
		}
	}

	/**
	 * Registers taxonomies.
	 */
	public function register_taxonomies() {
		foreach ( hivepress()->get_config( 'taxonomies' ) as $taxonomy => $taxonomy_args ) {
			register_taxonomy( hp_prefix( $taxonomy ), hp_prefix( $taxonomy_args['object_type'] ), $taxonomy_args['args'] );
		}
	}

	/**
	 * Adds admin pages.
	 */
	public function add_pages() {
		global $menu;

		// Add separator.
		$menu[] = [ '', 'manage_options', 'hp_separator', '', 'wp-menu-separator' ];

		// Add pages.
		add_menu_page( sprintf( esc_html__( '%s Settings', 'hivepress' ), HP_CORE_NAME ), HP_CORE_NAME, 'manage_options', 'hp_settings', [ $this, 'render_settings' ], HP_CORE_URL . '/assets/images/logo.svg' );
		add_submenu_page( 'hp_settings', sprintf( esc_html__( '%s Settings', 'hivepress' ), HP_CORE_NAME ), esc_html__( 'Settings', 'hivepress' ), 'manage_options', 'hp_settings' );
		add_submenu_page( 'hp_settings', sprintf( esc_html__( '%s Add-ons', 'hivepress' ), HP_CORE_NAME ), esc_html__( 'Add-ons', 'hivepress' ), 'manage_options', 'hp_addons', [ $this, 'render_addons' ] );
	}

	/**
	 * Orders admin pages.
	 *
	 * @param array $menu Menu items.
	 * @return array
	 */
	public function order_pages( $menu ) {
		if ( current_user_can( 'manage_options' ) ) {
			if ( is_array( $menu ) ) {

				// Get admin pages.
				$pages = [
					'hp_separator',
					'hp_settings',
				];

				foreach ( array_keys( hivepress()->get_config( 'post_types' ) ) as $post_type ) {
					$pages[] = 'edit.php?post_type=' . hp_prefix( $post_type );
				}

				// Filter menu items.
				$menu = array_filter(
					$menu,
					function( $name ) use ( $pages ) {
						return ! in_array( $name, $pages, true );
					}
				);

				// Insert menu items.
				array_splice( $menu, array_search( 'separator2', $menu, true ) - 1, 0, $pages );

				return $menu;
			} else {
				return true;
			}
		}
	}

	/**
	 * Routes component functions.
	 *
	 * @param string $name Function name.
	 * @param array  $args Function arguments.
	 */
	public function __call( $name, $args ) {

		// Render admin page.
		if ( strpos( $name, 'render_' ) === 0 ) {
			$template_name = str_replace( '_', '-', str_replace( 'render_', '', $name ) );
			$template_path = HP_CORE_PATH . '/templates/admin/' . $template_name . '.php';

			if ( file_exists( $template_path ) ) {
				if ( 'settings' === $template_name ) {
					$tabs        = $this->get_settings_tabs();
					$current_tab = $this->get_settings_tab();
				} elseif ( 'addons' === $template_name ) {
					$tabs        = $this->get_addons_tabs();
					$current_tab = $this->get_addons_tab();
					$addons      = $this->get_addons( $current_tab );
				}

				include $template_path;
			}
		}
	}

	/**
	 * Gets settings tabs.
	 *
	 * @return array
	 */
	private function get_settings_tabs() {
		return array_map(
			function( $section ) {
				return hp_get_array_value( $section, 'title' );
			},
			hp_sort_array( hivepress()->get_config( 'options' ) )
		);
	}

	/**
	 * Gets current settings tab.
	 *
	 * @return mixed
	 */
	private function get_settings_tab() {
		$current_tab = false;

		// Get all tabs.
		$tabs = array_keys( hp_sort_array( hivepress()->get_config( 'options' ) ) );

		$first_tab   = hp_get_array_value( $tabs, 0 );
		$current_tab = hp_get_array_value( $_GET, 'tab', $first_tab );

		// Set the default tab.
		if ( ! in_array( $current_tab, $tabs, true ) ) {
			$current_tab = $first_tab;
		}

		return $current_tab;
	}

	/**
	 * Gets add-ons.
	 *
	 * @param string $status Add-ons status.
	 * @return array
	 */
	private function get_addons( $status = 'all' ) {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		// Get cached add-ons.
		$addons = get_transient( 'hp_addons' );

		if ( false === $addons ) {
			$addons = [];

			// Query plugins.
			$api = plugins_api(
				'query_plugins',
				[
					'author' => 'hivepress',
					'fields' => [
						'icons' => true,
					],
				]
			);

			if ( ! is_wp_error( $api ) ) {

				// Filter add-ons.
				$addons = array_filter(
					$api->plugins,
					function( $plugin ) {
						return 'hivepress' !== $plugin->slug;
					}
				);

				// Cache add-ons.
				set_transient( 'hp_addons', $addons, DAY_IN_SECONDS );
			}
		}

		// Set add-on statuses.
		foreach ( $addons as $index => $addon ) {

			// Get path and status.
			$addon_path   = $addon->slug . '/' . $addon->slug . '.php';
			$addon_status = install_plugin_install_status( $addon );

			// Set activation status.
			if ( ! in_array( $addon_status['status'], [ 'install', 'update_available' ], true ) && ! is_plugin_active( $addon_path ) ) {
				$addon_status['status'] = 'activate';
				$addon_status['url']    = admin_url(
					'plugins.php?' . http_build_query(
						[
							'action'   => 'activate',
							'plugin'   => $addon_path,
							'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $addon_path ),
						]
					)
				);
			}

			unset( $addon_status['version'] );

			$addons[ $index ]->name = str_replace( HP_CORE_NAME . ' ', '', $addon->name );
			$addons[ $index ]       = (object) array_merge( (array) $addon, $addon_status );
		}

		// Filter add-ons.
		if ( 'all' !== $status ) {
			$addons = array_filter(
				$addons,
				function( $addon ) use ( $status ) {
					return 'installed' === $status && 'install' !== $addon->status;
				}
			);
		}

		return $addons;
	}

	/**
	 * Gets add-ons tabs.
	 *
	 * @return array
	 */
	private function get_addons_tabs() {

		// Set tabs.
		$tabs = [
			'all'       => [
				'name'  => esc_html__( 'All', 'hivepress' ),
				'count' => 0,
			],
			'installed' => [
				'name'  => esc_html__( 'Installed', 'hivepress' ),
				'count' => 0,
			],
		];

		// Get add-ons.
		$addons = $this->get_addons();

		// Set tab counts.
		$tabs['all']['count']       = count( $addons );
		$tabs['installed']['count'] = count(
			array_filter(
				$addons,
				function( $addon ) {
					return 'install' !== $addon->status;
				}
			)
		);

		// Filter tabs.
		$tabs = array_filter(
			$tabs,
			function( $tab ) {
				return 0 !== $tab['count'];
			}
		);

		return $tabs;
	}

	/**
	 * Gets current add-ons tab.
	 *
	 * @return mixed
	 */
	private function get_addons_tab() {
		$current_tab = false;

		// Get all tabs.
		$tabs = array_keys( $this->get_addons_tabs() );

		$first_tab   = hp_get_array_value( $tabs, 0 );
		$current_tab = hp_get_array_value( $_GET, 'addon_status', $first_tab );

		// Set the default tab.
		if ( ! in_array( $current_tab, $tabs, true ) ) {
			$current_tab = $first_tab;
		}

		return $current_tab;
	}

	/**
	 * Adds meta boxes.
	 *
	 * @param string  $post_type Post type.
	 * @param WP_Post $post Post object.
	 */
	public function add_meta_boxes( $post_type, $post ) {
		foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_id => $meta_box ) {
			if ( hp_prefix( $meta_box['screen'] ) === $post_type ) {

				// Add meta box.
				if ( ! empty( $meta_box['fields'] ) ) {
					add_meta_box( hp_prefix( $meta_box_id ), $meta_box['title'], [ $this, 'render_meta_box' ], hp_prefix( $meta_box['screen'] ), hp_get_array_value( $meta_box, 'context', 'normal' ), hp_get_array_value( $meta_box, 'priority', 'default' ) );
				}
			}
		}
	}

	/**
	 * Updates meta box values.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function update_meta_box( $post_id, $post ) {
		global $pagenow;

		if ( 'post.php' === $pagenow ) {
			foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_id => $meta_box ) {
				$screen = hp_prefix( $meta_box['screen'] );

				if ( $screen === $post->post_type || ( is_array( $screen ) && in_array( $post->post_type, $screen, true ) ) ) {
					foreach ( $meta_box['fields'] as $field_id => $field_args ) {

						// Get field class.
						$field_class = '\HivePress\Fields\\' . $field_args['type'];

						// Create field.
						$field = new $field_class( $field_args );

						// todo pass value.
						if ( $field->validate() ) {
							update_post_meta( $post_id, hp_prefix( $field_id ), $field->get_value() );
						}
					}
				}
			}
		}
	}

	/**
	 * Renders meta box fields.
	 *
	 * @param WP_Post $post Post object.
	 * @param array   $args Meta box arguments.
	 */
	public function render_meta_box( $post, $args ) {
		$output = '';

		// Get meta box.
		$meta_box = hp_get_array_value( hivepress()->get_config( 'meta_boxes' ), hp_unprefix( $args['id'] ) );

		if ( ! is_null( $meta_box ) ) {

			// Get meta box ID.
			$meta_box_id = hp_unprefix( $args['id'] );

			// Sort fields.
			$meta_box['fields'] = hp_sort_array( $meta_box['fields'] );

			// Render fields.
			$output .= '<table class="form-table hp-form">';

			foreach ( $meta_box['fields'] as $field_id => $field_args ) {

				// Get field value.
				$value = get_post_meta( $post->ID, hp_prefix( $field_id ), true );

				if ( '' === $value ) {
					$value = null;
				}

				// Get field class.
				$field_class = '\HivePress\Fields\\' . $field_args['type'];

				// Create field.
				$field = new $field_class( $field_args );

				if ( 'hidden' === $field_args['type'] ) {

					// Render field.
					$output .= $field->render();
				} else {
					$output .= '<tr>';

					// Render field label.
					$output .= '<th scope="row">' . esc_html( $field_args['label'] ) . '</th>';

					// Render field.
					$output .= '<td>' . $field->render() . '</td>';

					$output .= '</tr>';
				}
			}

			$output .= '</table>';
		}

		echo $output;
	}
}
