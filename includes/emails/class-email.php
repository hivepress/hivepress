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
	use Traits\Meta;

	/**
	 * Email meta.
	 *
	 * @var array
	 */
	protected static $meta;

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
	 * Email headers.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Email tokens.
	 *
	 * @var array
	 */
	protected $tokens = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Email arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'meta' => [
					'name' => hp\get_class_name( static::class ),
				],
			],
			$args
		);

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'body'    => get_option( 'hp_email_' . static::get_meta( 'name' ) ),

				'headers' => [
					'Content-Type' => 'text/html; charset=UTF-8',
				],
			],
			$args
		);

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->bootstrap();
	}

	/**
	 * Bootstraps email properties.
	 */
	protected function bootstrap() {

		// Replace tokens.
		$this->body = hp\replace_tokens( $this->tokens, $this->body );
	}

	/**
	 * Sends email.
	 *
	 * @return bool
	 */
	final public function send() {
		if ( $this->body ) {
			return wp_mail(
				$this->recipient,
				$this->subject,
				$this->body,
				array_map(
					function( $name, $value ) {
						return $name . ': ' . $value;
					},
					array_keys( $this->headers ),
					$this->headers
				)
			);
		}
	}
}
