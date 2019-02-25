<?php
/**
 * Attachment controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Attachment controller class.
 *
 * @class Attachment
 */
class Attachment extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = array_replace_recursive(
			[
				'routes' => [
					[
						'path'      => '/attachments',
						'rest'      => true,
						'endpoints' => [
							[
								'methods' => 'POST',
								'action'  => 'upload_attachment',
							],

							[
								'path'    => '/(?P<id>\d+)',
								'methods' => 'DELETE',
								'action'  => 'delete_attachment',
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Uploads attachment.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function upload_attachment( $request ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// Check authorization.
		if ( ! is_user_logged_in() ) {
			return hp_rest_error( 401 );
		}

		// Validate form.
		$form = new \HivePress\Forms\Attachment_Upload();

		if ( ! $form->validate() ) {
			return hp_rest_error( 400, $form->get_errors() );
		}

		// Upload attachment.
		$attachment_id = media_handle_upload( $form->get_value( 'todo' ), 0 );

		if ( is_wp_error( $attachment_id ) ) {
			return hp_rest_error( 400, esc_html__( 'Error uploading file.', 'hivepress' ) );
		}

		return new \WP_Rest_Response( null, 200 );
	}

	/**
	 * Deletes attachment.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function delete_attachment( $request ) {
		// todo.
	}
}
