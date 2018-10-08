<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages emails.
 *
 * @class Email
 */
class Email extends Component {

	/**
	 * Array of emails.
	 *
	 * @var array
	 */
	private $emails = [];

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		// Initialize emails.
		add_action( 'hivepress/component/init_emails', [ $this, 'init_emails' ], 10, 2 );

		// Set content type.
		add_filter( 'wp_mail_content_type', [ $this, 'set_content_type' ] );
	}

	/**
	 * Initializes emails.
	 *
	 * @param array  $emails
	 * @param string $component_id
	 */
	public function init_emails( $emails, $component_id ) {
		$this->emails = array_merge(
			$this->emails,
			array_combine(
				array_map(
					function( $email_name ) use ( $component_id ) {
						return $component_id . '__' . $email_name;
					},
					array_keys( $emails )
				),
				$emails
			)
		);
	}

	/**
	 * Sets content type.
	 */
	public function set_content_type() {
		return 'text/html';
	}

	/**
	 * Sends an email.
	 *
	 * @param int   $id
	 * @param array $args
	 * @return bool
	 */
	public function send( $id, $args = [] ) {

		// Set default arguments.
		$args = hp_merge_arrays(
			[
				'message' => get_option( 'hp_email_' . preg_replace( '/[_]+/', '_', $id ) ),
			],
			hp_get_array_value( $this->emails, $id, [] ),
			$args
		);

		// Replace placeholders.
		if ( isset( $args['placeholders'] ) && is_array( $args['placeholders'] ) ) {
			$args['message'] = hp_replace_placeholders( $args['placeholders'], $args['message'] );
		}

		// Render email content.
		$output = hivepress()->template->render_template( 'email', [ 'email_content' => $args['message'] ] );

		return wp_mail( $args['to'], $args['subject'], $output );
	}
}
