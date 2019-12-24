<?php
/**
 * User password request email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User password request email class.
 *
 * @class User_Password_Request
 */
class User_Password_Request extends Email {

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
				'subject' => esc_html__( 'Password Reset', 'hivepress' ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
