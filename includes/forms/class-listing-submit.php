<?php
/**
 * Listing submit form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Submits a new listing.
 */
class Listing_Submit extends Listing_Update {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'   => hivepress()->translator->get_string( 'submit_listing' ),
				'captcha' => false,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'message'  => null,
				'redirect' => true,

				'button'   => [
					'label' => hivepress()->translator->get_string( 'submit_listing' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
