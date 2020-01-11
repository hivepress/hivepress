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
	 * Email context.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Email meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'name' => hp\get_class_name( static::class ),
			],
			$meta
		);

		// Filter meta.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters email meta.
			 *
			 * @filter /emails/{$name}/meta
			 * @description Filters email meta.
			 * @param string $name Email name.
			 * @param array $meta Email meta.
			 */
			$meta = apply_filters( 'hivepress/v1/emails/' . hp\get_class_name( $class ) . '/meta', $meta );
		}

		// Set meta.
		static::set_meta( $meta );
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

		// Filter properties.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters email arguments.
			 *
			 * @filter /emails/{$name}
			 * @description Filters email arguments.
			 * @param string $name Email name.
			 * @param array $args Email arguments.
			 * @param object $object Email object.
			 */
			$args = apply_filters( 'hivepress/v1/emails/' . hp\get_class_name( $class ), $args, $this );
		}

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps email properties.
	 */
	protected function boot() {

		// Replace tokens.
		$this->body = hp\replace_tokens( $this->tokens, $this->body );
	}

	/**
	 * Gets context values.
	 *
	 * @param string $name Context name.
	 * @return mixed
	 */
	final public function get_context( $name = '' ) {
		$context = $this->context;

		if ( $name ) {
			$context = hp\get_array_value( $context, $name );
		}

		return $context;
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
