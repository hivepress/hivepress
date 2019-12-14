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
	 * File path.
	 *
	 * @var string
	 */
	protected $filepath;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get file path.
		$filepath = locate_template( 'hivepress/' . $this->filepath . '.php' );

		if ( empty( $filepath ) ) {
			foreach ( hivepress()->get_dirs() as $dir ) {
				if ( file_exists( $dir . '/templates/' . $this->filepath . '.php' ) ) {
					$filepath = $dir . '/templates/' . $this->filepath . '.php';

					break;
				}
			}
		}

		if ( ! empty( $filepath ) ) {

			// Extract context.
			unset( $this->context['filepath'] );
			unset( $this->context['output'] );

			extract( $this->context );

			// Render element.
			ob_start();

			include $filepath;
			$output .= ob_get_contents();

			ob_end_clean();
		}

		return $output;
	}
}
