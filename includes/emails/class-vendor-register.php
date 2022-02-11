<?php
/**
 * Vendor register email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Sent to admins when a new vendor is registered.
 */
class Vendor_Register extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => hivepress()->translator->get_string( 'vendor_registered' ),
				'body'    => hp\sanitize_html( __( 'A new vendor profile has been registered, click on the following link to view it: %vendor_url%', 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
