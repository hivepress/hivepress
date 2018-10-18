<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages back-end.
 *
 * @class Admin
 */
class Admin extends Component {

	/**
	 * Array of post types.
	 *
	 * @var array
	 */
	private $post_types = [];

	/**
	 * Array of options.
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Array of option pages.
	 *
	 * @var array
	 */
	private $option_pages = [];

	/**
	 * Array of meta boxes.
	 *
	 * @var array
	 */
	private $meta_boxes = [];

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		if ( is_admin() ) {

			// Initialize post types.
			add_action( 'hivepress/component/init_post_types', [ $this, 'init_post_types' ] );

			// Manage pages.
			add_action( 'admin_menu', [ $this, 'add_pages' ] );
			add_filter( 'custom_menu_order', [ $this, 'order_pages' ] );
			add_filter( 'menu_order', [ $this, 'order_pages' ] );

			// Manage options.
			add_action( 'hivepress/component/init_options', [ $this, 'init_options' ] );
			add_action( 'hivepress/core/activate_plugin', [ $this, 'add_options' ] );

			// Register settings.
			add_action( 'admin_init', [ $this, 'register_settings' ] );

			// Manage meta boxes.
			add_action( 'hivepress/component/init_meta_boxes', [ $this, 'init_meta_boxes' ], 10, 2 );
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 2 );
			add_action( 'save_post', [ $this, 'update_meta_box' ], 10, 2 );

			// Add term boxes.
			add_action( 'admin_init', [ $this, 'add_term_boxes' ] );

			// Add post states.
			add_filter( 'display_post_states', [ $this, 'add_post_states' ], 10, 2 );

			// Filter comment types.
			add_filter( 'comments_clauses', [ $this, 'filter_comment_types' ] );

			// Init media.
			add_action( 'admin_enqueue_scripts', [ $this, 'init_media' ] );

