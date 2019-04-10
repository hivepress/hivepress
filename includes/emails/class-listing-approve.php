<?php
/**
 * Listing approve email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing approve email class.
 *
 * @class Listing_Approve
 */
class Listing_Approve extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Listing Approved', 'hivepress' ),
				'body'    => hp_sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has been approved, click on the following link to view it: %listing_url%', 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
