<?php
/**
 * Attachment controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;

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
		$args = hp\merge_arrays(
			[
				'routes' => [

					/**
					 * Attachments API route.
					 *
					 * @resource Attachments
					 * @description The attachments API allows you to upload, update and delete attachments.
					 */
					'attachments_resource'     => [
						'path' => '/attachments',
						'rest' => true,
					],

					'attachment_resource'      => [
						'base' => 'attachments_resource',
						'path' => '/(?P<attachment_id>\d+)',
						'rest' => true,
					],

					/**
					 * Uploads attachment.
					 *
					 * @endpoint Upload attachment
					 * @route /attachments
					 * @method POST
					 * @param string $parent_model Parent model.
					 * @param string $parent_field Parent field.
					 * @param int $parent_id Parent ID.
					 */
					'attachment_upload_action' => [
						'base'   => 'attachments_resource',
						'method' => 'POST',
						'action' => [ $this, 'upload_attachment' ],
						'rest'   => true,
					],

					/**
					 * Updates attachment.
					 *
					 * @endpoint Update attachment
					 * @route /attachments/<id>
					 * @method POST
					 * @param int $order Order.
					 */
					'attachment_update_action' => [
						'base'   => 'attachment_resource',
						'method' => 'POST',
						'action' => [ $this, 'update_attachment' ],
						'rest'   => true,
					],

					/**
					 * Deletes atachment.
					 *
					 * @endpoint Delete attachment
					 * @route /attachments/<id>
					 * @method DELETE
					 */
					'attachment_delete_action' => [
						'base'   => 'attachment_resource',
						'method' => 'DELETE',
						'action' => [ $this, 'delete_attachment' ],
						'rest'   => true,
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
			return hp\rest_error( 401 );
		}

		// Get fields.
		$fields = hp\call_class_method( '\HivePress\Models\\' . $request->get_param( 'parent_model' ), 'get_fields' );

		if ( empty( $fields ) ) {
			return hp\rest_error( 400 );
		}

		// Get field.
		$field = hp\get_array_value( $fields, $request->get_param( 'parent_field' ) );

		if ( empty( $field ) || $field::get_display_type() !== 'attachment_upload' ) {
			return hp\rest_error( 400 );
		}

		// Get parent ID.
		$parent_id = 0;

		if ( $request->get_param( 'parent_model' ) !== 'user' ) {
			$parent_id = hp\get_post_id(
				[
					'post_type'   => hp\prefix( $request->get_param( 'parent_model' ) ),
					'post_status' => [ 'auto-draft', 'draft', 'publish' ],
					'post__in'    => [ absint( $request->get_param( 'parent_id' ) ) ],
					'author'      => get_current_user_id(),
				]
			);

			if ( 0 === $parent_id ) {
				return hp\rest_error( 400 );
			}
		}

		// Get attachment IDs.
		$attachment_ids = get_posts(
			[
				'post_type'      => 'attachment',
				'post_parent'    => $parent_id,
				'author'         => get_current_user_id(),
				'meta_key'       => 'hp_parent_field',
				'meta_value'     => $field->get_name(),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			]
		);

		// Check attachment quantity.
		if ( $field->is_multiple() && count( $attachment_ids ) >= $field->get_max_files() ) {
			return hp\rest_error( 403, sprintf( esc_html__( 'Only up to %s files can be uploaded.', 'hivepress' ), number_format_i18n( $field->get_max_files() ) ) );
		}

		// Check file format.
		$file_type    = wp_check_filetype( wp_unslash( $_FILES['file']['name'] ) );
		$file_formats = array_map( 'strtoupper', $field->get_formats() );

		if ( ! in_array( strtoupper( $file_type['ext'] ), $file_formats, true ) ) {
			return hp\rest_error( 400, sprintf( esc_html__( 'Only %s files are allowed.', 'hivepress' ), implode( ', ', $file_extensions ) ) );
		}

		// Delete attachments.
		if ( ! $field->is_multiple() ) {
			foreach ( $attachment_ids as $attachment_id ) {
				wp_delete_attachment( $attachment_id, true );
			}
		}

		// Upload attachment.
		$attachment_id = media_handle_upload( 'file', $parent_id );

		if ( is_wp_error( $attachment_id ) ) {
			return hp\rest_error( 400, $attachment_id->get_error_messages() );
		}

		// Set order.
		if ( $field->is_multiple() ) {
			wp_update_post(
				[
					'ID'         => $attachment_id,
					'menu_order' => count( $attachment_ids ),
				]
			);
		}

		// Set field.
		update_post_meta( $attachment_id, 'hp_parent_field', $field->get_name() );

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
			return hp\rest_error( 401 );
		}

		// Get attachment ID.
		$attachment_id = hp\get_post_id(
			[
				'post_type' => 'attachment',
				'post__in'  => [ absint( $request->get_param( 'attachment_id' ) ) ],
				'author'    => get_current_user_id(),
			]
		);

		if ( 0 === $attachment_id ) {
			return hp\rest_error( 404 );
		}

		// Update attachment.
		if ( wp_update_post(
			[
				'ID'         => $attachment_id,
				'menu_order' => absint( $request->get_param( 'order' ) ),
			]
		) === 0 ) {
			return hp\rest_error( 400 );
		}

		return new \WP_Rest_Response(
			[
				'data' => [
					'id' => $attachment_id,
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
			return hp\rest_error( 401 );
		}

		// Get attachment ID.
		$attachment_id = hp\get_post_id(
			[
				'post_type' => 'attachment',
				'post__in'  => [ absint( $request->get_param( 'attachment_id' ) ) ],
				'author'    => get_current_user_id(),
			]
		);

		if ( 0 === $attachment_id ) {
			return hp\rest_error( 404 );
		}

		// Delete attachment.
		if ( wp_delete_attachment( $attachment_id, true ) === false ) {
			return hp\rest_error( 400 );
		}

		return new \WP_Rest_Response( (object) [], 204 );
	}
}
