<?php
/**
 * Listing submit email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submit email class.
 *
 * @class Listing_Submit
 */
class Listing_Submit extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Listing Submitted', 'hivepress' ),
				'body'    => hp_sanitize_html( __( 'A new listing "%listing_title%" has been submitted, click on the following link to view it: %listing_url%', 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
