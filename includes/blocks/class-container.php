<?php
/**
 * Container block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Container block class.
 *
 * @class Container
 */
class Container extends Block {

	/**
	 * Inner blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * Class constructor.
	 */
	public function __construct( $args ) {
		if ( isset( $args['blocks'] ) ) {
			foreach ( $args['blocks'] as $block_args ) {
				$block_class    = '\HivePress\Blocks\\' . $block_args['type'];
				$this->blocks[] = new $block_class( $block_args );
			}
		}

		if ( isset( $args['attributes'] ) ) {
			$this->attributes = $args['attributes'];
		}

		if(!isset($this->attributes['tag'])) {
			$this->attributes['tag']='div';
		}
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<' . esc_attr( $this->attributes['tag'] ) . '>';

		foreach ( $this->blocks as $block ) {
			$output .= $block->render();
		}

		$output .= '</' . esc_attr( $this->attributes['tag'] ) . '>';

		return $output;
	}
}
