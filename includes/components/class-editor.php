<?php
/**
 * Editor component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Blocks;
use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Implements integration with Gutenberg.
 */
final class Editor extends Component {

	/**
	 * Registered blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected $template;

	/**
	 * Template context.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Initialize template editor.
		add_action( 'rest_api_init', [ $this, 'init_template_editor' ] );
		add_action( 'admin_init', [ $this, 'init_template_editor' ] );

		// Register block categories.
		add_filter( 'block_categories_all', [ $this, 'register_block_categories' ] );

		// Register default blocks.
		add_action( 'init', [ $this, 'register_default_blocks' ], 200 );

		if ( is_admin() ) {

			// Enqueue editor styles.
			add_action( 'admin_init', [ $this, 'enqueue_editor_styles' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Checks if editor preview mode is enabled.
	 *
	 * @return bool
	 */
	public function is_preview() {

		/**
		 * Filters the editor preview mode status.
		 *
		 * @hook hivepress/v1/components/editor/preview
		 * @param {bool} $enabled Preview status.
		 * @return {bool} Preview status.
		 */
		return apply_filters( 'hivepress/v1/components/editor/preview', hp\is_rest() );
	}

	/**
	 * Checks if block preview mode is enabled.
	 *
	 * @return bool
	 */
	protected function is_block_preview() {
		global $wp;

		return hp\is_rest() && strpos( $wp->request, '/wp/v2/block-renderer/hivepress/' );
	}

	/**
	 * Gets block arguments.
	 *
	 * @param string $type Block type.
	 * @param string $name Block name.
	 * @param array  $args Template arguments.
	 * @return array
	 */
	protected function get_block( $type, $name = null, $args = [] ) {
		$block = null;

		// Get class.
		$class = '\HivePress\Blocks\\' . $type;

		if ( class_exists( $class ) ) {

			// Get slug.
			$slug = hp\sanitize_slug( $name ? $name : $type );

			// Set block.
			$block = [
				'title'      => hp\get_array_value( $args, '_label', $class::get_meta( 'label' ) ),
				'type'       => 'hivepress/' . $slug,
				'script'     => 'hivepress-block-' . $slug,
				'category'   => isset( $args['_label'] ) ? 'hivepress-template' : 'hivepress',
				'attributes' => [],
				'settings'   => [],
			];

			// Get field names.
			$field_names = hp\get_array_value( $args, '_settings' );

			foreach ( $class::get_meta( 'settings' ) as $field_name => $field ) {
				if ( ! is_array( $field_names ) || in_array( $field_name, $field_names, true ) ) {

					// Get field arguments.
					$field_args = $field->get_args();

					if ( isset( $field_args['options'] ) ) {
						if ( is_array( hp\get_first_array_value( $field_args['options'] ) ) ) {
							$field_args['options'] = wp_list_pluck( $field_args['options'], 'label' );
						}

						if ( ! hp\get_array_value( $field_args, 'required', false ) && ! isset( $field_args['options'][''] ) ) {
							$field_args['options'] = [ '' => '&mdash;' ] + $field_args['options'];
						}
					}

					// Add attribute.
					$block['attributes'][ $field_name ] = [
						'type'    => 'string',
						'default' => hp\get_array_value( $field_args, 'default', '' ),
					];

					// Add setting.
					$block['settings'][ $field_name ] = $field_args;
				}
			}
		}

		return $block;
	}

	/**
	 * Registers block type.
	 *
	 * @param string $type Block type.
	 * @param array  $args Block arguments.
	 */
	protected function register_block( $type, $args ) {

		// Check compatibility.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register block script.
		wp_register_script( $args['script'], hivepress()->get_url() . '/assets/js/block.min.js', [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ], hivepress()->get_version(), true );
		wp_add_inline_script( $args['script'], 'var hivepressBlock = ' . wp_json_encode( $args ) . ';', 'before' );

		// Register block type.
		register_block_type(
			$args['type'],
			[
				'editor_script'   => $args['script'],
				'render_callback' => [ $this, 'render_' . $type ],
				'attributes'      => $args['attributes'],
			]
		);

		// Add block.
		$this->blocks[ $type ] = $args;
	}

	/**
	 * Initializes template editor.
	 */
	public function init_template_editor() {
		global $pagenow;

		// Check template.
		if ( is_array( $this->template ) ) {
			return;
		}

		// Get template ID.
		$template_id = null;

		if ( is_admin() && 'post.php' === $pagenow && isset( $_GET['post'] ) ) {
			$template_id = absint( $_GET['post'] );
		} elseif ( $this->is_block_preview() && isset( $_GET['post_id'] ) ) {
			$template_id = absint( $_GET['post_id'] );
		}

		// Register template blocks.
		if ( $template_id && get_post_type( $template_id ) === 'hp_template' ) {
			$this->register_template_blocks( get_post_field( 'post_name', $template_id ) );
		}
	}

	/**
	 * Registers block categories.
	 *
	 * @param array $categories Block categories.
	 * @return array
	 */
	public function register_block_categories( $categories ) {
		return array_merge(
			$categories,
			[
				[
					'title' => hivepress()->get_name(),
					'slug'  => 'hivepress',
				],

				[
					'title' => esc_html__( 'Template', 'hivepress' ),
					'slug'  => 'hivepress-template',
				],
			]
		);
	}

