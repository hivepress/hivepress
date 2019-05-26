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
	 * Email name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Email subject.
	 *
	 * @var string
	 */
	protected static $subject;

	/**
	 * Email body.
	 *
	 * @var string
	 */
	protected static $body;

	/**
	 * Email recipient.
	 *
	 * @var string
	 */
	protected $recipient;

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
	 * Class initializer.
	 *
	 * @param array $args Email arguments.
	 */
	public static function init( $args = [] ) {

		// Set name.
		$args['name'] = strtolower( ( new \ReflectionClass( static::class ) )->getShortName() );

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

		// Set content type.
		$this->headers = hp\merge_arrays(
			$this->headers,
			[
				'Content-Type' => 'text/html; charset=UTF-8',
			]
		);

		// Set body.
		$body = get_option( 'hp_email_' . static::$name );

		if ( ! empty( $body ) ) {
			static::$body = $body;
		}

		static::$body = hp\replace_tokens( $this->tokens, static::$body );
	}

	/**
	 * Sends email.
	 *
	 * @return bool
	 */
	public function send() {
		return wp_mail(
			$this->recipient,
			static::$subject,
			static::$message,
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
