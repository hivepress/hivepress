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

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp_rest_error( 401 );
		}

		// Upload attachment.
		$attachment_id = media_handle_upload( 'todo', 0 );

		if ( is_wp_error( $attachment_id ) ) {
			return hp_rest_error( 400, esc_html__( 'Error uploading file', 'hivepress' ) );
		}

		return new \WP_Rest_Response(
			[
				'data' => [
					'id' => $attachment_id,
				],
			],
			201
		);
	}

	/**
	 * Deletes attachment.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function delete_attachment( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp_rest_error( 401 );
		}

		// Get attachment.
		$attachments = get_posts(
			[
				'post_type'      => 'attachment',
				'post__in'       => [ absint( $request->get_param( 'id' ) ) ],
				'posts_per_page' => 1,
			]
		);

		if ( empty( $attachments ) ) {
			return hp_rest_error( 404 );
		}

		$attachment = reset( $attachments );

		// Check permissions.
		if ( ! current_user_can( 'delete_others_posts' ) && get_current_user_id() !== absint( $attachment->post_author ) ) {
			return hp_rest_error( 403 );
		}

		// Delete attachment.
		if ( wp_delete_attachment( $attachment->ID, true ) === false ) {
			return hp_rest_error( 400, esc_html__( 'Error deleting file', 'hivepress' ) );
		}

		return new \WP_Rest_Response( (object) [], 204 );
	}
}
