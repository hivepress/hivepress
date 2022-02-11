<?php
/**
 * Part block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders a template part.
 */
class Part extends Block {

	/**
	 * File path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get file path.
		$filepath = locate_template( 'hivepress/' . $this->path . '.php' );

		if ( empty( $filepath ) ) {
			foreach ( hivepress()->get_paths() as $dir ) {
				$dirpath = $dir . '/templates/' . $this->path . '.php';

				if ( file_exists( $dirpath ) ) {
					$filepath = $dirpath;

					break;
				}
			}
		}

		if ( $filepath ) {

			// Extract context.
			unset( $this->context['filepath'] );
			unset( $this->context['output'] );

			extract( $this->context );

			// Render part.
			ob_start();

			include $filepath;
			$output .= ob_get_contents();

			ob_end_clean();
		}

		return $output;
	}
}