	/**
	 * Gets template blocks.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	protected function get_template_blocks( $template ) {
		$blocks = [];

		foreach ( $template['blocks'] as $name => $block ) {
			if ( isset( $block['_label'] ) || isset( $block['_parent'] ) ) {
				$blocks[ $name ] = $block;
			}

			if ( isset( $block['blocks'] ) ) {
				$blocks = array_merge( $blocks, $this->get_template_blocks( $block ) );
			}
		}

		return $blocks;
	}

	/**
	 * Registers template blocks.
	 *
	 * @param string $name Template name.
	 * @param array  $args Template arguments.
	 * @return object
	 */
	public function register_template_blocks( $name, $args = [] ) {

		// Create template.
		$template = hp\create_class_instance( '\HivePress\Templates\\' . $name, [ $args ] );

		if ( ! $template ) {
			return;
		}

		// Get template blocks.
		$this->template = $this->get_template_blocks( [ 'blocks' => $template->get_blocks() ] );

		foreach ( $this->template as $block_name => $block_args ) {

			// Move block.
			if ( isset( $block_args['_parent'] ) ) {
				if ( isset( $this->template[ $block_args['_parent'] ] ) ) {
					$this->template[ $block_args['_parent'] ]['_blocks'][ $block_name ] = $block_args;
				}

				unset( $this->template[ $block_name ] );

				continue;
			}

			if ( ! isset( $this->blocks[ $block_name ] ) ) {

				// Get block.
				$block = $this->get_block( hp\get_array_value( $block_args, 'type' ), $block_name, $block_args );

				if ( $block ) {

					// Register block.
					$this->register_block( $block_name, $block );
				}
			} else {

				// Filter settings.
				$block_settings = hp\get_array_value( $block_args, '_settings' );

				if ( is_array( $block_settings ) ) {
					$block_settings = array_flip( $block_settings );

					$this->blocks[ $block_name ]['attributes'] = array_intersect_key( $this->blocks[ $block_name ]['attributes'], $block_settings );
					$this->blocks[ $block_name ]['settings']   = array_intersect_key( $this->blocks[ $block_name ]['settings'], $block_settings );
				}
			}
		}

		if ( $this->blocks ) {
			wp_localize_script( hp\get_array_value( hp\get_first_array_value( $this->blocks ), 'script' ), 'hivepressBlocks', $this->blocks );
		}

		// Set template context.
		$this->context = $template->get_context();

		return $template;
	}

	/**
	 * Registers default blocks.
	 */
	public function register_default_blocks() {
		global $pagenow;

		// Check request.
		if ( isset( $_GET['doing_wp_cron'] ) || ( is_admin() && ! in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) ) ) {
			return;
		}

		foreach ( hivepress()->get_classes( 'blocks' ) as $block_type => $block_class ) {
			if ( $block_class::get_meta( 'label' ) ) {

				// Get block.
				$block = $this->get_block( $block_type );

				if ( $block ) {

					// Register block.
					$this->register_block( $block_type, $block );

					// Add shortcode.
					if ( function_exists( 'add_shortcode' ) ) {
						add_shortcode( 'hivepress_' . $block_type, [ $this, 'render_' . $block_type ] );
					}
				}
			}
		}

		if ( $this->blocks ) {
			wp_localize_script( hp\get_array_value( hp\get_first_array_value( $this->blocks ), 'script' ), 'hivepressBlocks', $this->blocks );
		}
	}

	/**
	 * Catches calls to undefined methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return string
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'render_' ) === 0 ) {
			$output = ' ';

			// Get block arguments.
			$block_type = substr( $name, strlen( 'render_' ) );
			$block_args = (array) hp\get_first_array_value( $args );

			if ( isset( $this->blocks[ $block_type ] ) ) {
				if ( is_array( $this->template ) ) {

					// Render placeholder.
					if ( is_admin() || $this->is_block_preview() ) {
						return '<div class="hp-block__placeholder"><span>' . esc_html( $this->blocks[ $block_type ]['title'] ) . '</span></div>';
					}

					if ( isset( $this->template[ $block_type ] ) ) {

						// Filter block settings.
						if ( isset( $this->template[ $block_type ]['_settings'] ) ) {
							$block_args = array_intersect_key( $block_args, array_flip( $this->template[ $block_type ]['_settings'] ) );
						}

						// Set block arguments.
						$block_args = array_merge( $this->template[ $block_type ], $block_args, [ 'name' => $block_type ] );
						$block_type = hp\get_array_value( $this->template[ $block_type ], 'type' );
					}
				}

				// Set block context.
				if ( $this->context ) {
					if ( isset( $block_args['context'] ) ) {
						$block_args['context'] = array_merge( $this->context, $block_args['context'] );
					} else {
						$block_args = array_merge( [ 'context' => $this->context ], $block_args );
					}
				}

				// Create block.
				$block = hp\create_class_instance( '\HivePress\Blocks\\' . $block_type, [ $block_args ] );

				if ( $block ) {

					// Render block.
					$output .= $block->render();

					if ( isset( $block_args['_blocks'] ) ) {
						$output .= ( new Blocks\Container(
							[
								'context' => hp\get_array_value( $block_args, 'context', [] ),
								'tag'     => false,
								'blocks'  => $block_args['_blocks'],
							]
						) )->render();
					}
				}
			}

			return $output;
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Enqueues editor styles.
	 */
	public function enqueue_editor_styles() {

		// @todo remove when fixed in the theme framework.
		if ( ! defined( 'REQUESTS_SILENCE_PSR0_DEPRECATIONS' ) ) {
			define( 'REQUESTS_SILENCE_PSR0_DEPRECATIONS', true );
		}

		foreach ( hivepress()->get_config( 'styles' ) as $style ) {
			if ( in_array( 'editor', (array) hp\get_array_value( $style, 'scope' ), true ) ) {
				add_editor_style( $style['src'] );
			}
		}
	}
}
