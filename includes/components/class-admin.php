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
final class Admin extends Component {

	/**
	 * Array of meta boxes.
	 *
	 * @var array
	 */
	protected $meta_boxes = [];

	/**
	 * Array of post states.
	 *
	 * @var array
	 */
	protected $post_states = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Register post types.
		add_action( 'init', [ $this, 'register_post_types' ] );

		// Register taxonomies.
		add_action( 'init', [ $this, 'register_taxonomies' ] );

		// Disable post redirect.
		add_filter( 'redirect_canonical', [ $this, 'disable_post_redirect' ] );

		if ( is_admin() ) {

			// Add admin pages.
			add_action( 'admin_menu', [ $this, 'add_admin_pages' ] );

			// Order admin pages.
			add_filter( 'custom_menu_order', '__return_true' );
			add_filter( 'menu_order', [ $this, 'order_admin_pages' ] );

			// Initialize settings.
			add_action( 'hivepress/v1/activate', [ $this, 'init_settings' ] );
			add_action( 'hivepress/v1/update', [ $this, 'init_settings' ] );

			// Register settings.
			add_action( 'admin_init', [ $this, 'register_settings' ] );

			// Manage post states.
			add_action( 'init', [ $this, 'register_post_states' ] );
			add_filter( 'display_post_states', [ $this, 'add_post_states' ], 10, 2 );

			// Manage meta boxes.
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 2 );
			add_action( 'save_post', [ $this, 'update_meta_box' ] );

			// Add term boxes.
			add_action( 'admin_init', [ $this, 'add_term_boxes' ] );

			// Hide comments.
			add_filter( 'comments_clauses', [ $this, 'hide_comments' ] );

