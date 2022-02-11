<?php
/**
 * Listing report email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Sent to admins when a listing is reported.
 */
class Listing_Report extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => hivepress()->translator->get_string( 'listing_reported' ),
				'body'    => hp\sanitize_html( __( 'Listing "%listing_title%" %listing_url% has been reported with the following details: %report_details%', 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
