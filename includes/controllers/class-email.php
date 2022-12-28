<?php
/**
 * Email controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages emails.
 */
final class Email extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'email_show_tokens' => [
						'base'   => 'admin_base',
						'path'   => '/show-tokens',
						'method' => 'POST',
						'action' => [ $this, 'show_tokens' ],
						'rest'   => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Show tokens.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function show_tokens( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			return hp\rest_error( 403 );
		}

		// Get email event.
		$email_event = sanitize_text_field( $request->get_param( 'email_event' ) );

		// Set default description.
		$default_description = esc_html__( 'Please choose email event to see available email tokens', 'hivepress' );

		if ( ! $email_event ) {
			return hp\rest_response( 200, [ 'tokens' => $default_description ] );
		}

		// Get email object.
		$email = hp\get_array_value( hivepress()->get_classes( 'emails' ), $email_event );

		if ( ! $email && ! $email::get_meta( 'label' ) ) {
			return hp\rest_response( 400 );
		}

		// Set output.
		$output = '';

		if ( $email::get_meta( 'description' ) ) {
			$output .= $email::get_meta( 'description' ) . ' ';
		}

		if ( $email::get_meta( 'tokens' ) ) {
			$output .= sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '<code>%' . implode( '%</code>, <code>%', $email::get_meta( 'tokens' ) ) . '%</code>' );
		}

		if ( ! $output ) {
			$output = $default_description;
		}

		return hp\rest_response( 200, [ 'tokens' => trim( $output ) ] );
	}
}
