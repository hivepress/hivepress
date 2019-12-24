<?php
/**
 * Listing reject email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing reject email class.
 *
 * @class Listing_Reject
 */
class Listing_Reject extends Email {

	/**
	 * Email meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => hivepress()->translator->get_string( 'listing_rejected' ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
