<?php
/**
 * Listing update email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Sent to admins when moderated listing details are changed.
 */
class Listing_Update extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => hivepress()->translator->get_string( 'listing_updated' ),
				'body'    => hp\sanitize_html( __( 'Listing "%listing_title%" %listing_url% has been updated with the following details: %listing_attributes%.', 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
