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
 * Editor component class.
 *
 * @class Editor
 */
final class Editor extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Register blocks.
		add_action( 'init', [ $this, 'register_blocks' ] );

		if ( is_admin() ) {

			// Enqueue styles.
			add_action( 'admin_init', [ $this, 'enqueue_styles' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Registers blocks.
	 */
	public function register_blocks() {

		// Get blocks.
		$blocks = [];

		foreach ( hivepress()->get_classes( 'blocks' ) as $block_type => $block ) {
			if ( $block::get_meta( 'label' ) ) {

				// Get slug.
				$block_slug = hp\sanitize_slug( $block_type );

				// Add block.
				$blocks[ $block_type ] = [
					'title'      => hivepress()->get_name() . ' ' . $block::get_meta( 'label' ),
					'type'       => 'hivepress/' . $block_slug,
					'script'     => 'hivepress-block-' . $block_slug,
					'attributes' => [],
					'settings'   => [],
				];

				foreach ( $block::get_meta( 'settings' ) as $field_name => $field ) {

					// Add attribute.
					$blocks[ $block_type ]['attributes'][ $field_name ] = [
						'type'    => 'string',
						'default' => hp\get_array_value( $field->get_args(), 'default', '' ),
					];

					// Add setting.
					$blocks[ $block_type ]['settings'][ $field_name ] = $field->get_args();
				}
			}
		}

		// Register blocks.
		if ( function_exists( 'register_block_type' ) ) {
			foreach ( $blocks as $block_type => $block ) {

				// Register block script.
				wp_register_script( $block['script'], hivepress()->get_url() . '/assets/js/block.min.js', [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ], hivepress()->get_version(), true );
				wp_localize_script( $block['script'], 'hivepressBlock', $block );

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

			if ( $blocks ) {
				wp_localize_script( reset( $blocks )['script'], 'hivepressBlocks', $blocks );
			}
		}

		// Add shortcodes.
		foreach ( array_keys( $blocks ) as $block_type ) {
			add_shortcode( 'hivepress_' . $block_type, [ $this, 'render_' . $block_type ] );
		}
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return string
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'render_' ) === 0 ) {
			$output = ' ';

			// Create block.
			$block = hp\create_class_instance( '\HivePress\Blocks\\' . substr( $name, strlen( 'render_' ) ), [ (array) reset( $args ) ] );

			if ( $block ) {

				// Render block.
				$output .= $block->render();
			}

			return $output;
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Enqueues styles.
	 */
	public function enqueue_styles() {
		foreach ( hivepress()->get_config( 'styles' ) as $style ) {
			if ( in_array( 'editor', (array) hp\get_array_value( $style, 'scope' ), true ) ) {
				add_editor_style( $style['src'] );
			}
		}
	}
}
