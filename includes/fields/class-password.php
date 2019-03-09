<?php
/**
 * Password field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Password field class.
 *
 * @class Password
 */
class Password extends Text {

	/**
	 * Field type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Field title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {

		// Set minimum length.
		$args['min_length'] = 8;

		// Set maximum length.
		$args['max_length'] = 64;

		parent::__construct( $args );
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {}
}
