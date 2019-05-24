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
 * Listing report email class.
 *
 * @class Listing_Report
 */
class Listing_Report extends Email {

	/**
	 * Email name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Listing Reported', 'hivepress' ),
				'body'    => hp\sanitize_html( __( 'Listing "%listing_title%" %listing_url% has been reported for the following reason: %report_reason%', 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
