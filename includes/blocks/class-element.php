<?php
/**
 * Element block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Element block class.
 *
 * @class Element
 */
class Element extends Block {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {

		// Get file path.
		$filepath = locate_template( 'hivepress/' . $this->get_attribute( 'file_path' ) . '.php' );

		if ( '' === $filepath ) {
			foreach ( hivepress()->get_dirs() as $dir ) {
				if ( file_exists( $dir . '/templates/' . $this->get_attribute( 'file_path' ) . '.php' ) ) {
					$filepath = $dir . '/templates/' . $this->get_attribute( 'file_path' ) . '.php';

					break;
				}
			}
		}

		// Render element.
		ob_start();

		include $filepath;
		$output = ob_get_contents();

		ob_end_clean();

		return $output;
	}
}
