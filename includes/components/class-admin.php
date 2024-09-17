<?php
/**
 * Admin component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Fields;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Implements integration with WordPress admin.
 */
final class Admin extends Component {

	/**
	 * Meta boxes.
	 *
	 * @var array
	 */
	protected $meta_boxes = [];

	/**
	 * Post states.
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

		// Share usage data.
		add_action( 'hivepress/v1/events/weekly', [ $this, 'share_usage_data' ] );

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

			// Clear cache.
			add_action( 'update_option_hp_hivepress_license_key', [ $this, 'clear_purchases_cache' ] );
			add_action( 'switch_theme', [ $this, 'clear_themes_cache' ] );
			add_action( 'hivepress/v1/activate', [ $this, 'clear_extensions_cache' ] );

			// Manage post states.
			add_action( 'init', [ $this, 'register_post_states' ] );
			add_filter( 'display_post_states', [ $this, 'add_post_states' ], 10, 2 );

			// Manage meta boxes.
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 2 );
			add_action( 'do_meta_boxes', [ $this, 'remove_meta_boxes' ] );
			add_action( 'save_post', [ $this, 'update_meta_box' ] );

			// Add term boxes.
			add_action( 'admin_init', [ $this, 'add_term_boxes' ] );

			// Manage user boxes.
			add_action( 'show_user_profile', [ $this, 'render_user_boxes' ] );
			add_action( 'edit_user_profile', [ $this, 'render_user_boxes' ] );

			add_action( 'personal_options_update', [ $this, 'update_user_boxes' ] );
			add_action( 'edit_user_profile_update', [ $this, 'update_user_boxes' ] );

			// Check access.
			add_action( 'admin_init', [ $this, 'check_access' ] );

			// Enqueue scripts.
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// Render links.
			add_filter( 'plugin_action_links_hivepress/hivepress.php', [ $this, 'render_links' ] );

			// Render notices.
			add_action( 'admin_notices', [ $this, 'render_notices' ] );

			// Render footer.
			add_action( 'admin_footer', [ $this, 'render_footer' ] );
		}

		parent::__construct( $args );
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
	 * Adds admin pages.
	 */
	public function add_admin_pages() {
		global $menu;

		// Add menu separator.
		$menu[] = [ '', 'manage_options', 'hp_separator', '', 'wp-menu-separator' ];

		// Set page title.
		$title = ' &lsaquo; ' . hivepress()->get_name();

		// Add pages.
		add_menu_page( hivepress()->translator->get_string( 'settings' ) . $title, hivepress()->get_name(), 'manage_options', 'hp_settings', [ $this, 'render_settings' ], hivepress()->get_url() . '/assets/images/logo-light.svg' );
		add_submenu_page( 'hp_settings', hivepress()->translator->get_string( 'settings' ) . $title, hivepress()->translator->get_string( 'settings' ), 'manage_options', 'hp_settings', [ $this, 'render_settings' ], 0 );
		add_submenu_page( 'hp_settings', esc_html__( 'Themes', 'hivepress' ) . $title, esc_html__( 'Themes', 'hivepress' ), 'install_themes', 'hp_themes', [ $this, 'render_themes' ] );
		add_submenu_page( 'hp_settings', esc_html__( 'Extensions', 'hivepress' ) . $title, esc_html__( 'Extensions', 'hivepress' ), 'install_plugins', 'hp_extensions', [ $this, 'render_extensions' ] );

		// Add counts.
		foreach ( $menu as $item_index => $item_args ) {
			if ( isset( $item_args[2] ) && strpos( $item_args[2], 'edit.php?post_type=hp_' ) === 0 ) {
				$item_count = wp_count_posts( hp\get_last_array_value( explode( '=', $item_args[2] ) ) );

				if ( property_exists( $item_count, 'pending' ) && $item_count->pending ) {
					$menu[ $item_index ][0] .= ' <span class="update-plugins count-' . esc_attr( $item_count->pending ) . '"><span class="plugin-count">' . esc_html( $item_count->pending ) . '</span></span>';
				}
			}
		}
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

			foreach ( hivepress()->get_config( 'post_types' ) as $post_type => $post_type_args ) {
				if ( ! isset( $post_type_args['show_in_menu'] ) ) {
					$pages[] = 'edit.php?post_type=' . hp\prefix( $post_type );
				}
			}

			// Filter menu items.
			$menu = array_filter(
				$menu,
				function( $name ) use ( $pages ) {
					return ! in_array( $name, $pages, true );
				}
			);

			// Insert menu items.
			array_splice( $menu, array_search( 'separator2', $menu, true ) - ( count( $pages ) - 2 ), 0, $pages );
		}

