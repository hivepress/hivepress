<?php
/**
 * Editor component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Implements integration with Gutenberg.
 */
final class Editor extends Component {

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

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

		// Register block categories.
		add_filter( 'block_categories_all', [ $this, 'register_block_categories' ] );

		// Register default blocks.
		add_action( 'init', [ $this, 'register_default_blocks' ] );

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
	 * Initializes template editor.
	 */
	public function init_template_editor() {
		global $pagenow;

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
					'title' => hivepress()->get_name() . ' (' . esc_html__( 'Template', 'hivepress' ) . ')',
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
			if ( is_array( $block ) ) {
				if ( isset( $block['_label'] ) ) {
					$blocks[ $name ] = $block;
				}

				if ( isset( $block['blocks'] ) ) {
					$blocks = array_merge( $blocks, $this->get_template_blocks( $block ) );
				}
			}
		}

		return $blocks;
	}

	/**
	 * Registers template blocks.
	 *
	 * @param string $name Template name.
	 * @param array  $args Template arguments.
	 */
	public function register_template_blocks( $name, $args = [] ) {

		// Create template.
		$template = hp\create_class_instance( '\HivePress\Templates\\' . $name, [ $args ] );

		if ( ! $template ) {
			return;
		}

		// Get blocks.
		$blocks = [];

		$this->blocks = $this->get_template_blocks( [ 'blocks' => $template->get_blocks() ] );

		foreach ( $this->blocks as $block_type => $block ) {

			// Get slug.
			$block_slug = hp\sanitize_slug( $block_type );

			// Add block.
			$blocks[ $block_type ] = [
				'title'      => $block['_label'],
				'type'       => 'hivepress/' . $block_slug,
				'script'     => 'hivepress-block-' . $block_slug,
				'category'   => 'hivepress-template',
				'attributes' => [],
				'settings'   => [],
			];
		}

		// Register blocks.
		$this->register_blocks( $blocks );

		// Set context.
		$this->context = $template->get_context();
	}

	/**
	 * Registers default blocks.
	 */
	public function register_default_blocks() {

		// Get blocks.
		$blocks = [];

		foreach ( hivepress()->get_classes( 'blocks' ) as $block_type => $block ) {
			if ( $block::get_meta( 'label' ) ) {

				// Get slug.
				$block_slug = hp\sanitize_slug( $block_type );

				// Add block.
				$blocks[ $block_type ] = [
					'title'      => $block::get_meta( 'label' ),
					'type'       => 'hivepress/' . $block_slug,
					'script'     => 'hivepress-block-' . $block_slug,
					'category'   => 'hivepress',
					'attributes' => [],
					'settings'   => [],
				];

				foreach ( $block::get_meta( 'settings' ) as $field_name => $field ) {

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
					$blocks[ $block_type ]['attributes'][ $field_name ] = [
						'type'    => 'string',
						'default' => hp\get_array_value( $field_args, 'default', '' ),
					];

					// Add setting.
					$blocks[ $block_type ]['settings'][ $field_name ] = $field_args;
				}
			}
		}

		// Register blocks.
		if ( $this->register_blocks( $blocks ) ) {
			wp_localize_script( hp\get_array_value( hp\get_first_array_value( $blocks ), 'script' ), 'hivepressBlocks', $blocks );
		}

		// Add shortcodes.
		if ( function_exists( 'add_shortcode' ) ) {
			foreach ( array_keys( $blocks ) as $block_type ) {
				add_shortcode( 'hivepress_' . $block_type, [ $this, 'render_' . $block_type ] );
			}
		}
	}

	/**
	 * Registers blocks.
	 *
	 * @param array $blocks Block arguments.
	 * @return bool
	 */
	protected function register_blocks( $blocks ) {

		// Check compatibility.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		foreach ( $blocks as $block_type => $block ) {

			// Register block script.
			wp_register_script( $block['script'], hivepress()->get_url() . '/assets/js/block.min.js', [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ], hivepress()->get_version(), true );
			wp_add_inline_script( $block['script'], 'var hivepressBlock = ' . wp_json_encode( $block ) . ';', 'before' );

			// Register block type.
			register_block_type(
				$block['type'],
				[
					'editor_script'   => $block['script'],
					'render_callback' => [ $this, 'render_' . $block_type ],
					'attributes'      => $block['attributes'],
				]
			);
		}

		return ! empty( $blocks );
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
				$block_args = array_merge( $this->blocks[ $block_type ], $block_args );
				$block_type = hp\get_array_value( $this->blocks[ $block_type ], 'type' );

				if ( is_admin() || $this->is_block_preview() ) {

					// Render placeholder.
					return '<div class="hp-block__placeholder"><span>' . esc_html( $block_args['_label'] ) . '</span></div>';
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
			}

			return $output;
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Enqueues editor styles.
	 */
	public function enqueue_editor_styles() {
		foreach ( hivepress()->get_config( 'styles' ) as $style ) {
			if ( in_array( 'editor', (array) hp\get_array_value( $style, 'scope' ), true ) ) {
				add_editor_style( $style['src'] );
			}
		}
	}
}
