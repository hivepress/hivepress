<?php
/**
 * Abstract email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;
use HivePress\Traits;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract email class.
 */
abstract class Email {
	use Traits\Mutator;
	use Traits\Meta;
	use Traits\Context;

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
	 * @param array $meta Class meta values.
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
			 * Filters the email class meta. The class meta stores properties related to the email type rather than a specific email instance. For example, it stores the email description displayed on the email edit page. The dynamic part of the hook refers to the email name (e.g. `listing_expire`). You can check the available emails in the `includes/emails` directory of HivePress.
			 *
			 * @hook hivepress/v1/emails/{email_name}/meta
			 * @param {array} $meta Class meta values.
			 * @return {array} Class meta values.
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
					'content-type' => 'text/html; charset=UTF-8',
				],
			],
			$args
		);

		// Filter properties.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters the email properties. The dynamic part of the hook refers to the email name (e.g. `listing_expire`). You can check the available emails in the `includes/emails` directory of HivePress.
			 *
			 * @hook hivepress/v1/emails/{email_name}
			 * @param {array} $props Email properties.
			 * @param {object} $email Email object.
			 * @return {array} Email properties.
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
		$this->subject = hp\replace_tokens( $this->tokens, $this->subject );
		$this->body    = hp\replace_tokens( $this->tokens, $this->body );

		// Convert URLs.
		$this->body = make_clickable( $this->body );
	}

	/**
	 * Gets email subject.
	 *
	 * @return string
	 */
	final public function get_subject() {
		return $this->subject;
	}

	/**
	 * Gets email body.
	 *
	 * @return string
	 */
	final public function get_body() {
		return $this->body;
	}

	/**
	 * Gets email tokens.
	 *
	 * @return array
	 */
	final public function get_tokens() {
		return $this->tokens;
	}

	/**
	 * Sends email.
	 *
	 * @return bool
	 */
	final public function send() {

		/**
		 * Fires when a new email is sent. The dynamic part of the hook refers to the email name (e.g. `listing_expire`). You can check the available emails in the `includes/emails` directory of HivePress.
		 *
		 * @hook hivepress/v1/emails/{email_name}/send
		 * @param {object} $email Email object.
		 */
		do_action( 'hivepress/v1/emails/' . static::get_meta( 'name' ) . '/send', $this );

		// Check content.
		if ( ! $this->body ) {
			return false;
		}

		// Get headers.
		$headers = array_map(
			function( $name, $value ) {
				return $name . ': ' . $value;
			},
			array_keys( $this->headers ),
			$this->headers
		);

		// Get content.
		$content = ( new Blocks\Template(
			[
				'template' => 'email',

				'context'  => [
					'email' => $this,
				],
			]
		) )->render();

		// Send email.
		return wp_mail( $this->recipient, $this->subject, $content, $headers );
	}
}