		return $menu;
	}

	/**
	 * Catches calls to undefined methods.
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
				} elseif ( 'themes' === $template_name ) {
					$themes = $this->get_themes();
				}

				include $template_path;

				return;
			}
		} elseif ( strpos( $name, 'validate_' ) === 0 ) {

			// Validate settings field.
			return $this->validate_settings_field( substr( $name, strlen( 'validate_' ) ), hp\get_first_array_value( $args ) );
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
					if ( ! hp\get_array_value( $field, 'readonly' ) ) {
						$autoload = hp\get_array_value( $field, '_autoload', true ) ? 'yes' : 'no';

						add_option( hp\prefix( $field_name ), hp\get_array_value( $field, 'default', '' ), '', $autoload );
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

			if ( empty( $tab ) ) {
				return;
			}

			foreach ( hp\sort_array( $tab['sections'] ) as $section_name => $section ) {
				if ( $section['fields'] ) {

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
										'default' => get_option( $field_name, hp\get_array_value( $field_args, 'default' ) ),
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
							if ( 'options.php' !== $pagenow || ! hp\get_array_value( $field_args, 'readonly' ) ) {
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

			if ( 'hp_settings' === hp\get_array_value( $_POST, 'option_page' ) ) {

				// Refresh permalinks.
				hivepress()->router->flush_rewrite_rules();
			}
		}

		if ( in_array( $pagenow, [ 'options.php', 'options-media.php' ], true ) ) {
			foreach ( hivepress()->get_config( 'image_sizes' ) as $image_size_name => $image_size ) {
				if ( isset( $image_size['label'] ) ) {

					// Get field name.
					$field_name = hp\prefix( 'image_size_' . $image_size_name );

					// Add field.
					add_settings_field(
						$field_name,
						$image_size['label'],
						[ $this, 'render_settings_field' ],
						'media',
						'default',
						[
							'name'    => $field_name,
							'type'    => 'image_size',
							'default' => get_option( $field_name, $image_size ),
						]
					);

					// Register setting.
					register_setting(
						'media',
						$field_name,
						[
							'sanitize_callback' => [ $this, 'validate_image_size_field' ],
						]
					);
				}
			}
		}

		if ( 'options-permalink.php' === $pagenow ) {

			// Get permalinks.
			$permalinks     = (array) get_option( 'hp_permalinks', [] );
			$new_permalinks = $permalinks;

			// Get post types and taxonomies.
			$types = array_filter(
				array_merge(
					get_post_types( [ 'public' => true ], 'objects' ),
					get_taxonomies( [ 'public' => true ], 'objects' )
				),
				function( $type_args ) {
					return strpos( $type_args->name, 'hp_' ) === 0;
				}
			);

			foreach ( $types as $type_name => $type_args ) {

				// Get field name.
				$type_name   = hp\unprefix( $type_name );
				$option_name = $type_name . '_slug';
				$field_name  = hp\prefix( $option_name );

				// Get field label.
				$field_label = $type_args->labels->singular_name;

				if ( property_exists( $type_args, 'object_type' ) ) {
					$type_group = hp\unprefix( hp\get_first_array_value( $type_args->object_type ) );

					if ( hivepress()->translator->get_string( $type_group ) ) {
						$field_label .= ' (' . hivepress()->translator->get_string( $type_group ) . ')';
					}
				}

				// Add field.
				add_settings_field(
					$field_name,
					$field_label,
					[ $this, 'render_settings_field' ],
					'permalink',
					'optional',
					[
						'name'       => $field_name,
						'type'       => 'text',
						'max_length' => 64,
						'default'    => hp\get_array_value( $permalinks, $option_name ),

						'attributes' => [
							'class' => [ 'regular-text', 'code' ],
						],
					]
				);

				if ( isset( $_POST[ $field_name ] ) ) {

					// Get field value.
					$field_value = sanitize_title( wp_unslash( $_POST[ $field_name ] ) );

					if ( $field_value ) {

						// Set permalink.
						$new_permalinks[ $option_name ] = urldecode( $field_value );
					} else {
						unset( $new_permalinks[ $option_name ] );
					}
				}
			}

			// Update permalinks.
			if ( $new_permalinks !== $permalinks ) {
				update_option( 'hp_permalinks', $new_permalinks );
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
			echo '<p>' . hp\sanitize_html( $section['description'] ) . '</p>';
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

			// Get field attributes.
			$attributes = [];

			if ( $field->get_arg( '_parent' ) ) {
				$attributes['data-component'] = 'field';
				$attributes['data-parent']    = hp\prefix( $field->get_arg( '_parent' ) );
			}

			// Render field.
			$output .= '<div ' . hp\html_attributes( $attributes ) . '>' . $field->render() . '</div>';
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
	 * Validates image size field.
	 *
	 * @param mixed $value Field value.
	 * @return mixed
	 */
	public function validate_image_size_field( $value ) {

		// Create field.
		$field = new Fields\Image_Size();

		// Validate field.
		$field->set_value( $value );

		if ( $field->validate() ) {
			return $field->get_value();
		}
	}

	/**
	 * Gets settings page tabs.
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
	 * Gets current settings page tab.
	 *
	 * @return string
	 */
	protected function get_settings_tab() {
		$current_tab = null;

		// Get all tabs.
		$tabs = array_keys( hp\sort_array( hivepress()->get_config( 'settings' ) ) );

		$first_tab   = hp\get_first_array_value( $tabs );
		$current_tab = hp\get_array_value( $_GET, 'tab', $first_tab );

		// Set the default tab.
		if ( ! in_array( $current_tab, $tabs, true ) ) {
			$current_tab = $first_tab;
		}

		return $current_tab;
	}

	/**
	 * Gets HivePress purchases.
	 *
	 * @return array
	 */
	protected function get_purchases() {

		// Get license key.
		$license_key = implode( ',', explode( "\n", get_option( 'hp_hivepress_license_key' ) ) );

		if ( ! $license_key ) {
			return [];
		}

		// Get cached purchases.
		$purchases = hivepress()->cache->get_cache( 'purchases' );

		if ( is_null( $purchases ) ) {
			$purchases = [];

			// Get API response.
			$response = json_decode(
				wp_remote_retrieve_body(
					wp_remote_get(
						'https://store.hivepress.io/api/v1/products?' . http_build_query(
							[
								'license_key' => $license_key,
							]
						)
					)
				),
				true
			);

			if ( is_array( $response ) && isset( $response['data'] ) ) {
				foreach ( $response['data'] as $product ) {

					// Add purchase.
					$purchases[ $product['slug'] ] = [
						'name' => $product['name'],
						'slug' => $product['slug'],
						'type' => $product['type'],
					];
				}

				// Cache purchases.
				hivepress()->cache->set_cache( 'purchases', null, $purchases, DAY_IN_SECONDS );
			}
		}

		return $purchases;
	}

	/**
	 * Clears cached purchases.
	 */
	public function clear_purchases_cache() {
		hivepress()->cache->delete_cache( 'purchases' );
	}

	/**
	 * Gets HivePress themes.
	 *
	 * @return array
	 */
	protected function get_themes() {

		// Get cached themes.
		$themes = hivepress()->cache->get_cache( 'themes' );

		if ( is_null( $themes ) ) {
			$themes = [];

			// Get paid themes.
			$paid_themes = json_decode(
				wp_remote_retrieve_body(
					wp_remote_get( 'https://store.hivepress.io/api/v1/products?type=theme' )
				),
				true
			);

			if ( is_array( $paid_themes ) && isset( $paid_themes['data'] ) ) {

				// Add themes.
				$themes = array_merge( $themes, $paid_themes['data'] );
			}

			// Get free themes.
			$free_themes = themes_api(
				'query_themes',
				[
					'author' => 'hivepress',
				]
			);

			if ( ! is_wp_error( $free_themes ) ) {

				// Add themes.
				$themes = array_merge(
					$themes,
					array_map(
						function( $theme ) {
							$theme = (array) $theme;

							return array_merge(
								$theme,
								[
									'buy_url'   => $theme['homepage'],
									'image_url' => $theme['screenshot_url'],
								]
							);
						},
						$free_themes->themes
					)
				);
			}

			// Set theme URLs.
			$themes = array_map(
				function( $theme ) {
					$slug = sanitize_key( $theme['slug'] );

					$theme['preview_url'] = 'https://' . $slug . '.hivepress.io/';
					$theme['buy_url']     = 'https://hivepress.io/themes/' . $slug . '/?utm_medium=referral&utm_source=dashboard';

					return $theme;
				},
				$themes
			);

			// Cache themes.
			if ( is_array( $paid_themes ) && isset( $paid_themes['data'] ) && ! is_wp_error( $free_themes ) && count( $themes ) <= 100 ) {
				hivepress()->cache->set_cache( 'themes', null, $themes, DAY_IN_SECONDS );
			}
		}

		// Set theme statuses.
		$all_themes    = array_keys( wp_get_themes() );
		$current_theme = get_template();

		foreach ( $themes as $theme_index => $theme ) {
			$slug = sanitize_key( $theme['slug'] );

			// Get status and URL.
			$status = 'active';
			$url    = '';

			if ( ! in_array( $slug, $all_themes, true ) ) {
				$status = 'install';
				$url    = admin_url(
					'update.php?' . http_build_query(
						[
							'action'   => 'install-theme',
							'theme'    => $slug,
							'_wpnonce' => wp_create_nonce( 'install-theme_' . $slug ),
						]
					)
				);
			} elseif ( $slug !== $current_theme ) {
				$status = 'activate';
				$url    = admin_url(
					'themes.php?' . http_build_query(
						[
							'action'     => 'activate',
							'stylesheet' => $slug,
							'_wpnonce'   => wp_create_nonce( 'switch-theme_' . $slug ),
						]
					)
				);
			}

			// Set status and URL.
			$themes[ $theme_index ]['status'] = $status;
			$themes[ $theme_index ]['url']    = $url;
		}

		return $themes;
	}

	/**
	 * Clears cached themes.
	 */
	public function clear_themes_cache() {
		hivepress()->cache->delete_cache( 'themes' );
	}

	/**
	 * Gets HivePress extensions.
	 *
	 * @param string $status Extensions status.
	 * @return array
	 */
	protected function get_extensions( $status = 'all' ) {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		// Get cached extensions.
		$extensions = hivepress()->cache->get_cache( 'all_extensions' );

		if ( is_null( $extensions ) ) {
			$extensions = [];

			// Get paid extensions.
			$paid_extensions = json_decode(
				wp_remote_retrieve_body(
					wp_remote_get( 'https://store.hivepress.io/api/v1/products?type=extension' )
				),
				true
			);

			if ( is_array( $paid_extensions ) && isset( $paid_extensions['data'] ) ) {

				// Add extensions.
				$extensions = array_merge( $extensions, $paid_extensions['data'] );
			}

			// Get free extensions.
			$free_extensions = plugins_api(
				'query_plugins',
				[
					'author' => 'hivepress',

					'fields' => [
						'icons' => true,
					],
				]
			);

			if ( ! is_wp_error( $free_extensions ) ) {

				// Add extensions.
				$extensions = array_merge(
					$extensions,
					array_filter(
						array_map(
							function( $extension ) {
								$extension = (array) $extension;

								return array_merge(
									$extension,
									[
										'description' => $extension['short_description'],
										'image_url'   => hp\get_last_array_value( $extension['icons'] ),
									]
								);
							},
							$free_extensions->plugins
						),
						function( $extension ) {
							return ! in_array( $extension['slug'], [ 'hivepress', 'hivepress-authentication' ], true );
						}
					)
				);
			}

			// Get referral ID.
			$referral = null;

			$stylesheet = get_template_directory() . '/style.css';

			if ( file_exists( $stylesheet ) ) {
				$referral = sanitize_key( hp\get_first_array_value( get_file_data( $stylesheet, [ 'HivePress ID' ] ) ) );
			}

			// Set extension URLs.
			$extensions = array_map(
				function( $extension ) use ( $referral ) {
					$path = preg_replace( '/^hivepress-/', '', $extension['slug'] ) . '/?utm_medium=referral&utm_source=dashboard';

					if ( $referral ) {
						$path .= '&ref=' . $referral;
					}

					return array_merge(
						$extension,
						[
							'buy_url'     => 'https://hivepress.io/extensions/' . $path,
							'docs_url'    => 'https://hivepress.io/docs/extensions/' . $path,
							'support_url' => 'https://community.hivepress.io/?utm_medium=referral&utm_source=dashboard',
						]
					);
				},
				$extensions
			);

			// Cache extensions.
			if ( is_array( $paid_extensions ) && isset( $paid_extensions['data'] ) && ! is_wp_error( $free_extensions ) && count( $extensions ) <= 100 ) {
				hivepress()->cache->set_cache( 'all_extensions', null, $extensions, DAY_IN_SECONDS );
			}
		}

		// Set extension statuses.
		foreach ( $extensions as $extension_index => $extension ) {

			// Set bundle status.
			if ( 'bundle' === $extension['slug'] ) {
				$extensions[ $extension_index ]['status'] = 'install';

				continue;
			}

			// Get path and status.
			$extension_path   = $extension['slug'] . '/' . $extension['slug'] . '.php';
			$extension_status = install_plugin_install_status( $extension );

			// Set activation status.
			if ( $extension_status['file'] && ! is_plugin_active( $extension_path ) ) {
				$extension_status = array_merge(
					$extension_status,
					[
						'status' => 'activate',
						'url'    => admin_url(
							'plugins.php?' . http_build_query(
								[
									'action'   => 'activate',
									'plugin'   => $extension_path,
									'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $extension_path ),
								]
							)
						),
					]
				);
			}

			unset( $extension_status['version'] );

			$extensions[ $extension_index ] = array_merge(
				$extension,
				$extension_status,
				[
					'name' => trim( str_replace( hivepress()->get_name(), '', $extension['name'] ) ),
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
	 * Clears cached extensions.
	 */
	public function clear_extensions_cache() {
		hivepress()->cache->delete_cache( 'all_extensions' );
	}

	/**
	 * Gets extensions page tabs.
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
		$tabs['all']['count'] = count( $extensions ) - 1;

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
	 * Gets current extensions page tab.
	 *
	 * @return string
	 */
	protected function get_extensions_tab() {
		$current_tab = null;

		// Get all tabs.
		$tabs = array_keys( $this->get_extensions_tabs() );

		$first_tab   = hp\get_first_array_value( $tabs );
		$current_tab = hp\get_array_value( $_GET, 'tab', $first_tab );

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
		global $pagenow;

		if ( 'edit.php' === $pagenow ) {
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
			$states[] = esc_html( $this->post_states[ $post->ID ] );
		}

		return $states;
	}

	/**
	 * Gets meta boxes.
	 *
	 * @param string $screen Screen name.
	 * @return array
	 */
	protected function get_meta_boxes( $screen ) {

		// Check screen.
		if ( 'user' === $screen ) {
			$screen = hp\prefix( $screen );
		}

		if ( empty( $this->meta_boxes ) ) {
			$this->meta_boxes = [];

			foreach ( hivepress()->get_config( 'meta_boxes' ) as $name => $args ) {
				if ( in_array( $screen, (array) hp\prefix( $args['screen'] ), true ) ) {

					/**
					 * Filters meta box properties. The dynamic part of the hook refers to the meta box name (e.g. `listing_settings`). You can check the available meta boxes in the `includes/configs/meta-boxes.php` file of HivePress.
					 *
					 * @hook hivepress/v1/meta_boxes/{meta_box_name}
					 * @param {array} $props Meta box properties.
					 * @return {array} Meta box properties.
					 */
					$args = apply_filters( 'hivepress/v1/meta_boxes/' . $name, array_merge( $args, [ 'name' => $name ] ) );

					// Set default arguments.
					$args = array_merge(
						[
							'title'    => '',
							'screen'   => '',
							'context'  => 'normal',
							'priority' => 'default',
							'fields'   => [],
							'blocks'   => [],
						],
						$args
					);

					// Set field aliases.
					foreach ( $args['fields'] as $field_name => $field ) {
						if ( ! isset( $field['_alias'] ) ) {
							$args['fields'][ $field_name ] = array_merge(
								$field,
								[
									'_external' => true,
									'_alias'    => hp\prefix( $field_name ),
								]
							);
						}
					}

					// Add meta box.
					$this->meta_boxes[ $name ] = $args;
				}
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

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		// Add meta boxes.
		foreach ( $this->get_meta_boxes( $post_type ) as $name => $args ) {
			if ( $args['fields'] || $args['blocks'] ) {
				add_meta_box( hp\prefix( $name ), $args['title'], [ $this, 'render_meta_box' ], hp\prefix( $args['screen'] ), $args['context'], $args['priority'] );
			}
		}
	}

	/**
	 * Removes meta boxes.
	 */
	public function remove_meta_boxes() {

		// Check post type.
		$post_type = get_post_type();

		if ( strpos( $post_type, 'hp_' ) !== 0 ) {
			return;
		}

		// Remove meta box.
		if ( array_key_exists( hp\unprefix( $post_type . '_images' ), hivepress()->get_config( 'meta_boxes' ) ) ) {
			remove_meta_box( 'postimagediv', $post_type, 'side' );
		}
	}

	/**
	 * Updates meta box values.
	 *
	 * @param int $post_id Post ID.
	 */
	public function update_meta_box( $post_id ) {

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check action.
		if ( hp\get_array_value( $_POST, 'action' ) !== 'editpost' ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post ID.
		if ( get_the_ID() !== $post_id ) {
			return;
		}

		// Remove action.
		remove_action( 'save_post', [ $this, 'update_meta_box' ] );

		// Update field values.
		foreach ( $this->get_meta_boxes( get_post_type() ) as $meta_box ) {
			foreach ( $meta_box['fields'] as $field_name => $field_args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ $field_args ] );

				if ( $field && ! $field->is_disabled() ) {

					// Validate field.
					$field->set_value( hp\get_array_value( $_POST, hp\prefix( $field_name ) ) );

					if ( $field->validate() ) {

						// Update field value.
						if ( $field->get_arg( '_external' ) ) {
							$taxonomy = hp\get_array_value( $field->get_arg( 'option_args' ), 'taxonomy' );

							if ( in_array( $field->get_value(), [ null, false ], true ) ) {
								if ( $taxonomy ) {
									wp_set_post_terms( $post_id, [], $taxonomy );
								} else {
									delete_post_meta( $post_id, $field->get_arg( '_alias' ) );
								}
							} elseif ( ! $field->get_arg( 'readonly' ) ) {
								if ( $taxonomy ) {
									wp_set_post_terms( $post_id, (array) $field->get_value(), $taxonomy );
								} else {
									update_post_meta( $post_id, $field->get_arg( '_alias' ), $field->get_value() );
								}
							}
						} else {
							wp_update_post(
								[
									'ID' => $post_id,
									$field->get_arg( '_alias' ) => $field->get_value(),
								]
							);
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
		$meta_box = hp\get_array_value( $this->get_meta_boxes( $post->post_type ), $meta_box_name );

		if ( $meta_box ) {

			// Render blocks.
			if ( $meta_box['blocks'] ) {
				foreach ( hp\sort_array( $meta_box['blocks'] ) as $block_name => $block_args ) {

					// Create block.
					$block = hp\create_class_instance( '\HivePress\Blocks\\' . $block_args['type'], [ array_merge( $block_args, [ 'name' => $block_name ] ) ] );

					if ( $block ) {

						// Render block.
						$output .= $block->render();
					}
				}
			}

			// Render fields.
			if ( $meta_box['fields'] ) {
				$output .= '<table class="form-table hp-form hp-form--table" data-model="' . esc_attr( hp\unprefix( $post->post_type ) ) . '" data-id="' . esc_attr( $post->ID ) . '">';

				foreach ( hp\sort_array( $meta_box['fields'] ) as $field_name => $field_args ) {

					// Get field name.
					$field_name = hp\prefix( $field_name );

					// Create field.
					$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ array_merge( $field_args, [ 'name' => $field_name ] ) ] );

					if ( $field ) {

						// Get field value.
						$value = '';

						if ( isset( $args['defaults'] ) ) {
							$value = hp\get_array_value( $args['defaults'], $field_name, '' );
						} elseif ( $field->get_arg( '_external' ) ) {
							$taxonomy = hp\get_array_value( $field->get_arg( 'option_args' ), 'taxonomy' );

							if ( $taxonomy ) {
								$value = wp_get_post_terms( $post->ID, $taxonomy, [ 'fields' => 'ids' ] );
							} else {
								$value = get_post_meta( $post->ID, $field->get_arg( '_alias' ), true );
							}
						} else {
							$value = get_post_field( $field->get_arg( '_alias' ), $post );
						}

						// Set field value.
						if ( '' !== $value ) {
							$field->set_value( $value );
						}

						if ( 'hidden' === $field->get_display_type() ) {

							// Render field.
							$output .= $field->render();
						} else {

							// Get field attributes.
							$attributes = [
								'class' => 'hp-form__field hp-form__field--' . hp\sanitize_slug( $field->get_display_type() ),
							];

							if ( $field->get_arg( '_parent' ) ) {
								$attributes['data-component'] = 'field';
								$attributes['data-parent']    = hp\prefix( $field->get_arg( '_parent' ) );
							}

							// Render field.
							$output .= '<tr ' . hp\html_attributes( $attributes ) . '>';

							// Render field label.
							if ( $field->get_label() ) {
								$output .= '<th scope="row"><div><label class="hp-field__label"><span>' . esc_html( $field->get_label() ) . '</span>';

								if ( $field->get_statuses() ) {
									$output .= ' <small>(' . esc_html( implode( ', ', $field->get_statuses() ) ) . ')</small>';
								}

								$output .= '</label>' . $this->render_tooltip( $field->get_description() ) . '</div></th>';
								$output .= '<td>';
							} else {
								$output .= '<td colspan="2">';
							}

							// Render field.
							$output .= $field->render();

							$output .= '</td>';
							$output .= '</tr>';
						}
					}
				}

				$output .= '</table>';
			}
		}

		// Return output.
		if ( ! hp\get_array_value( $args, 'echo', true ) ) {
			return $output;
		}

		echo $output;
	}

	/**
	 * Adds term boxes.
	 */
	public function add_term_boxes() {

		// Get taxonomies.
		$taxonomies = [];

		foreach ( hivepress()->get_config( 'meta_boxes' ) as $name => $args ) {
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

		// Check quick edit.
		if ( isset( $_POST['_inline_edit'] ) ) {
			return;
		}

		// Get term.
		$term = get_term( $term_id );

		if ( empty( $term ) ) {
			return;
		}

		// Remove actions.
		remove_action( 'edit_' . $term->taxonomy, [ $this, 'update_term_box' ] );
		remove_action( 'create_' . $term->taxonomy, [ $this, 'update_term_box' ] );

		// Update field values.
		foreach ( $this->get_meta_boxes( $term->taxonomy ) as $meta_box ) {
			foreach ( $meta_box['fields'] as $field_name => $field_args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ $field_args ] );

				if ( $field && ! $field->is_disabled() ) {

					// Validate field.
					$field->set_value( hp\get_array_value( $_POST, hp\prefix( $field_name ) ) );

					if ( $field->validate() ) {

						// Update field value.
						if ( $field->get_arg( '_external' ) ) {
							if ( in_array( $field->get_value(), [ null, false ], true ) ) {
								delete_term_meta( $term->term_id, $field->get_arg( '_alias' ) );
							} elseif ( ! $field->get_arg( 'readonly' ) ) {
								update_term_meta( $term->term_id, $field->get_arg( '_alias' ), $field->get_value() );
							}
						} else {
							wp_update_term(
								$term->term_id,
								$term->taxonomy,
								[
									$field->get_arg( '_alias' ) => $field->get_value(),
								]
							);
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

		foreach ( $this->get_meta_boxes( $taxonomy ) as $meta_box ) {
			foreach ( hp\sort_array( $meta_box['fields'] ) as $field_name => $field_args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ array_merge( $field_args, [ 'name' => hp\prefix( $field_name ) ] ) ] );

				if ( $field ) {

					// Get field attributes.
					$attributes = [
						'class' => 'form-field hp-form--table',
					];

					if ( $field->get_arg( '_parent' ) ) {
						$attributes['data-component'] = 'field';
						$attributes['data-parent']    = hp\prefix( $field->get_arg( '_parent' ) );
					}

					if ( ! is_object( $term ) ) {
						$output .= '<div ' . hp\html_attributes( $attributes ) . '>';

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
							$output .= '<p>' . hp\sanitize_html( $field->get_description() ) . '</p>';
						}

						$output .= '</div>';
					} else {
						$output .= '<tr ' . hp\html_attributes( $attributes ) . '>';

						// Render label.
						$output .= '<th scope="row"><label class="hp-field__label"><span>' . esc_html( $field->get_label() ) . '</span>';

						if ( $field->get_statuses() ) {
							$output .= ' <small>(' . esc_html( implode( ', ', $field->get_statuses() ) ) . ')</small>';
						}

						$output .= '</label></th>';

						// Get field value.
						$value = '';

						if ( $field->get_arg( '_external' ) ) {
							$value = get_term_meta( $term->term_id, $field->get_arg( '_alias' ), true );
						} else {
							$value = get_term_field( $field->get_arg( '_alias' ), $term );
						}

						// Set field value.
						if ( '' !== $value ) {
							$field->set_value( $value );
						}

						// Render field.
						$output .= '<td>';

						$output .= $field->render();

						// Render description.
						if ( $field->get_description() ) {
							$output .= '<p class="description">' . hp\sanitize_html( $field->get_description() ) . '</p>';
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
	 * Updates user box values.
	 *
	 * @param int $user_id User ID.
	 */
	public function update_user_boxes( $user_id ) {

		// Check permissions.
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		// Remove actions.
		remove_action( 'personal_options_update', [ $this, 'update_user_boxes' ] );
		remove_action( 'edit_user_profile_update', [ $this, 'update_user_boxes' ] );

		// Update field values.
		foreach ( $this->get_meta_boxes( 'user' ) as $meta_box ) {
			foreach ( $meta_box['fields'] as $field_name => $field_args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ $field_args ] );

				if ( $field && ! $field->is_disabled() ) {

					// Validate field.
					$field->set_value( hp\get_array_value( $_POST, hp\prefix( $field_name ) ) );

					if ( $field->validate() ) {

						// Update field value.
						if ( $field->get_arg( '_external' ) ) {
							if ( in_array( $field->get_value(), [ null, false ], true ) ) {
								delete_user_meta( $user_id, $field->get_arg( '_alias' ) );
							} elseif ( ! $field->get_arg( 'readonly' ) ) {
								update_user_meta( $user_id, $field->get_arg( '_alias' ), $field->get_value() );
							}
						} else {
							wp_update_user(
								[
									'ID' => $user_id,
									$field->get_arg( '_alias' ) => $field->get_value(),
								]
							);
						}
					}
				}
			}
		}
	}

	/**
	 * Renders user box fields.
	 *
	 * @param WP_User $user User object.
	 */
	public function render_user_boxes( $user ) {
		$output = '';

		// Check permissions.
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		foreach ( $this->get_meta_boxes( 'user' ) as $meta_box ) {

			// Check fields.
			if ( ! $meta_box['fields'] ) {
				continue;
			}

			// Render title.
			$output .= '<h2>' . esc_html( $meta_box['title'] ) . '</h2>';
			$output .= '<table class="form-table hp-form" role="presentation">';

			foreach ( hp\sort_array( $meta_box['fields'] ) as $field_name => $field_args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ array_merge( $field_args, [ 'name' => hp\prefix( $field_name ) ] ) ] );

				if ( $field ) {

					// Get field attributes.
					$attributes = [
						'class' => 'form-field hp-form--table',
					];

					if ( $field->get_arg( '_parent' ) ) {
						$attributes['data-component'] = 'field';
						$attributes['data-parent']    = hp\prefix( $field->get_arg( '_parent' ) );
					}

					$output .= '<tr ' . hp\html_attributes( $attributes ) . '>';

					// Render label.
					$output .= '<th><label class="hp-field__label"><span>' . esc_html( $field->get_label() ) . '</span>';

					if ( $field->get_statuses() ) {
						$output .= ' <small>(' . esc_html( implode( ', ', $field->get_statuses() ) ) . ')</small>';
					}

					$output .= '</label></th>';

					// Get field value.
					$value = '';

					if ( $field->get_arg( '_external' ) ) {
						$value = get_user_meta( $user->ID, $field->get_arg( '_alias' ), true );
					} else {
						$value = $user->get( $field->get_arg( '_alias' ) );
					}

					// Set field value.
					if ( '' !== $value ) {
						$field->set_value( $value );
					}

					// Render field.
					$output .= '<td>';

					$output .= $field->render();

					// Render description.
					if ( $field->get_description() ) {
						$output .= '<p class="description">' . do_shortcode( wp_kses_post( $field->get_description() ) ) . '</p>';
					}

					$output .= '</td>';
					$output .= '</tr>';
				}
			}

			$output .= '</table>';
		}

		echo $output;
	}

	/**
	 * Checks user access.
	 */
	public function check_access() {
		if ( ! wp_doing_ajax() && get_option( 'hp_user_disable_backend' ) && ! current_user_can( 'publish_posts' ) ) {
			wp_safe_redirect( hivepress()->router->get_url( 'user_account_page' ) );

			exit;
		}
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
	 * Renders plugin links.
	 *
	 * @param array $links Plugin links.
	 * @return array
	 */
	public function render_links( $links ) {
		return array_merge(
			[
				'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=hp_settings' ) ) . '">' . esc_html__( 'Settings', 'hivepress' ) . '</a>',
			],
			$links
		);
	}

	/**
	 * Renders notices.
	 */
	public function render_notices() {
		global $pagenow;

		$output = '';

		// Get installation time.
		$installed_time = absint( get_option( 'hp_installed_time' ) );

		// Add default notices.
		$notices = [];

		// Check valid purchases.
		$purchases = $this->get_purchases();

		$products = array_filter(
			array_merge( $this->get_themes(), $this->get_extensions() ),
			function( $product ) use ( $purchases ) {
				return isset( $product['price'] ) && 'bundle' !== $product['slug'] && in_array( $product['status'], [ 'installed', 'latest_installed', 'activate', 'active' ] ) && ! isset( $purchases[ $product['slug'] ] );
			}
		);

		if ( $products ) {
			$notices['license_request'] = [
				'type'        => 'error',
				'dismissible' => true,
				'text'        => sprintf(
					/* translators: 1: settings URL, 2: unlicensed products. */
					hp\sanitize_html( __( 'Please <a href="%1$s">add the license keys</a> for the installed premium HivePress themes and extensions. The following products without valid licenses are going to be disabled automatically: %2$s.', 'hivepress' ) ),
					esc_url( admin_url( 'admin.php?page=hp_settings&tab=integrations' ) ),
					implode( ', ', array_column( $products, 'name' ) )
				),
			];
		}

		// Check theme support.
		if ( ! current_theme_supports( 'hivepress' ) ) {
			$notices['incompatible_theme'] = [
				'type'        => 'warning',
				'dismissible' => true,
				'text'        => sprintf(
					/* translators: %s: themes URL. */
					hp\sanitize_html( __( 'The current theme doesn\'t declare HivePress support, if you notice any styling issues please consider using one of the <a href="%s">official themes</a> instead.', 'hivepress' ) ),
					esc_url( admin_url( 'admin.php?page=hp_themes' ) )
				),
			];
		}

		// Suggest adding review.
		if ( $installed_time < time() - WEEK_IN_SECONDS * 2 ) {
			$notices['review_request'] = [
				'type'        => 'info',
				'dismissible' => true,
				'text'        => sprintf(
					/* translators: %s: link URL. */
					hp\sanitize_html( __( 'It\'s been more than 2 weeks since you installed HivePress, that\'s awesome! If you find it useful, please leave a review on <a href="%s" target="_blank">WordPress.org</a> to help us spread the word.', 'hivepress' ) ),
					'https://wordpress.org/support/plugin/hivepress/reviews/'
				),
			];
		}

		if ( $installed_time < time() - DAY_IN_SECONDS * 2 ) {

			// Suggest premium products.
			if ( get_template() === 'listinghive' && ! get_option( 'hp_hivepress_license_key' ) ) {
				$notices['upgrade_request'] = [
					'type'        => 'info',
					'dismissible' => true,
					'text'        => sprintf(
						/* translators: %s: link URL. */
						hp\sanitize_html( __( 'Great start with HivePress! Check out our <a href="%1$s">premium themes</a> and <a href="%2$s">extensions</a> for more tailored design and functionality.', 'hivepress' ) ),
						esc_url( admin_url( 'admin.php?page=hp_themes' ) ),
						esc_url( admin_url( 'admin.php?page=hp_extensions' ) )
					),
				];
			}

			// Request usage sharing.
			if ( ! get_option( 'hp_hivepress_allow_tracking' ) ) {
				$notices['usage_tracking'] = [
					'type'        => 'info',
					'option'      => 'hivepress_allow_tracking',
					'dismissible' => true,
					/* translators: %s: terms URL. */
					'text'        => sprintf( hp\sanitize_html( __( 'Help us make HivePress better by sharing <a href="%s" target="_blank">non-sensitive usage data</a> or dismiss this notice to opt out.', 'hivepress' ) ), 'https://hivepress.io/usage-tracking/' ) . '&nbsp;&nbsp;<a href="#" class="button">' . esc_html__( 'Share Usage Data', 'hivepress' ) . '</a>',
				];
			}
		}

		// Suggest website showcase.
		if ( $installed_time < time() - MONTH_IN_SECONDS * 2 ) {
			$notices['showcase_request'] = [
				'type'        => 'info',
				'dismissible' => true,
				'text'        => sprintf(
					/* translators: %s: link URL. */
					hp\sanitize_html( __( 'Have you already launched this website? Please submit it to the <a href="%s" target="_blank">HivePress Showcase</a> to inspire others.', 'hivepress' ) ),
					'https://hivepress.io/showcase/'
				),
			];
		}

		// Suggest expert program.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && $installed_time < time() - WEEK_IN_SECONDS ) {
			$notices['expert_request'] = [
				'type'        => 'info',
				'dismissible' => true,
				'text'        => sprintf(
					/* translators: %s: link URL. */
					hp\sanitize_html( __( 'Are you a developer familiar with HivePress? Join the <a href="%s" target="_blank">HivePress Experts</a> program to regularly get new clients.', 'hivepress' ) ),
					'https://hivepress.io/experts/'
				),
			];
		}

		/**
		 * Filters the WordPress admin area notices.
		 *
		 * @hook hivepress/v1/admin_notices
		 * @param {array} $notices Notice configurations.
		 * @return {array} Notice configurations.
		 */
		$notices = apply_filters( 'hivepress/v1/admin_notices', $notices );

		// Remove dismissed notices.
		$notices = array_diff_key( $notices, array_flip( array_filter( (array) get_option( 'hp_admin_dismissed_notices' ) ) ) );

		// Render notices.
		foreach ( $notices as $notice_name => $notice ) {
			$output .= $this->render_notice( array_merge( $notice, [ 'name' => $notice_name ] ) );
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
	 * Renders notice.
	 *
	 * @param array $args Notice arguments.
	 * @return string
	 */
	public function render_notice( $args ) {
		$output = '';

		// Set defaults.
		$args = array_merge(
			[
				'type'        => 'info',
				'name'        => '',
				'text'        => '',
				'option'      => null,
				'dismissible' => false,
				'inline'      => false,
			],
			$args
		);

		// Set attributes.
		$attributes = [
			'class' => [ 'notice-' . $args['type'] ],
		];

		if ( $args['inline'] ) {
			$attributes = hp\merge_arrays(
				$attributes,
				[
					'class' => [ 'hp-notice' ],
				]
			);
		} else {
			$attributes = hp\merge_arrays(
				$attributes,
				[
					'class' => [ 'notice' ],
				]
			);
		}

		if ( $args['option'] ) {
			$attributes['data-option'] = $args['option'];
		}

		if ( $args['dismissible'] ) {
			$attributes = hp\merge_arrays(
				$attributes,
				[
					'class'          => [ 'is-dismissible' ],
					'data-component' => 'notice',
					'data-name'      => $args['name'],
					'data-url'       => esc_url( hivepress()->router->get_url( 'admin_notice_update_action', [ 'notice_name' => $args['name'] ] ) ),
				]
			);
		}

		// Render notice.
		$output .= '<div ' . hp\html_attributes( $attributes ) . '><p>' . hp\sanitize_html( $args['text'] ) . '</p></div>';

		return $output;
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
			$output .= '<div class="hp-tooltip">';
			$output .= '<span class="hp-tooltip__icon dashicons dashicons-editor-help"></span>';
			$output .= '<div class="hp-tooltip__text">' . do_shortcode( wp_kses_post( $text ) ) . '</div>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Renders footer.
	 */
	public function render_footer() {
		global $pagenow;

		$output = '';

		if ( 'plugins.php' === $pagenow ) {
			$output .= ( new Blocks\Modal(
				[
					/* translators: %s: plugin name. */
					'title'  => sprintf( esc_html__( 'Deactivate %s', 'hivepress' ), hivepress()->get_name() ),
					'name'   => 'hivepress_deactivate_modal',

					'blocks' => [
						'plugin_deactivate_form' => [
							'type'   => 'form',
							'form'   => 'plugin_deactivate',
							'_order' => 10,
						],
					],
				]
			) )->render();
		}

		$output .= hivepress()->request->get_context( 'admin_footer' );

		echo $output;
	}

	/**
	 * Shares usage data.
	 */
	public function share_usage_data() {

		// Check settings.
		if ( ! get_option( 'hp_hivepress_allow_tracking' ) ) {
			return;
		}

		// Set defaults.
		$data = [
			'url'       => home_url(),
			'date'      => gmdate( 'Y-m-d' ),
			'email'     => get_bloginfo( 'admin_email' ),
			'language'  => get_locale(),
			'timezone'  => wp_timezone_string(),
			'version'   => get_bloginfo( 'version' ),
			'multisite' => is_multisite(),
		];

		if ( get_option( 'hp_installed_time' ) ) {
			$data['lifetime'] = ceil( ( time() - absint( get_option( 'hp_installed_time' ) ) ) / DAY_IN_SECONDS );
		}

		// Get stats.
		$data['stats'] = [
			'listings' => wp_count_posts( 'hp_listing' )->publish,
			'vendors'  => wp_count_posts( 'hp_vendor' )->publish,
			'users'    => get_user_count(),
		];

		// Get theme.
		$theme = wp_get_theme( get_template() );

		$data['theme'] = [
			'slug' => get_template(),
		];

		if ( $theme->exists() ) {
			$data['theme'] = array_merge(
				$data['theme'],
				array_filter(
					array_combine(
						[
							'name',
							'version',
							'url',
							'author',
							'author_url',
						],
						array_map(
							function( $header ) use ( $theme ) {
								return $theme->get( $header );
							},
							[
								'Name',
								'Version',
								'ThemeURI',
								'Author',
								'AuthorURI',
							]
						)
					)
				)
			);
		}

		// Get plugins.
		$data['plugins'] = array_filter(
			array_map(
				function ( $plugin ) {
					$file = WP_PLUGIN_DIR . '/' . $plugin;

					if ( file_exists( $file ) ) {
						$headers = array_filter(
							get_file_data(
								$file,
								[
									'name'       => 'Plugin Name',
									'version'    => 'Version',
									'url'        => 'Plugin URI',
									'author'     => 'Author',
									'author_url' => 'Author URI',
								]
							)
						);

						$headers['slug'] = basename( $plugin, '.php' );

						return $headers;
					}
				},
				(array) get_option( 'active_plugins' )
			)
		);

		// Get settings.
		$settings = hivepress()->get_config( 'settings' );

		unset( $settings['integrations'] );

		// Get options.
		$options = [
			'users_can_register',
			'default_role',
			'date_format',
			'time_format',
			'start_of_week',
			'show_on_front',
			'blog_public',
			'permalink_structure',
		];

		foreach ( $settings as $tab ) {
			foreach ( hp\get_array_value( $tab, 'sections', [] ) as $section ) {
				foreach ( array_keys( hp\get_array_value( $section, 'fields', [] ) ) as $field ) {
					$options[] = hp\prefix( $field );
				}
			}
		}

		$data['options'] = array_filter(
			wp_load_alloptions(),
			function( $value, $option ) use ( $options ) {
				return in_array( $option, $options ) && strlen( $value );
			},
			ARRAY_FILTER_USE_BOTH
		);

		// Get PHP info.
		$configs = [
			'max_execution_time',
			'max_input_time',
			'memory_limit',
			'upload_max_filesize',
		];

		$data['php'] = array_combine(
			$configs,
			array_map(
				function( $config ) {
					return ini_get( $config );
				},
				$configs
			)
		);

		$data['php']['version'] = phpversion();

		// Send usage data.
		wp_remote_post(
			'https://hivepress.io/api/v1/feedback',
			[
				'body' => [
					'action' => 'share_usage_data',
					'data'   => wp_json_encode( $data ),
				],
			]
		);
	}
}
