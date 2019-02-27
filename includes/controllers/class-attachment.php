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
								'methods' => 'POST',
								'action'  => 'update_attachment',
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

		// Get form.
		$form = hp_get_array_value( hivepress()->get_forms(), $request->get_param( 'form' ) );

		if ( is_null( $form ) ) {
			return hp_rest_error( 400 );
		}

		// Get field.
		$field = hp_get_array_value( $form->get_fields(), $request->get_param( 'field' ) );

		if ( is_null( $field ) ) {
			return hp_rest_error( 400 );
		}

		// Upload attachment.
		$attachment_id = media_handle_upload( 'file', 0 );

		if ( is_wp_error( $attachment_id ) ) {
			return hp_rest_error( 400, $attachment_id->get_error_messages() );
		}

		// Render attachment.
		$data = [
			'id' => $attachment_id,
		];

		if ( $request->get_param( 'render' ) ) {
			$data['html'] = $field->render_attachment( $attachment_id );
		}

		return new \WP_Rest_Response(
			[
				'data' => $data,
			],
			201
		);
	}

	/**
	 * Updates attachment.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function update_attachment( $request ) {

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
		if ( ! current_user_can( 'edit_others_posts' ) && get_current_user_id() !== absint( $attachment->post_author ) ) {
			return hp_rest_error( 403 );
		}

		// Update attachment.
		if ( wp_update_post(
			[
				'ID'         => $attachment->ID,
				'menu_order' => absint( $request->get_param( 'order' ) ),
			]
		) === 0 ) {
			return hp_rest_error( 400 );
		}

		return new \WP_Rest_Response(
			[
				'data' => [
					'id' => $attachment->ID,
				],
			],
			200
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
