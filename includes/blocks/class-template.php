<?php
/**
 * Template block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template block class.
 *
 * @class Template
 */
class Template extends Block {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {

		// Get template path.
		$filepath = locate_template( 'hivepress/' . $this->get_attribute( 'path' ) . '.php' );

		if ( '' === $filepath ) {
			foreach ( array_reverse( hivepress()->get_dirs() ) as $dir ) {
				if ( file_exists( $dir . '/templates/' . $this->get_attribute( 'path' ) . '.php' ) ) {
					$filepath = $dir . '/templates/' . $this->get_attribute( 'path' ) . '.php';

					break;
				}
			}
		}

		// Render template.
		ob_start();

		include $filepath;
		$output = ob_get_contents();

		ob_end_clean();

		return $output;
	}
}
