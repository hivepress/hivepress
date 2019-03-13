<?php
/**
 * Listing delete form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing delete form class.
 *
 * @class Listing_Delete
 */
class Listing_Delete extends Model_Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'model'    => 'listing',
				'method'   => 'DELETE',
				'redirect' => true,

				'button'   => [
					'label' => esc_html__( 'Delete Listing', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
