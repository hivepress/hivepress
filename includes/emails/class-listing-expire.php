<?php
/**
 * Listing expire email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing expire email class.
 *
 * @class Listing_Expire
 */
class Listing_Expire extends Email {

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
				'subject' => esc_html__( 'Listing Expired', 'hivepress' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has expired.', 'hivepress' ) ),
			],
			$args
		);

		parent::init( $args );
	}
}