			// Render notices.
			add_action( 'admin_notices', [ $this, 'render_notices' ] );
		}
	}

	/**
	 * Initializes post types.
	 *
	 * @param array $post_types
	 */
	public function init_post_types( $post_types ) {
		$this->post_types = array_merge( $this->post_types, array_keys( $post_types ) );
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
	 * @param array $menu
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

				foreach ( $this->post_types as $post_type ) {
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
	 * @param string $name
	 * @param array  $args
	 */
	public function __call( $name, $args ) {
		parent::__call( $name, $args );

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

			// Validate setting.
		} elseif ( strpos( $name, 'validate_' ) === 0 ) {
			return $this->validate_setting( str_replace( 'validate_', '', $name ), $args[0] );
		}
	}

	/**
	 * Initializes options.
	 *
	 * @param array $options
	 */
	public function init_options( $options ) {
		$this->options = hp_merge_arrays( $this->options, $options );

		// Set option pages.
		foreach ( $options as $tab_id => $tab ) {
			foreach ( $tab['sections'] as $section_id => $section ) {
				foreach ( $section['fields'] as $field_id => $field ) {
					if ( strpos( $field_id, 'page_' ) === 0 ) {
						$page_id = absint( get_option( hp_prefix( $field_id ) ) );

						if ( 0 !== $page_id ) {
							$this->option_pages[ $page_id ] = $field['name'];
						}
					}
				}
			}
		}
	}

	/**
	 * Adds options.
	 */
	public function add_options() {
		foreach ( $this->options as $tab ) {
			foreach ( $tab['sections'] as $section ) {
				foreach ( $section['fields'] as $option_id => $option ) {
					add_option( hp_prefix( $option_id ), hp_get_array_value( $option, 'default', '' ) );
				}
			}
		}
	}

	/**
	 * Registers settings.
	 */
	public function register_settings() {
		global $pagenow;

		if ( 'options.php' === $pagenow || ( 'admin.php' === $pagenow && 'hp_settings' === hp_get_array_value( $_GET, 'page' ) ) ) {

			// Get current tab.
			$tab = hp_get_array_value( $this->options, $this->get_settings_tab() );

			if ( ! is_null( $tab ) ) {

				// Sort sections.
				$tab['sections'] = hp_sort_array( $tab['sections'] );

				foreach ( $tab['sections'] as $section_id => $section ) {

					// Add settings section.
					add_settings_section( $section_id, esc_html( hp_get_array_value( $section, 'name' ) ), [ $this, 'render_settings_section' ], 'hp_settings' );

					// Sort settings.
					$section['fields'] = hp_sort_array( hp_get_array_value( $section, 'fields', [] ) );

					// Register settings.
					foreach ( $section['fields'] as $option_id => $option ) {
						$option_id         = hp_prefix( $option_id );
						$option['default'] = get_option( $option_id );

						add_settings_field( $option_id, esc_html( $option['name'] ) . $this->render_tooltip( hp_get_array_value( $option, 'description' ) ), [ $this, 'render_settings_field' ], 'hp_settings', $section_id, array_merge( $option, [ 'id' => $option_id ] ) );
						register_setting( 'hp_settings', $option_id, [ $this, 'validate_' . hp_unprefix( $option_id ) ] );
					}
				}
			}
		}
	}

	/**
	 * Validates setting.
	 *
	 * @param string $id
	 * @return mixed
	 */
	private function validate_setting( $id ) {

		// Get current tab.
		$tab = hp_get_array_value( $this->options, $this->get_settings_tab() );

		// Get setting.
		$setting = false;

		if ( ! is_null( $tab ) ) {
			foreach ( $tab['sections'] as $section_id => $section ) {
				foreach ( $section['fields'] as $option_id => $option ) {
					if ( $option_id === $id ) {
						$setting = $option;

						break 2;
					}
				}
			}
		}

		// Validate setting.
		if ( false !== $setting ) {
			$setting_id = hp_prefix( $id );

			$value = hivepress()->form->validate_field( $setting, hp_get_array_value( $_POST, $setting_id ) );

			if ( false !== $value ) {
				return $value;
			} else {
				foreach ( hivepress()->form->get_messages() as $message ) {
					add_settings_error( $setting_id, $setting_id . '_error', esc_html( $message['text'] ), $message['type'] );
				}

				hivepress()->form->clear_messages();

				return get_option( $setting_id );
			}
		}

		return false;
	}

	/**
	 * Gets settings tabs.
	 *
	 * @return array
	 */
	private function get_settings_tabs() {
		return array_map(
			function( $section ) {
				return hp_get_array_value( $section, 'name' );
			},
			hp_sort_array( $this->options )
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
		$tabs = array_keys( hp_sort_array( $this->options ) );

		$first_tab   = hp_get_array_value( $tabs, 0 );
		$current_tab = hp_get_array_value( $_GET, 'tab', $first_tab );

		// Set the default tab.
		if ( ! in_array( $current_tab, $tabs, true ) ) {
			$current_tab = $first_tab;
		}

		return $current_tab;
	}

	/**
	 * Renders settings section.
	 *
	 * @param array $args
	 */
	public function render_settings_section( $args ) {

		// Get current tab.
		$tab = hp_get_array_value( $this->options, $this->get_settings_tab() );

		if ( ! is_null( $tab ) ) {

			// Get current section.
			$section = hp_get_array_value( $tab['sections'], $args['id'] );

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
	 * @param array $args
	 */
	public function render_settings_field( $args ) {
		echo hivepress()->form->render_field( $args['id'], $args );
	}

	/**
	 * Initializes meta boxes.
	 *
	 * @param array  $meta_boxes
	 * @param string $component_name
	 */
	public function init_meta_boxes( $meta_boxes, $component_name ) {
		$this->meta_boxes = array_merge(
			$this->meta_boxes,
			array_combine(
				array_map(
					function( $meta_box_name ) use ( $component_name ) {
						return $component_name . '__' . $meta_box_name;
					},
					array_keys( $meta_boxes )
				),
				$meta_boxes
			)
		);
	}

	/**
	 * Adds meta boxes.
	 *
	 * @param string  $post_type
	 * @param WP_Post $post
	 */
	public function add_meta_boxes( $post_type, $post ) {
		foreach ( $this->meta_boxes as $meta_box_id => $meta_box ) {
			if ( hp_prefix( $meta_box['screen'] ) === $post_type ) {

				// Filter fields.
				$meta_box['fields'] = apply_filters( "hivepress/admin/meta_box_fields/{$meta_box_id}", $meta_box['fields'], [ 'post_id' => $post->ID ] );

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
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function update_meta_box( $post_id, $post ) {
		global $pagenow;

		if ( 'post.php' === $pagenow ) {
			foreach ( $this->meta_boxes as $meta_box_id => $meta_box ) {
				$screen = hp_prefix( $meta_box['screen'] );

				if ( $screen === $post->post_type || ( is_array( $screen ) && in_array( $post->post_type, $screen, true ) ) ) {

					// Filter fields.
					$meta_box['fields'] = apply_filters( "hivepress/admin/meta_box_fields/{$meta_box_id}", $meta_box['fields'], [ 'post_id' => $post_id ] );

					foreach ( $meta_box['fields'] as $field_id => $field ) {

						// Validate field.
						$value = hivepress()->form->validate_field( $field, hp_get_array_value( $_POST, hp_prefix( $field_id ) ) );

						// Update meta value.
						if ( false !== $value ) {
							update_post_meta( $post_id, hp_prefix( $field_id ), $value );
						}
					}
				}
			}
		}
	}

	/**
	 * Renders meta box fields.
	 *
	 * @param WP_Post $post
	 * @param array   $args
	 */
	public function render_meta_box( $post, $args ) {
		$output = '';

		// Get meta box.
		$meta_box = hp_get_array_value( $this->meta_boxes, hp_unprefix( $args['id'] ) );

		if ( ! is_null( $meta_box ) ) {

			// Get meta box ID.
			$meta_box_id = hp_unprefix( $args['id'] );

			// Filter fields.
			$meta_box['fields'] = apply_filters( "hivepress/admin/meta_box_fields/{$meta_box_id}", $meta_box['fields'], [ 'post_id' => $post->ID ] );

			// Sort fields.
			$meta_box['fields'] = hp_sort_array( $meta_box['fields'] );

			// Render fields.
			$output .= '<table class="hp-form form-table">';

			foreach ( $meta_box['fields'] as $field_id => $field ) {

				// Get field value.
				$value = get_post_meta( $post->ID, hp_prefix( $field_id ), true );

				if ( '' === $value ) {
					$value = null;
				}

				if ( 'hidden' === $field['type'] ) {

					// Render field.
					$output .= hivepress()->form->render_field( hp_prefix( $field_id ), $field, $value );
				} else {
					$output .= '<tr>';

					// Render field label.
					$output .= '<th scope="row">' . esc_html( $field['name'] ) . $this->render_tooltip( hp_get_array_value( $field, 'description' ) ) . '</th>';

					// Set field parent.
					if ( isset( $field['parent'] ) ) {
						$field['attributes'] = [
							'class'       => 'hp-js-field',
							'data-parent' => is_array( $field['parent'] ) ? wp_json_encode( array_combine( hp_prefix( array_keys( $field['parent'] ) ), $field['parent'] ) ) : hp_prefix( $field['parent'] ),
						];
					}

					// Render field.
					$output .= '<td>' . hivepress()->form->render_field( hp_prefix( $field_id ), $field, $value ) . '</td>';

					$output .= '</tr>';
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
		foreach ( $this->meta_boxes as $meta_box_id => $meta_box ) {
			if ( taxonomy_exists( hp_prefix( $meta_box['screen'] ) ) ) {
				$taxonomy = hp_prefix( $meta_box['screen'] );

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
	 * @param int $term_id
	 */
	public function update_term_box( $term_id ) {

		// Get term.
		$term = get_term( $term_id );

		if ( ! is_null( $term ) ) {
			foreach ( $this->meta_boxes as $meta_box_id => $meta_box ) {
				$screen = hp_prefix( $meta_box['screen'] );

				if ( ! is_array( $screen ) && taxonomy_exists( $screen ) && $screen === $term->taxonomy ) {

					// Filter fields.
					$meta_box['fields'] = apply_filters( "hivepress/admin/meta_box_fields/{$meta_box_id}", $meta_box['fields'], [ 'term_id' => $term->term_id ] );

					foreach ( $meta_box['fields'] as $field_id => $field ) {

						// Validate field.
						$value = hivepress()->form->validate_field( $field, hp_get_array_value( $_POST, hp_prefix( $field_id ) ) );

						// Update meta value.
						if ( false !== $value ) {
							update_term_meta( $term->term_id, hp_prefix( $field_id ), $value );
						}
					}
				}
			}
		}
	}

	/**
	 * Renders term box fields.
	 *
	 * @param mixed  $term
	 * @param string $taxonomy
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

		foreach ( $this->meta_boxes as $meta_box_id => $meta_box ) {
			$screen = hp_prefix( $meta_box['screen'] );

			if ( ! is_array( $screen ) && taxonomy_exists( $screen ) && $screen === $taxonomy ) {

				// Filter fields.
				$meta_box['fields'] = apply_filters( "hivepress/admin/meta_box_fields/{$meta_box_id}", $meta_box['fields'], [ 'term_id' => $term_id ] );

				// Sort fields.
				$meta_box['fields'] = hp_sort_array( $meta_box['fields'] );

				foreach ( $meta_box['fields'] as $field_id => $field ) {
					if ( ! is_object( $term ) ) {
						$output .= '<div class="form-field">';

						// Render label.
						$output .= '<label for="' . esc_attr( $field_id ) . '">' . esc_html( $field['name'] ) . '</label>';

						// Render field.
						$output .= hivepress()->form->render_field(
							hp_prefix( $field_id ),
							hp_merge_arrays(
								$field,
								[
									'attributes' => [
										'class' => 'hp-form__field hp-form__field--%type_slug%',
									],
								]
							)
						);

						// Render description.
						if ( isset( $field['description'] ) ) {
							$output .= '<p>' . esc_html( $field['description'] ) . '</p>';
						}

						$output .= '</div>';
					} else {
						$output .= '<tr class="form-field">';

						// Render label.
						$output .= '<th scope="row"><label for="' . esc_attr( $field_id ) . '">' . esc_html( $field['name'] ) . '</label></th>';
						$output .= '<td>';

						// Get field value.
						$value = get_term_meta( $term->term_id, hp_prefix( $field_id ), true );

						if ( '' === $value ) {
							$value = null;
						}

						// Render field.
						$output .= hivepress()->form->render_field(
							hp_prefix( $field_id ),
							hp_merge_arrays(
								$field,
								[
									'default'    => $value,
									'attributes' => [
										'class' => 'hp-form__field hp-form__field--%type_slug%',
									],
								]
							)
						);

						// Render description.
						if ( isset( $field['description'] ) ) {
							$output .= '<p class="description">' . esc_html( $field['description'] ) . '</p>';
						}

						$output .= '</td>';
						$output .= '</tr>';
					}
				}
			}
		}

		echo $output;
	}

	/**
	 * Gets add-ons.
	 *
	 * @param string $status
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

					$addons[ $index ]->name = str_replace( HP_CORE_NAME . ' ', '', $addon->name );
					$addons[ $index ]       = (object) array_merge( (array) $addon, $addon_status );
				}

				// Cache add-ons.
				set_transient( 'hp_addons', $addons, DAY_IN_SECONDS );
			}
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
	 * Adds post states.
	 *
	 * @param array   $states
	 * @param WP_Post $post
	 * @return array
	 */
	public function add_post_states( $states, $post ) {
		if ( isset( $this->option_pages[ $post->ID ] ) ) {
			$states[] = $this->option_pages[ $post->ID ];
		}

		return $states;
	}

	/**
	 * Filters comment types.
	 *
	 * @param array $clauses
	 * @return array
	 */
	public function filter_comment_types( $clauses ) {
		global $pagenow;

		if ( in_array( $pagenow, [ 'index.php', 'edit-comments.php' ], true ) ) {
			$clauses['where'] .= ' AND comment_type NOT LIKE "hp_%"';
		}

		return $clauses;
	}

	/**
	 * Initializes media.
	 */
	public function init_media() {
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

		if ( 'admin.php' === $pagenow && 'hp_settings' === hp_get_array_value( $_GET, 'page' ) ) {
			settings_errors();
		}
	}

	/**
	 * Renders tooltip.
	 *
	 * @param string $text
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
