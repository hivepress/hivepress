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
 *
 * @class Email
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
					'content-type' => 'text/html; charset=UTF-8',
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
		if ( static::get_meta( 'label' ) ) {

			// Get email.
			$email = get_page_by_path( static::get_meta( 'name' ), OBJECT, 'hp_email' );

			if ( $email ) {

				// Set subject.
				if ( $email->post_title ) {
					$this->subject = $email->post_title;
				}

				// Set body.
				$this->body = apply_filters( 'the_content', $email->post_content );
			}
		}

		// Replace tokens.
		$this->body = hp\replace_tokens( $this->tokens, $this->body );

		// Convert URLs.
		$this->body = make_clickable( $this->body );
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
	 * Sends email.
	 *
	 * @return bool
	 */
	final public function send() {

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
