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

		foreach ( hivepress()->get_classes( 'blocks' ) as $block_type => $block_class ) {
			if ( $block_class::get_meta( 'label' ) ) {
				$block_slug = hp\sanitize_slug( $block_type );

				// Add block.
				$blocks[ $block_type ] = [
					'title'      => HP_CORE_NAME . ' ' . $block_class::get_meta( 'label' ),
					'type'       => 'hivepress/' . $block_slug,
					'script'     => 'hp-block-' . $block_slug,
					'attributes' => [],
					'settings'   => [],
				];

				foreach ( $block_class::get_meta( 'settings' ) as $field_name => $field ) {

					// Add attribute.
					$blocks[ $block_type ]['attributes'][ $field_name ] = [
						'type'    => 'string',
						'default' => $field->get_value(),
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
				wp_register_script( $block['script'], HP_CORE_URL . '/assets/js/block.min.js', [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ], HP_CORE_VERSION, true );
				wp_localize_script( $block['script'], 'hpBlock', $block );

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
				wp_localize_script( reset( $blocks )['script'], 'hpBlocks', $blocks );
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
