<?php
/**
 * Email component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Email component class.
 *
 * @class Email
 */
final class Email {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Set content type.
		add_filter( 'wp_mail_content_type', [ $this, 'set_content_type' ] );
	}

	/**
	 * Sets content type.
	 */
	public function set_content_type() {
		return 'text/html';
	}
}
