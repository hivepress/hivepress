<?php
/**
 * Password field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Password field class.
 *
 * @class Password
 */
class Password extends Text {

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {

		// Set maximum length.
		$this->max_length = 64;

		parent::__construct( $args );
	}

	// Forbid setting maximum length.
	final private function set_max_length() {}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {}
}
