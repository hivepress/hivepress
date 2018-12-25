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

		// Initialize blocks.
		add_action( 'hivepress/component/init_blocks', [ $this, 'init_blocks' ] );

		// Register blocks.
		add_action( 'init', [ $this, 'register_blocks' ] );

		// Add shortcodes.
		add_action( 'init', [ $this, 'add_shortcodes' ] );

		// Filter shortcodes.
		add_filter( 'the_content', [ $this, 'filter_shortcodes' ] );
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
		wp_register_script( 'hp-editor', HP_CORE_URL . '/assets/js/editor.js', [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ], HP_CORE_VERSION, true );
		wp_localize_script( 'hp-editor', 'blocks', $this->blocks );

		wp_register_style( 'hp-editor', HP_CORE_URL . '/assets/css/frontend.min.css', [ 'wp-edit-blocks' ] );

		foreach ( $this->blocks as $block_id => $block ) {
			$attributes = [];

			foreach ( hp_get_array_value( $block, 'fields', [] ) as $field_id => $field ) {
				$attributes[ $field_id ] = [
					'type'    => 'string',
					'default' => hp_get_array_value( $field, 'default' ),
				];
			}
			register_block_type(
				'hivepress/' . str_replace( '_', '-', $block_id ),
				[
					'editor_script'   => 'hp-editor',
					'editor_style'    => 'hp-editor',
					'render_callback' => [ $this, 'render_' . $block_id ],
					'attributes'      => $attributes,
				]
			);
		}

		//
		// public function gutenberg_boilerplate_block() {
		// wp_register_script(
		// 'gutenberg-boilerplate-es5-step01',
		// HP_CORE_URL.'/assets/js/blocks/block.js',
		// array( 'wp-blocks', 'wp-element', 'wp-components' )
		// );
		//
		// wp_register_style(
		// 'gutenberg-boilerplate-es5-step01-editor',
		// HP_CORE_URL.'/assets/js/blocks/editor.css',
		// array( 'wp-edit-blocks' ),
		// filemtime( HP_CORE_PATH.'/assets/js/blocks/editor.css' )
		// );
		//
		// register_block_type( 'gutenberg-boilerplate-es5/hello-world-step-01', array(
		// 'attributes'      => array(
		// 'foo' => array(
		// 'type' => 'string',
		// ),
		// ),
		// 'editor_script' => 'gutenberg-boilerplate-es5-step01',
		// 'editor_style' => 'gutenberg-boilerplate-es5-step01-editor',
		// 'render_callback' => [$this, 'my_plugin_render_block_latest_post'],
		// ) );
		// }
		//
		// public function my_plugin_render_block_latest_post( $attributes, $content ) {
		// $recent_posts = wp_get_recent_posts( array(
		// 'numberposts' => 1,
		// 'post_status' => 'publish',
		// ) );
		// if ( count( $recent_posts ) === 0 ) {
		// return 'No posts';
		// }
		// $post = $recent_posts[ 0 ];
		// $post_id = $post['ID'];
		// return sprintf(
		// '<a class="wp-block-my-plugin-latest-post" href="%1$s">%2$s</a>',
		// esc_url( get_permalink( $post_id ) ),
		// esc_html( get_the_title( $post_id ) )
		// );
		// }
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
