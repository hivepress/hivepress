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
final class Editor {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Register blocks.
		add_action( 'init', [ $this, 'register_blocks' ] );

		if ( is_admin() ) {

			// Enqueue styles.
			add_action( 'admin_init', [ $this, 'enqueue_styles' ] );
		}
	}

	/**
	 * Registers blocks.
	 */
	public function register_blocks() {

		// Get blocks.
		$blocks = [];

		foreach ( hivepress()->get_blocks() as $block_type => $block ) {
			if ( $block::get_title() ) {
				$block_slug = hp\sanitize_slug( $block_type );

				$blocks[ $block_type ] = [
					'title'      => HP_CORE_NAME . ' ' . $block::get_title(),
					'type'       => 'hivepress/' . $block_slug,
					'script'     => 'hp-block-' . $block_slug,
					'attributes' => [],
					'settings'   => [],
				];

				foreach ( $block::get_settings() as $field_name => $field ) {
					$field_args = $field->get_args();

					if ( isset( $field_args['options'] ) && ! isset( $field_args['options'][''] ) ) {
						$field_args['options'] = [ '' => '&mdash;' ] + $field_args['options'];
					}

					// Add attribute.
					$blocks[ $block_type ]['attributes'][ $field_name ] = [
						'type'    => 'string',
						'default' => hp\get_array_value( $field_args, 'default' ),
					];

					// Add setting.
					$blocks[ $block_type ]['settings'][ $field_name ] = $field_args;
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

			if ( ! empty( $blocks ) ) {
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
	 * @return mixed
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'render_' ) === 0 ) {

			// Render block HTML.
			$output = '';

			$block_type  = substr( $name, strlen( 'render' ) + 1 );
			$block_class = '\HivePress\Blocks\\' . $block_type;
			$block_args  = reset( $args );

			if ( class_exists( $block_class ) ) {
				$output .= ( new $block_class( (array) $block_args ) )->render();
			}

			return $output;
		}
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
