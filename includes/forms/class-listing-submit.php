<?php
/**
 * Listing submit form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submit form class.
 *
 * @class Listing_Submit
 */
class Listing_Submit extends Listing_Update {

	/**
	 * Form title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Form message.
	 *
	 * @var string
	 */
	protected static $message;

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	protected static $captcha = false;

	/**
	 * Form redirect.
	 *
	 * @var mixed
	 */
	protected static $redirect = false;

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Form button.
	 *
	 * @var object
	 */
	protected static $button;

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => hivepress()->translator->get_string( 'submit_listing' ),
				'message'  => null,
				'redirect' => true,

				'button'   => [
					'label' => hivepress()->translator->get_string( 'submit_listing' ),
				],
			],
			$args
		);

		parent::init( $args );
	}
}