			// Enqueue scripts.
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// Render notices.
			add_action( 'admin_notices', [ $this, 'render_notices' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Gets option value.
	 *
	 * @param string $name Option name.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get_option( $name, $default = false ) {
		return get_option( hp\prefix( $name ), $default );
	}

	/**
	 * Registers post types.
	 */
	public function register_post_types() {
		foreach ( hivepress()->get_config( 'post_types' ) as $type => $args ) {
			register_post_type( hp\prefix( $type ), $args );
		}
	}

	/**
	 * Registers taxonomies.
	 */
	public function register_taxonomies() {
		foreach ( hivepress()->get_config( 'taxonomies' ) as $taxonomy => $args ) {
			register_taxonomy( hp\prefix( $taxonomy ), hp\prefix( $args['post_type'] ), $args );
		}
	}

	/**
	 * Disables post redirect.
	 *
	 * @param string $url Redirect URL.
	 * @return string
	 */
	public function disable_post_redirect( $url ) {
		foreach ( hivepress()->get_config( 'post_types' ) as $type => $args ) {
			if ( ! hp\get_array_value( $args, 'redirect_canonical', true ) && is_singular( hp\prefix( $type ) ) ) {
				$url = false;

				break;
			}
		}

		return $url;
	}

	/**
	 * Adds admin pages.
	 */
	public function add_admin_pages() {
		global $menu;

		// Add menu separator.
		$menu[] = [ '', 'manage_options', 'hp_separator', '', 'wp-menu-separator' ];

		// Set page title.
		$title = ' &lsaquo; ' . hivepress()->get_name();

		// Add pages.
		add_menu_page( esc_html__( 'Settings', 'hivepress' ) . $title, hivepress()->get_name(), 'manage_options', 'hp_settings', [ $this, 'render_settings' ], hivepress()->get_url() . '/assets/images/logo.svg' );
		add_submenu_page( 'hp_settings', esc_html__( 'Settings', 'hivepress' ) . $title, esc_html__( 'Settings', 'hivepress' ), 'manage_options', 'hp_settings' );
		add_submenu_page( 'hp_settings', esc_html__( 'Extensions', 'hivepress' ) . $title, esc_html__( 'Extensions', 'hivepress' ), 'manage_options', 'hp_extensions', [ $this, 'render_extensions' ] );
	}

	/**
	 * Orders admin pages.
	 *
	 * @param array $menu Menu items.
	 * @return array
	 */
	public function order_admin_pages( $menu ) {
		if ( current_user_can( 'manage_options' ) ) {

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
		}

		return $menu;
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return mixed
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'render_' ) === 0 ) {

			// Render admin page.
			$template_name = hp\sanitize_slug( substr( $name, strlen( 'render_' ) ) );
			$template_path = hivepress()->get_path() . '/templates/admin/' . $template_name . '.php';

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

				return;
			}
		} elseif ( strpos( $name, 'validate_' ) === 0 ) {

			// Validate settings field.
			return $this->validate_settings_field( substr( $name, strlen( 'validate_' ) ), reset( $args ) );
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Initializes settings.
	 */
	public function init_settings() {
		add_option( 'hp_admin_dismissed_notices' );

		foreach ( hivepress()->get_config( 'settings' ) as $tab ) {
			foreach ( $tab['sections'] as $section ) {
				foreach ( $section['fields'] as $field_name => $field ) {
					$autoload = hp\get_array_value( $field, '_autoload', true ) ? 'yes' : 'no';

					add_option( hp\prefix( $field_name ), hp\get_array_value( $field, 'default', '' ), '', $autoload );
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

			if ( empty( $tab ) ) {
				return;
			}

			foreach ( hp\sort_array( $tab['sections'] ) as $section_name => $section ) {

				// Add settings section.
				add_settings_section( $section_name, esc_html( hp\get_array_value( $section, 'title' ) ), [ $this, 'render_settings_section' ], 'hp_settings' );

				// Register settings.
				foreach ( hp\sort_array( $section['fields'] ) as $field_name => $field_args ) {

					// Get field name.
					$field_name = hp\prefix( $field_name );

					// Create field.
					$field = hp\create_class_instance(
						'\HivePress\Fields\\' . $field_args['type'],
						[
							array_merge(
								$field_args,
								[
									'name'    => $field_name,
									'default' => get_option( $field_name ),
								]
							),
						]
					);

					if ( $field ) {

						// Get field label.
						$field_label = '<div><label class="hp-field__label"><span>' . esc_html( $field->get_label() ) . '</span>';

						if ( $field->get_statuses() ) {
							$field_label .= ' <small>(' . esc_html( implode( ', ', $field->get_statuses() ) ) . ')</small>';
						}

						$field_label .= '</label>' . $this->render_tooltip( $field->get_description() ) . '</div>';

						// Add field.
						add_settings_field( $field_name, $field_label, [ $this, 'render_settings_field' ], 'hp_settings', $section_name, $field->get_args() );

						// Register setting.
						register_setting(
							'hp_settings',
							$field_name,
							[
								'sanitize_callback' => [ $this, 'validate_' . hp\unprefix( $field_name ) ],
							]
						);
					}
				}
			}
		}
	}

	/**
	 * Renders settings section.
	 *
	 * @param array $args Section arguments.
	 */
	public function render_settings_section( $args ) {

		// Get current tab.
		$tab = hp\get_array_value( hivepress()->get_config( 'settings' ), $this->get_settings_tab() );

		if ( empty( $tab ) ) {
			return;
		}

		// Get current section.
		$section = hp\get_array_value( $tab['sections'], $args['id'] );

		if ( empty( $section ) ) {
			return;
		}

		// Render description.
		if ( isset( $section['description'] ) ) {
			echo '<p>' . esc_html( $section['description'] ) . '</p>';
		}
	}

	/**
	 * Renders settings field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_settings_field( $args ) {
		$output = '';

		// Create field.
		$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ $args ] );

		if ( $field ) {

			// Render field.
			$output .= $field->render();
		}

		echo $output;
	}

	/**
	 * Validates settings field.
	 *
	 * @param string $name Field name.
	 * @param mixed  $value Field value.
	 * @return mixed
	 */
	protected function validate_settings_field( $name, $value ) {

		// Get current tab.
		$tab = hp\get_array_value( hivepress()->get_config( 'settings' ), $this->get_settings_tab() );

		if ( empty( $tab ) ) {
			return;
		}

		// Get field arguments.
		$field_args = null;

		foreach ( $tab['sections'] as $section_name => $section ) {
			foreach ( $section['fields'] as $field_name => $field ) {
				if ( $field_name === $name ) {
					$field_args = $field;

					break 2;
				}
			}
		}

		if ( empty( $field_args ) ) {
			return;
		}

		// Get field name.
		$field_name = hp\prefix( $name );

		// Create field.
		$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ $field_args ] );

		if ( $field ) {

			// Validate field.
			$field->set_value( $value );

			if ( $field->validate() ) {
				return $field->get_value();
			} else {
				foreach ( $field->get_errors() as $error ) {
					add_settings_error( $field_name, $field_name, esc_html( $error ) );
				}

				return get_option( $field_name );
			}
		}
	}

	/**
	 * Gets settings tabs.
	 *
	 * @return array
	 */
	protected function get_settings_tabs() {
		return array_map(
			function( $tab ) {
				return hp\get_array_value( $tab, 'title' );
			},
			hp\sort_array( hivepress()->get_config( 'settings' ) )
		);
	}

	/**
	 * Gets current settings tab.
	 *
	 * @return mixed
	 */
	protected function get_settings_tab() {
		$current_tab = null;

		// Get all tabs.
		$tabs = array_keys( hp\sort_array( hivepress()->get_config( 'settings' ) ) );

		$first_tab   = reset( $tabs );
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
	protected function get_extensions( $status = 'all' ) {
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
					hivepress()->cache->set_cache( 'extensions', null, $extensions, DAY_IN_SECONDS );
				}
			}
		}

