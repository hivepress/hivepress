<?php
/**
 * Vendor controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Forms;
use HivePress\Blocks;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor controller class.
 *
 * @class Vendor
 */
class Vendor extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					[
						'rule'   => 'is_vendor_view_page',
						'action' => 'render_vendor_view_page',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Checks vendor view page.
	 *
	 * @return bool
	 */
	public function is_vendor_view_page() {
		return is_singular( 'hp_vendor' );
	}

	/**
	 * Renders vendor view page.
	 *
	 * @return string
	 */
	public function render_vendor_view_page() {
		the_post();

		$output  = ( new Blocks\Element( [ 'file_path' => 'header' ] ) )->render();
		$output .= ( new Blocks\Template( [ 'template_name' => 'vendor_view_page' ] ) )->render();
		$output .= ( new Blocks\Element( [ 'file_path' => 'footer' ] ) )->render();

		return $output;
	}
}
