<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages editor.
 *
 * @class Editor
 */
class Editor extends Component {

	/**
	 * Array of blocks.
	 *
	 * @var array
	 */
	private $blocks = [];

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		// Add styles.
		add_action( 'hivepress/component/init_styles', [ $this, 'add_styles' ] );

		// Initialize blocks.
		add_action( 'hivepress/component/init_editor_blocks', [ $this, 'init_blocks' ] );

		// Register blocks.
		add_action( 'init', [ $this, 'register_blocks' ] );

		// Add shortcodes.
		add_action( 'init', [ $this, 'add_shortcodes' ] );

		// Filter shortcodes.
		add_filter( 'the_content', [ $this, 'filter_shortcodes' ] );
	}

	/**
	 * Adds styles.
	 *
	 * @param array $styles
	 */
	public function add_styles( $styles ) {
		foreach ( $styles as $style ) {
			if ( isset( $style['src'] ) && hp_get_array_value( $style, 'editor', false ) ) {
				add_editor_style( $style['src'] );
			}
		}
	}

	/**
	 * Initializes blocks.
	 *
	 * @param array $blocks
	 */
	public function init_blocks( $blocks ) {
		$this->blocks = array_merge( $this->blocks, $blocks );
	}

	/**
	 * Registers blocks.
	 */
	public function register_blocks() {
		foreach ( $this->blocks as $block_id => $block ) {

			// Get block slug and type.
			$block_slug  = str_replace( '_', '-', $block_id );
			$block_type  = 'hivepress/' . str_replace( '_', '-', $block_id );
			$block_title = HP_CORE_NAME . ' ' . $block['title'];

			// Register block script.
			$script_handle = 'hp-block-' . $block_slug;

			wp_register_script( $script_handle, HP_CORE_URL . '/assets/js/editor-block.js', [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ], HP_CORE_VERSION, true );

			wp_localize_script(
				$script_handle,
				'block',
				array_merge(
					$block,
					[
						'type'  => $block_type,
						'title' => $block_title,
					]
				)
			);

			// Get block attributes.
			$attributes = [];

			foreach ( hp_get_array_value( $block, 'fields', [] ) as $field_id => $field ) {
				$attributes[ $field_id ] = [
					'type'    => 'string',
					'default' => hp_get_array_value( $field, 'default' ),
				];
			}

			// Register block type.
			register_block_type(
				$block_type,
				[
					'editor_script'   => $script_handle,
					'render_callback' => [ $this, 'render_' . $block_id ],
					'attributes'      => $attributes,
				]
			);
		}
	}

	/**
	 * Adds shortcodes.
	 */
	public function add_shortcodes() {
		foreach ( $this->blocks as $shortcode_id => $shortcode ) {
			add_shortcode( 'hivepress_' . $shortcode_id, [ $this, 'render_' . $shortcode_id ] );
		}
	}

	/**
	 * Filters shortcodes.
	 *
	 * @param string $content
	 * @return string
	 */
	public function filter_shortcodes( $content ) {
		$shortcodes = implode(
			'|',
			array_map(
				function( $shortcode_id ) {
					return 'hivepress_' . $shortcode_id;
				},
				array_keys( $this->blocks )
			)
		);

		$content = preg_replace( '/(<p>)?\[(' . $shortcodes . ')(.*?)?\](<\/p>)?/', '[$2$3]', $content );
		$content = preg_replace( '/(<p>)?\[\/(' . $shortcodes . ')\](<\/p>)?/', '[/$2]', $content );

		return $content;
	}

	/**
	 * Routes component functions.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __call( $name, $args ) {
		parent::__call( $name, $args );

		// Render block.
		if ( strpos( $name, 'render_' ) === 0 ) {

			// Get block ID.
			$block_id = str_replace( 'render_', '', $name );

			// Render block HTML.
			return apply_filters( "hivepress/editor/block_html/{$block_id}", $args[1], $args[0] );
		}
	}
}
