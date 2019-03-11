<?php
/**
 * Abstract email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract email class.
 *
 * @class Email
 */
abstract class Email {
	use Traits\Mutator;

	/**
	 * Email recipient.
	 *
	 * @var string
	 */
	protected $recipient;

	/**
	 * Email subject.
	 *
	 * @var string
	 */
	protected $subject;

	/**
	 * Email body.
	 *
	 * @var string
	 */
	protected $body;

	/**
	 * Email tokens.
	 *
	 * @var array
	 */
	protected $tokens = [];

	/**
	 * Email headers.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Email attachments.
	 *
	 * @var array
	 */
	protected $attachments = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}
	}

	/**
	 * Gets email headers.
	 *
	 * @return array
	 */
	final protected function get_headers() {
		$headers = [];

		// Set content type.
		$headers['Content-Type'] = 'text/html; charset=UTF-8';

		return hp\merge_arrays( $this->headers, $headers );
	}

	/**
	 * Sends email.
	 *
	 * @return bool
	 */
	public function send() {
		return wp_mail(
			$this->recipient,
			$this->subject,
			$this->body,
			array_map(
				function( $name, $value ) {
					return $name . ': ' . $value;
				},
				array_keys( $this->get_headers() ),
				$this->get_headers()
			),
			$this->attachments
		);
	}
}
