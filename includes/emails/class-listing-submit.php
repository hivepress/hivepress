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
	 * Email name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Email subject.
	 *
	 * @var string
	 */
	protected static $subject;

	/**
	 * Email body.
	 *
	 * @var string
	 */
	protected static $body;

	/**
	 * Class initializer.
	 *
	 * @param array $args Email arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => hivepress()->translator->get_string( 'listing_submitted' ),
				'body'    => hp\sanitize_html( __( 'A new listing "%listing_title%" has been submitted, click on the following link to view it: %listing_url%', 'hivepress' ) ),
			],
			$args
		);

		parent::init( $args );
	}
}