		// Set extension statuses.
		foreach ( $extensions as $extension_index => $extension ) {

			// Get path and status.
			$extension_path   = $extension['slug'] . '/' . $extension['slug'] . '.php';
			$extension_status = install_plugin_install_status( $extension );

			// Set activation status.
			if ( ! in_array( $extension_status['status'], [ 'install', 'update_available' ], true ) && ! is_plugin_active( $extension_path ) ) {
				$extension_status['status'] = 'activate';

				$extension_status['url'] = admin_url(
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

			$extensions[ $extension_index ] = array_merge(
				$extension,
				$extension_status,
				[
					'name' => substr( $extension['name'], strlen( hivepress()->get_name() ) + 1 ),
				]
			);
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
	protected function get_extensions_tabs() {

		// Set tabs.
		$tabs = [
			'all'       => [
				'name'  => esc_html_x( 'All', 'plural', 'hivepress' ),
				'count' => 0,
			],

			'installed' => [
				'name'  => esc_html_x( 'Installed', 'plural', 'hivepress' ),
				'count' => 0,
			],
		];

		// Get extensions.
		$extensions = $this->get_extensions();

		// Set tab counts.
		$tabs['all']['count'] = count( $extensions );

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
	protected function get_extensions_tab() {
		$current_tab = null;

		// Get all tabs.
		$tabs = array_keys( $this->get_extensions_tabs() );

		$first_tab   = reset( $tabs );
		$current_tab = hp\get_array_value( $_GET, 'tab', $first_tab );

		// Set the default tab.
		if ( ! in_array( $current_tab, $tabs, true ) ) {
			$current_tab = $first_tab;
		}

		return $current_tab;
	}

	/**
	 * Gets themes.
	 *
	 * @return array
	 */
	protected function get_themes() {

		// Get cached themes.
		$themes = hivepress()->cache->get_cache( 'themes' );

		if ( is_null( $themes ) ) {
			$themes = [];

			// Query themes.
			$response = json_decode( wp_remote_retrieve_body( wp_remote_get( 'https://hivepress.io/wp-json/hivepress/v1/themes' ) ), true );

			if ( ! is_null( $response ) && isset( $response['data'] ) ) {
				$themes = (array) $response['data'];

				// Cache themes.
				if ( count( $themes ) <= 100 ) {
					hivepress()->cache->set_cache( 'themes', null, $themes, DAY_IN_SECONDS );
				}
			}
		}

		// Filter themes.
		$themes = array_map(
			function( $theme ) {
				return array_merge(
					[
						'name'    => '',
						'slug'    => '',
						'version' => '',
					],
					(array) $theme
				);
			},
			$themes
		);

		return $themes;
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

						if ( $post_id ) {
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
	 * Gets meta boxes.
	 *
	 * @return array
	 */
	protected function get_meta_boxes() {
		if ( empty( $this->meta_boxes ) ) {
			$this->meta_boxes = [];

			foreach ( hivepress()->get_config( 'meta_boxes' ) as $name => $args ) {

				/**
				 * Filters meta box arguments.
				 *
				 * @filter /meta_boxes/{$name}
				 * @description Filters meta box arguments.
				 * @param string $name Meta box name.
				 * @param array $args Meta box arguments.
				 */
				$args = apply_filters( 'hivepress/v1/meta_boxes/' . $name, array_merge( $args, [ 'name' => $name ] ) );

				// Add meta box.
				$this->meta_boxes[ $name ] = $args;
			}
		}

		return $this->meta_boxes;
	}

	/**
	 * Adds meta boxes.
	 *
	 * @param string  $post_type Post type.
	 * @param WP_Post $post Post object.
	 */
	public function add_meta_boxes( $post_type, $post ) {
		foreach ( $this->get_meta_boxes() as $name => $args ) {

			// Get post types.
			$post_types = (array) hp\prefix( $args['screen'] );

			if ( in_array( $post_type, $post_types, true ) && $args['fields'] ) {

				// Add meta box.
				add_meta_box( hp\prefix( $name ), $args['title'], [ $this, 'render_meta_box' ], hp\prefix( $args['screen'] ), hp\get_array_value( $args, 'context', 'normal' ), hp\get_array_value( $args, 'priority', 'default' ) );
			}
		}
	}

	/**
	 * Updates meta box values.
	 *
	 * @param int $post_id Post ID.
	 */
	public function update_meta_box( $post_id ) {
		global $pagenow;

		if ( 'post.php' !== $pagenow ) {
			return;
		}

		// Remove action.
		remove_action( 'save_post', [ $this, 'update_meta_box' ] );

		// Get post type.
		$post_type = get_post_type( $post_id );

		// Update field values.
		foreach ( $this->get_meta_boxes() as $meta_box_name => $meta_box ) {

			// Get post types.
			$post_types = (array) hp\prefix( $meta_box['screen'] );

			if ( in_array( $post_type, $post_types, true ) ) {
				foreach ( $meta_box['fields'] as $field_name => $field_args ) {

					// Create field.
					$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ $field_args ] );

					if ( $field ) {

						// Validate field.
						$field->set_value( hp\get_array_value( $_POST, hp\prefix( $field_name ) ) );

						if ( $field->validate() ) {

							// Update field value.
							if ( ! isset( $field_args['_alias'] ) ) {
								update_post_meta( $post_id, hp\prefix( $field_name ), $field->get_value() );
							} else {
								wp_update_post(
									[
										'ID' => $post_id,
										$field_args['_alias'] => $field->get_value(),
									]
								);
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
		$meta_box = hp\get_array_value( $this->get_meta_boxes(), $meta_box_name );

		if ( $meta_box ) {

			// Render fields.
			$output .= '<table class="form-table hp-form">';

			foreach ( hp\sort_array( $meta_box['fields'] ) as $field_name => $field_args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ array_merge( $field_args, [ 'name' => hp\prefix( $field_name ) ] ) ] );

				if ( $field ) {

					// Get field value.
					$value = null;

					if ( ! isset( $field_args['_alias'] ) ) {
						$value = get_post_meta( $post->ID, hp\prefix( $field_name ), true );
					} else {
						$value = get_post_field( $field_args['_alias'], $post );
					}

					// Set field value.
					$field->set_value( $value );

					if ( 'hidden' === $field->get_display_type() ) {

						// Render field.
						$output .= $field->render();
					} else {
						$output .= '<tr class="hp-form__field hp-form__field--' . esc_attr( hp\sanitize_slug( $field::get_meta( 'name' ) ) ) . '">';

						// Render field label.
						if ( $field->get_label() ) {
							$output .= '<th scope="row"><div><label class="hp-field__label"><span>' . esc_html( $field->get_label() ) . '</span>';

							if ( $field->get_statuses() ) {
								$output .= ' <small>(' . esc_html( implode( ', ', $field->get_statuses() ) ) . ')</small>';
							}

							$output .= '</label>' . $this->render_tooltip( $field->get_description() ) . '</div></th>';
						}

						// Render field.
						if ( $field->get_label() ) {
							$output .= '<td>';
						} else {
							$output .= '<td colspan="2">';
						}

						$output .= $field->render();

						$output .= '</td>';
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

		// Get taxonomies.
		$taxonomies = [];

		foreach ( $this->get_meta_boxes() as $name => $args ) {
			$taxonomies = array_merge( $taxonomies, (array) hp\prefix( $args['screen'] ) );
		}

		$taxonomies = array_unique( $taxonomies );

		// Add term boxes.
		foreach ( $taxonomies as $taxonomy ) {
			if ( taxonomy_exists( $taxonomy ) ) {

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
	 * Updates term box values.
	 *
	 * @param int $term_id Term ID.
	 */
	public function update_term_box( $term_id ) {

		// Get term.
		$term = get_term( $term_id );

		if ( empty( $term ) ) {
			return;
		}

		// Remove actions.
		remove_action( 'edit_' . $term->taxonomy, [ $this, 'update_term_box' ] );
		remove_action( 'create_' . $term->taxonomy, [ $this, 'update_term_box' ] );

		// Update field values.
		foreach ( $this->get_meta_boxes() as $meta_box_name => $meta_box ) {

			// Get taxonomies.
			$taxonomies = (array) hp\prefix( $meta_box['screen'] );

			if ( in_array( $term->taxonomy, $taxonomies, true ) ) {
				foreach ( $meta_box['fields'] as $field_name => $field_args ) {

					// Create field.
					$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ $field_args ] );

					if ( $field ) {

						// Validate field.
						$field->set_value( hp\get_array_value( $_POST, hp\prefix( $field_name ) ) );

						if ( $field->validate() ) {

							// Update field value.
							if ( ! isset( $field_args['_alias'] ) ) {
								update_term_meta( $term->term_id, hp\prefix( $field_name ), $field->get_value() );
							} else {
								wp_update_term(
									$term->term_id,
									$term->taxonomy,
									[
										$field_args['_alias'] => $field->get_value(),
									]
								);
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
		$term_id = null;

		if ( ! is_object( $term ) ) {
			$taxonomy = $term;
		} else {
			$term_id = $term->term_id;
		}

		foreach ( $this->get_meta_boxes() as $meta_box_name => $meta_box ) {

			// Get taxonomies.
			$taxonomies = (array) hp\prefix( $meta_box['screen'] );

			if ( in_array( $taxonomy, $taxonomies, true ) ) {
				foreach ( hp\sort_array( $meta_box['fields'] ) as $field_name => $field_args ) {

					// Create field.
					$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ array_merge( $field_args, [ 'name' => hp\prefix( $field_name ) ] ) ] );

					if ( $field ) {
						if ( ! is_object( $term ) ) {
							$output .= '<div class="form-field">';

							// Render label.
							$output .= '<label class="hp-field__label"><span>' . esc_html( $field->get_label() ) . '</span>';

							if ( $field->get_statuses() ) {
								$output .= ' <small>(' . esc_html( implode( ', ', $field->get_statuses() ) ) . ')</small>';
							}

							$output .= '</label>';

							// Render field.
							$output .= $field->render();

							// Render description.
							if ( $field->get_description() ) {
								$output .= '<p>' . esc_html( $field->get_description() ) . '</p>';
							}

							$output .= '</div>';
						} else {
							$output .= '<tr class="form-field">';

							// Render label.
							$output .= '<th scope="row"><label class="hp-field__label"><span>' . esc_html( $field->get_label() ) . '</span>';

							if ( $field->get_statuses() ) {
								$output .= ' <small>(' . esc_html( implode( ', ', $field->get_statuses() ) ) . ')</small>';
							}

							$output .= '</label></th>';

							// Get field value.
							$value = null;

							if ( ! isset( $field_args['_alias'] ) ) {
								$value = get_term_meta( $term->term_id, hp\prefix( $field_name ), true );
							} else {
								$value = get_term_field( $field_args['_alias'], $term );
							}

							// Set field value.
							$field->set_value( $value );

							// Render field.
							$output .= '<td>';

							$output .= $field->render();

							// Render description.
							if ( $field->get_description() ) {
								$output .= '<p class="description">' . esc_html( $field->get_description() ) . '</p>';
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
	 * Hides comments.
	 *
	 * @param array $query Query arguments.
	 * @return array
	 */
	public function hide_comments( $query ) {
		global $pagenow;

		if ( in_array( $pagenow, [ 'index.php', 'edit-comments.php', 'post.php' ], true ) ) {

			// Get comment types.
			$comment_types = hivepress()->get_config( 'comment_types' );

			// Filter comment types.
			$comment_types = array_filter(
				$comment_types,
				function( $args ) {
					return ! isset( $args['show_ui'] ) || ! $args['show_ui'];
				}
			);

			if ( $comment_types ) {

				// Get comment clause.
				$clause = '"' . implode( '", "', hp\prefix( array_keys( $comment_types ) ) ) . '"';

				// Set comment clause.
				$query['where'] .= ' AND comment_type NOT IN (' . $clause . ')';
			}
		}

		return $query;
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

		$output = '';

		// Get notices.
		$notices = [];

		if ( ! current_theme_supports( 'hivepress' ) ) {
			$notices['incompatible_theme'] = [
				'type' => 'warning',
				'text' => sprintf( esc_html__( 'The current theme doesn\'t declare HivePress support, if you encounter layout or styling issues please consider using the official %s theme.', 'hivepress' ), '<a href="https://hivepress.io/themes/" target="_blank">ListingHive</a>' ),
			];
		} else {
			foreach ( $this->get_themes() as $theme ) {
				if ( get_template() === $theme['slug'] ) {

					// Get notice name.
					$notice_name = 'update_theme_' . $theme['slug'] . '_' . str_replace( '.', '_', $theme['version'] );

					// Add notice.
					if ( version_compare( wp_get_theme()->get( 'Version' ), $theme['version'], '<' ) ) {
						$notices[ $notice_name ] = [
							'type' => 'warning',
							'text' => sprintf( esc_html__( 'A new version of %s theme is available, please update for new features and improvements.', 'hivepress' ), '<a href="https://hivepress.io/themes/" target="_blank">' . esc_html( $theme['name'] ) . '</a>' ),
						];
					}

					break;
				}
			}
		}

		/**
		 * Filters admin notices.
		 *
		 * @filter /admin_notices
		 * @description Filters admin notices.
		 * @param array $notices Admin notices.
		 */
		$notices = apply_filters( 'hivepress/v1/admin_notices', $notices );

		// Remove dismissed notices.
		$notices = array_diff_key( $notices, array_flip( (array) get_option( 'hp_admin_dismissed_notices' ) ) );

		// Render notices.
		foreach ( $notices as $notice_name => $notice ) {
			$output .= '<div class="notice notice-' . esc_attr( $notice['type'] ) . ' is-dismissible" data-component="notice" data-name="' . esc_attr( $notice_name ) . '" data-url="' . esc_url( hivepress()->router->get_url( 'admin_notice_update_action' ) ) . '"><p>' . hp\sanitize_html( $notice['text'] ) . '</p></div>';
		}

		// Render settings errors.
		if ( 'admin.php' === $pagenow && 'hp_settings' === hp\get_array_value( $_GET, 'page' ) ) {
			ob_start();

			settings_errors();
			$output .= ob_get_contents();

			ob_end_clean();
		}

		echo $output;
	}

	/**
	 * Renders tooltip.
	 *
	 * @param string $text Tooltip text.
	 * @return string
	 */
	protected function render_tooltip( $text ) {
		$output = '';

		if ( $text ) {
			$output .= '<div class="hp-tooltip"><span class="hp-tooltip__icon dashicons dashicons-editor-help"></span><div class="hp-tooltip__text">' . esc_html( $text ) . '</div></div>';
		}

		return $output;
	}
}
