<?php
/**
 * Admin component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin component class.
 *
 * @class Admin
 */
final class Admin {

	/**
	 * Array of post states.
	 *
	 * @var array
	 */
	private $post_states = [];

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

			// Initialize settings.
			add_action( 'hivepress/v1/activate', [ $this, 'init_settings' ] );

			// Register settings.
			add_action( 'admin_init', [ $this, 'register_settings' ] );

			// Manage post states.
			add_action( 'init', [ $this, 'register_post_states' ] );
			add_filter( 'display_post_states', [ $this, 'add_post_states' ], 10, 2 );

			// Manage meta boxes.
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 2 );
			add_action( 'save_post', [ $this, 'update_meta_box' ], 10, 2 );

			// Add term boxes.
			add_action( 'admin_init', [ $this, 'add_term_boxes' ] );

			// Hide comments.
			add_filter( 'comments_clauses', [ $this, 'hide_comments' ] );

			// Enqueue scripts.
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// Render notices.
			add_action( 'admin_notices', [ $this, 'render_notices' ] );
		}
	}

	/**
	 * Registers post types.
	 */
	public function register_post_types() {
		foreach ( hivepress()->get_config( 'post_types' ) as $post_type => $post_type_args ) {
			register_post_type( hp\prefix( $post_type ), $post_type_args );
		}
	}

	/**
	 * Registers taxonomies.
	 */
	public function register_taxonomies() {
		foreach ( hivepress()->get_config( 'taxonomies' ) as $taxonomy => $taxonomy_args ) {
			register_taxonomy( hp\prefix( $taxonomy ), hp\prefix( $taxonomy_args['object_type'] ), $taxonomy_args );
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
		add_submenu_page( 'hp_settings', sprintf( esc_html__( '%s Extensions', 'hivepress' ), HP_CORE_NAME ), esc_html__( 'Extensions', 'hivepress' ), 'manage_options', 'hp_extensions', [ $this, 'render_extensions' ] );
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
					$pages[] = 'edit.php?post_type=' . hp\prefix( $post_type );
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
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @return mixed
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'render_' ) === 0 ) {

			// Render admin page.
			$template_name = hp\sanitize_slug( substr( $name, strlen( 'render' ) + 1 ) );
			$template_path = HP_CORE_DIR . '/templates/admin/' . $template_name . '.php';

			if ( file_exists( $template_path ) ) {
				if ( 'settings' === $template_name ) {
					$tabs        = $this->get_settings_tabs();
					$current_tab = $this->get_settings_tab();
				} elseif ( 'extensions' === $template_name ) {
					$tabs        = $this->get_extensions_tabs();
					$current_tab = $this->get_extensions_tab();
					$extensions  = $this->get_extensions( $current_tab );
				}

				include $template_path;
			}
		} elseif ( strpos( $name, 'validate_' ) === 0 ) {

			// Validate setting.
			return $this->validate_setting( substr( $name, strlen( 'validate' ) + 1 ), $args[0] );
		}
	}

	/**
	 * Initializes settings.
	 */
	public function init_settings() {
		foreach ( hivepress()->get_config( 'settings' ) as $tab ) {
			foreach ( $tab['sections'] as $section ) {
				foreach ( $section['fields'] as $field_name => $field ) {
					if ( isset( $field['default'] ) ) {
						add_option( hp\prefix( $field_name ), $field['default'] );
					}
				}
			}
		}
	}

	/**
	 * Registers settings.
	 */
	public function register_settings() {
		global $pagenow;

		if ( 'options.php' === $pagenow || ( 'admin.php' === $pagenow && 'hp_settings' === hp\get_array_value( $_GET, 'page' ) ) ) {

			// Get current tab.
			$tab = hp\get_array_value( hivepress()->get_config( 'settings' ), $this->get_settings_tab() );

			if ( ! is_null( $tab ) ) {
				foreach ( hp\sort_array( $tab['sections'] ) as $section_name => $section ) {

					// Add settings section.
					add_settings_section( $section_name, esc_html( hp\get_array_value( $section, 'title' ) ), [ $this, 'render_settings_section' ], 'hp_settings' );

					// Register settings.
					foreach ( hp\sort_array( $section['fields'] ) as $field_name => $field ) {
						$field_name       = hp\prefix( $field_name );
						$field['default'] = get_option( $field_name );

						add_settings_field( $field_name, esc_html( $field['label'] ) . $this->render_tooltip( hp\get_array_value( $field, 'description' ) ), [ $this, 'render_settings_field' ], 'hp_settings', $section_name, array_merge( $field, [ 'name' => $field_name ] ) );
						register_setting( 'hp_settings', $field_name, [ $this, 'validate_' . hp\unprefix( $field_name ) ] );
					}
				}
			}
		}
	}

	/**
	 * Validates setting.
	 *
	 * @param string $name Setting name.
	 * @return mixed
	 */
	private function validate_setting( $name ) {

		// Get current tab.
		$tab = hp\get_array_value( hivepress()->get_config( 'settings' ), $this->get_settings_tab() );

		// Get setting.
		$setting = false;

		if ( ! is_null( $tab ) ) {
			foreach ( $tab['sections'] as $section_name => $section ) {
				foreach ( $section['fields'] as $field_name => $field ) {
					if ( $field_name === $name ) {
						$setting = $field;

						break 2;
					}
				}
			}
		}

		// Validate setting.
		if ( false !== $setting ) {

			// Get setting name.
			$setting_name = hp\prefix( $name );

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $setting['type'];

			if ( class_exists( $field_class ) ) {

				// Create field.
				$field = new $field_class( $setting );

				// Validate field.
				$field->set_value( hp\get_array_value( $_POST, $setting_name ) );

				if ( $field->validate() ) {
					return $field->get_value();
				} else {
					foreach ( $field->get_errors() as $error ) {
						add_settings_error( $setting_name, $setting_name, esc_html( $error ) );
					}

					return get_option( $setting_name );
				}
			}
		}

		return false;
	}

	/**
	 * Renders settings section.
	 *
	 * @param array $args Section arguments.
	 */
	public function render_settings_section( $args ) {

		// Get current tab.
		$tab = hp\get_array_value( hivepress()->get_config( 'settings' ), $this->get_settings_tab() );

		if ( ! is_null( $tab ) ) {

			// Get current section.
			$section = hp\get_array_value( $tab['sections'], $args['id'] );

			if ( ! is_null( $section ) ) {

				// Render description.
				if ( isset( $section['description'] ) ) {
					echo '<p>' . esc_html( $section['description'] ) . '</p>';
				}
			}
		}
	}

	/**
	 * Renders settings field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_settings_field( $args ) {
		$output = '';

		// Get field class.
		$field_class = '\HivePress\Fields\\' . $args['type'];

		if ( class_exists( $field_class ) ) {

			// Create field.
			$field = new $field_class( $args );

			// Render field.
			$output .= $field->render();
		}

		echo $output;
	}

	/**
	 * Gets settings tabs.
	 *
	 * @return array
	 */
	private function get_settings_tabs() {
		return array_map(
			function( $section ) {
				return hp\get_array_value( $section, 'title' );
			},
			hp\sort_array( hivepress()->get_config( 'settings' ) )
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
		$tabs = array_keys( hp\sort_array( hivepress()->get_config( 'settings' ) ) );

		$first_tab   = hp\get_array_value( $tabs, 0 );
		$current_tab = hp\get_array_value( $_GET, 'tab', $first_tab );

		// Set the default tab.
		if ( ! in_array( $current_tab, $tabs, true ) ) {
			$current_tab = $first_tab;
		}

		return $current_tab;
	}

	/**
	 * Gets extensions.
	 *
	 * @param string $status Extensions status.
	 * @return array
	 */
	private function get_extensions( $status = 'all' ) {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		// Get cached extensions.
		$extensions = hivepress()->cache->get_cache( 'extensions' );

		if ( is_null( $extensions ) ) {
			$extensions = [];

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

				// Filter extensions.
				$extensions = array_filter(
					array_map(
						function( $extension ) {
							return array_intersect_key(
								(array) $extension,
								array_flip(
									[
										'slug',
										'name',
										'version',
										'status',
										'url',
										'icons',
										'requires',
										'author',
										'short_description',
									]
								)
							);
						},
						$api->plugins
					),
					function( $extension ) {
						return 'hivepress' !== $extension['slug'];
					}
				);

				// Cache extensions.
				if ( count( $extensions ) <= 100 ) {
					hivepress()->cache->set_cache( 'extensions', $extensions, DAY_IN_SECONDS );
				}
			}
		}

		// Set extension statuses.
		foreach ( $extensions as $index => $extension ) {

			// Get path and status.
			$extension_path   = $extension['slug'] . '/' . $extension['slug'] . '.php';
			$extension_status = install_plugin_install_status( $extension );

			// Set activation status.
			if ( ! in_array( $extension_status['status'], [ 'install', 'update_available' ], true ) && ! is_plugin_active( $extension_path ) ) {
				$extension_status['status'] = 'activate';
				$extension_status['url']    = admin_url(
					'plugins.php?' . http_build_query(
						[
							'action'   => 'activate',
							'plugin'   => $extension_path,
							'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $extension_path ),
						]
					)
				);
			}

			unset( $extension_status['version'] );

			$extensions[ $index ]['name'] = str_replace( HP_CORE_NAME . ' ', '', $extension['name'] );
			$extensions[ $index ]         = array_merge( $extension, $extension_status );
		}

		// Filter extensions.
		if ( 'all' !== $status ) {
			$extensions = array_filter(
				$extensions,
				function( $extension ) use ( $status ) {
					return 'installed' === $status && 'install' !== $extension['status'];
				}
			);
		}

		return $extensions;
	}

	/**
	 * Gets extensions tabs.
	 *
	 * @return array
	 */
	private function get_extensions_tabs() {

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

		// Get extensions.
		$extensions = $this->get_extensions();

		// Set tab counts.
		$tabs['all']['count']       = count( $extensions );
		$tabs['installed']['count'] = count(
			array_filter(
				$extensions,
				function( $extension ) {
					return 'install' !== $extension['status'];
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
	 * Gets current extensions tab.
	 *
	 * @return mixed
	 */
	private function get_extensions_tab() {
		$current_tab = false;

		// Get all tabs.
		$tabs = array_keys( $this->get_extensions_tabs() );

		$first_tab   = hp\get_array_value( $tabs, 0 );
		$current_tab = hp\get_array_value( $_GET, 'extension_status', $first_tab );

		// Set the default tab.
		if ( ! in_array( $current_tab, $tabs, true ) ) {
			$current_tab = $first_tab;
		}

		return $current_tab;
	}

	/**
	 * Registers post states.
	 */
	public function register_post_states() {
		foreach ( hivepress()->get_config( 'settings' ) as $tab ) {
			foreach ( $tab['sections'] as $section ) {
				foreach ( $section['fields'] as $field_name => $field ) {
					if ( strpos( $field_name, 'page_' ) === 0 ) {
						$post_id = absint( get_option( hp\prefix( $field_name ) ) );

						if ( 0 !== $post_id ) {
							$this->post_states[ $post_id ] = $field['label'];
						}
					}
				}
			}
		}
	}

	/**
	 * Adds post states.
	 *
	 * @param array   $states Post states.
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	public function add_post_states( $states, $post ) {
		if ( isset( $this->post_states[ $post->ID ] ) ) {
			$states[] = $this->post_states[ $post->ID ];
		}

		return $states;
	}

	/**
	 * Adds meta boxes.
	 *
	 * @param string  $post_type Post type.
	 * @param WP_Post $post Post object.
	 */
	public function add_meta_boxes( $post_type, $post ) {
		foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_name => $meta_box ) {
			if ( hp\prefix( $meta_box['screen'] ) === $post_type ) {

				/**
				 * Filters meta box arguments.
				 *
				 * @filter /meta_boxes/{$name}
				 * @description Filters meta box arguments.
				 * @param string $name Meta box name.
				 * @param array $args Meta box arguments.
				 */
				$meta_box = apply_filters( 'hivepress/v1/meta_boxes/' . $meta_box_name, array_merge( $meta_box, [ 'name' => $meta_box_name ] ) );

				// Add meta box.
				if ( ! empty( $meta_box['fields'] ) ) {
					add_meta_box( hp\prefix( $meta_box_name ), $meta_box['title'], [ $this, 'render_meta_box' ], hp\prefix( $meta_box['screen'] ), hp\get_array_value( $meta_box, 'context', 'normal' ), hp\get_array_value( $meta_box, 'priority', 'default' ) );
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

			// Remove action.
			remove_action( 'save_post', [ $this, 'update_meta_box' ] );

			// Update field values.
			foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_name => $meta_box ) {
				$screen = hp\prefix( $meta_box['screen'] );

				if ( $screen === $post->post_type || ( is_array( $screen ) && in_array( $post->post_type, $screen, true ) ) ) {

					// Filter arguments.
					$meta_box = apply_filters( 'hivepress/v1/meta_boxes/' . $meta_box_name, array_merge( $meta_box, [ 'name' => $meta_box_name ] ) );

					foreach ( $meta_box['fields'] as $field_name => $field_args ) {

						// Get field class.
						$field_class = '\HivePress\Fields\\' . $field_args['type'];

						if ( class_exists( $field_class ) ) {

							// Create field.
							$field = new $field_class( $field_args );

							// Validate field.
							$field->set_value( hp\get_array_value( $_POST, hp\prefix( $field_name ) ) );

							if ( $field->validate() ) {

								// Update field value.
								if ( ! isset( $field_args['alias'] ) ) {
									update_post_meta( $post_id, hp\prefix( $field_name ), $field->get_value() );
								} else {
									wp_update_post(
										[
											'ID' => $post_id,
											$field_args['alias'] => $field->get_value(),
										]
									);
								}
							}
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

		// Get meta box name.
		$meta_box_name = hp\unprefix( $args['id'] );

		// Get meta box.
		$meta_box = hp\get_array_value( hivepress()->get_config( 'meta_boxes' ), $meta_box_name );

		if ( ! is_null( $meta_box ) ) {

			// Filter arguments.
			$meta_box = apply_filters( 'hivepress/v1/meta_boxes/' . $meta_box_name, array_merge( $meta_box, [ 'name' => $meta_box_name ] ) );

			// Render fields.
			$output .= '<table class="form-table hp-form">';

			foreach ( hp\sort_array( $meta_box['fields'] ) as $field_name => $field_args ) {

				// Get field class.
				$field_class = '\HivePress\Fields\\' . $field_args['type'];

				if ( class_exists( $field_class ) ) {

					// Create field.
					$field = new $field_class( array_merge( $field_args, [ 'name' => hp\prefix( $field_name ) ] ) );

					// Get field value.
					if ( ! isset( $field_args['alias'] ) ) {
						$value = get_post_meta( $post->ID, hp\prefix( $field_name ), true );
					} else {
						$value = get_post_field( $field_args['alias'], $post );
					}

					if ( '' !== $value ) {
						$field->set_value( $value );
					}

					if ( 'hidden' === $field_args['type'] ) {

						// Render field.
						$output .= $field->render();
					} else {
						$output .= '<tr class="hp-form__field hp-form__field--' . esc_attr( $field_args['type'] ) . '">';

						// Render field label.
						$output .= '<th scope="row">';

						if ( isset( $field_args['label'] ) ) {
							$output .= esc_html( $field_args['label'] ) . $this->render_tooltip( hp\get_array_value( $field_args, 'description' ) );
						}

						$output .= '</th>';

						// Render field.
						$output .= '<td>' . $field->render() . '</td>';

						$output .= '</tr>';
					}
				}
			}

			$output .= '</table>';
		}

		echo $output;
	}

	/**
	 * Adds term boxes.
	 */
	public function add_term_boxes() {
		foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_name => $meta_box ) {
			if ( taxonomy_exists( hp\prefix( $meta_box['screen'] ) ) ) {
				$taxonomy = hp\prefix( $meta_box['screen'] );

				// Update term boxes.
				add_action( 'edit_' . $taxonomy, [ $this, 'update_term_box' ] );
				add_action( 'create_' . $taxonomy, [ $this, 'update_term_box' ] );

				// Render term boxes.
				add_action( $taxonomy . '_edit_form_fields', [ $this, 'render_term_box' ], 10, 2 );
				add_action( $taxonomy . '_add_form_fields', [ $this, 'render_term_box' ], 10, 2 );
			}
		}
	}

	/**
	 * Hides comments.
	 *
	 * @param array $query Query arguments.
	 * @return array
	 */
	public function hide_comments( $query ) {
		global $pagenow;

		if ( in_array( $pagenow, [ 'index.php', 'edit-comments.php' ], true ) ) {
			$config = hivepress()->get_config( 'comment_types' );

			if ( ! empty( $config ) ) {
				$types = array_filter(
					array_map(
						function( $type, $args ) {
							if ( ! isset( $args['show_ui'] ) || ! $args['show_ui'] ) {
								return '"' . hp\prefix( $type ) . '"';
							}
						},
						array_keys( $config ),
						$config
					)
				);

				if ( ! empty( $types ) ) {
					$query['where'] .= ' AND comment_type NOT IN (' . implode( ',', $types ) . ')';
				}
			}
		}

		return $query;
	}

	/**
	 * Updates term box values.
	 *
	 * @param int $term_id Term ID.
	 */
	public function update_term_box( $term_id ) {

		// Get term.
		$term = get_term( $term_id );

		if ( ! is_null( $term ) ) {

			// Remove actions.
			add_action( 'edit_' . $term->taxonomy, [ $this, 'update_term_box' ] );
			add_action( 'create_' . $term->taxonomy, [ $this, 'update_term_box' ] );

			// Update field values.
			foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_name => $meta_box ) {
				$screen = hp\prefix( $meta_box['screen'] );

				if ( ! is_array( $screen ) && taxonomy_exists( $screen ) && $screen === $term->taxonomy ) {

					// Filter arguments.
					$meta_box = apply_filters( 'hivepress/v1/meta_boxes/' . $meta_box_name, array_merge( $meta_box, [ 'name' => $meta_box_name ] ) );

					foreach ( $meta_box['fields'] as $field_name => $field_args ) {

						// Get field class.
						$field_class = '\HivePress\Fields\\' . $field_args['type'];

						if ( class_exists( $field_class ) ) {

							// Create field.
							$field = new $field_class( $field_args );

							// Validate field.
							$field->set_value( hp\get_array_value( $_POST, hp\prefix( $field_name ) ) );

							if ( $field->validate() ) {

								// Update field value.
								if ( ! isset( $field_args['alias'] ) ) {
									update_term_meta( $term->term_id, hp\prefix( $field_name ), $field->get_value() );
								} else {
									wp_update_term(
										$term->term_id,
										$term->taxonomy,
										[
											$field_args['alias'] => $field->get_value(),
										]
									);
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Renders term box fields.
	 *
	 * @param mixed  $term Term object.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function render_term_box( $term, $taxonomy = '' ) {
		$output = '';

		// Get term ID.
		$term_id = 0;

		if ( ! is_object( $term ) ) {
			$taxonomy = $term;
		} else {
			$term_id = $term->term_id;
		}

		foreach ( hivepress()->get_config( 'meta_boxes' ) as $meta_box_name => $meta_box ) {
			$screen = hp\prefix( $meta_box['screen'] );

			if ( ! is_array( $screen ) && taxonomy_exists( $screen ) && $screen === $taxonomy ) {

				// Filter arguments.
				$meta_box = apply_filters( 'hivepress/v1/meta_boxes/' . $meta_box_name, array_merge( $meta_box, [ 'name' => $meta_box_name ] ) );

				foreach ( hp\sort_array( $meta_box['fields'] ) as $field_name => $field_args ) {

					// Get field class.
					$field_class = '\HivePress\Fields\\' . $field_args['type'];

					if ( class_exists( $field_class ) ) {

						// Create field.
						$field = new $field_class( array_merge( $field_args, [ 'name' => hp\prefix( $field_name ) ] ) );

						if ( ! is_object( $term ) ) {
							$output .= '<div class="form-field">';

							// Render label.
							$output .= '<label for="' . esc_attr( hp\prefix( $field_name ) ) . '">' . esc_html( $field_args['label'] ) . '</label>';

							// Render field.
							$output .= $field->render();

							// Render description.
							if ( isset( $field_args['description'] ) ) {
								$output .= '<p>' . esc_html( $field_args['description'] ) . '</p>';
							}

							$output .= '</div>';
						} else {
							$output .= '<tr class="form-field">';

							// Render label.
							$output .= '<th scope="row"><label for="' . esc_attr( hp\prefix( $field_name ) ) . '">' . esc_html( $field_args['label'] ) . '</label></th>';
							$output .= '<td>';

							// Get field value.
							if ( ! isset( $field_args['alias'] ) ) {
								$value = get_term_meta( $term->term_id, hp\prefix( $field_name ), true );
							} else {
								$value = get_term_field( $field_args['alias'], $term );
							}

							if ( '' === $value ) {
								$value = null;
							}

							$field->set_value( $value );

							// Render field.
							$output .= $field->render();

							// Render description.
							if ( isset( $field_args['description'] ) ) {
								$output .= '<p class="description">' . esc_html( $field_args['description'] ) . '</p>';
							}

							$output .= '</td>';
							$output .= '</tr>';
						}
					}
				}
			}
		}

		echo $output;
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {
		global $pagenow;

		if ( in_array( $pagenow, [ 'edit-tags.php', 'term.php' ], true ) ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Renders notices.
	 */
	public function render_notices() {
		global $pagenow;

		// Render setting errors.
		if ( 'admin.php' === $pagenow && 'hp_settings' === hp\get_array_value( $_GET, 'page' ) ) {
			settings_errors();
		}

		// Render theme notice.
		if ( ! current_theme_supports( 'hivepress' ) && ! in_array( 'incompatible_theme', (array) get_option( 'hp_admin_dismissed_notices' ), true ) ) {
			echo '<div class="notice notice-warning is-dismissible" data-component="notice" data-name="incompatible_theme"><p>' . sprintf( esc_html__( "The current theme doesn't declare HivePress support, if you encounter layout or styling issues please consider using the official %s theme.", 'hivepress' ), '<a href="https://hivepress.io/themes/" target="_blank">ListingHive</a>' ) . '</p></div>';
		}
	}

	/**
	 * Renders tooltip.
	 *
	 * @param string $text Tooltip text.
	 * @return string
	 */
	private function render_tooltip( $text ) {
		$output = '';

		if ( ! empty( $text ) ) {
			$output .= '<div class="hp-tooltip"><span class="hp-tooltip__icon dashicons dashicons-editor-help"></span><div class="hp-tooltip__text">' . esc_html( $text ) . '</div></div>';
		}

		return $output;
	}
}
