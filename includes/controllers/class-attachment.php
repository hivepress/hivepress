<?php
/**
 * Attachment controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Attachment controller class.
 *
 * @class Attachment
 */
final class Attachment extends Controller {

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
					 * @param int $parent Parent ID.
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
					 * @param int $sort_order Sorting order.
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

		// Get parent model.
		$parent_model = hp\create_class_instance( '\HivePress\Models\\' . sanitize_key( $request->get_param( 'parent_model' ) ) );

		if ( empty( $parent_model ) ) {
			return hp\rest_error( 400 );
		}

		// Get parent object.
		$parent = $parent_model::query()->get_by_id( $request->get_param( 'parent' ) );

		if ( empty( $parent ) ) {
			return hp\rest_error( 400 );
		}

		// Get user ID.
		$user_id = $parent->get_user__id();

		if ( $parent::_get_meta( 'type' ) === 'user' ) {
			$user_id = $parent->get_id();
		}

		// Check permissions.
		if ( get_current_user_id() !== $user_id || ( $parent::_get_meta( 'type' ) === 'post' && ! in_array( $parent->get_status(), [ 'auto-draft', 'draft', 'publish' ], true ) ) ) {
			return hp\rest_error( 403 );
		}

		// Get parent field.
		$parent_field = hp\get_array_value( $parent->_get_fields(), sanitize_key( $request->get_param( 'parent_field' ) ) );

		if ( empty( $parent_field ) || $parent_field::get_meta( 'name' ) !== 'attachment_upload' ) {
			return hp\rest_error( 400 );
		}

		// Get attachments.
		$attachments = Models\Attachment::query()->filter(
			[
				'parent_model' => $parent::_get_meta( 'name' ),
				'parent_field' => $parent_field->get_name(),
				'parent'       => $parent->get_id(),
			]
		)->get();

		// Check attachment limit.
		if ( $parent_field->is_multiple() && $attachments->count() >= $parent_field->get_max_files() ) {
			return hp\rest_error( 403, sprintf( esc_html__( 'Only up to %s files can be uploaded.', 'hivepress' ), number_format_i18n( $parent_field->get_max_files() ) ) );
		}

		// Check file.
		if ( ! isset( $_FILES['file'] ) ) {
			return hp\rest_error( 400 );
		}

		// Check file format.
		$file_type    = wp_check_filetype_and_ext( $_FILES['file']['tmp_name'], $_FILES['file']['name'] );
		$file_formats = array_map( 'strtoupper', $parent_field->get_formats() );

		if ( ! $file_type['ext'] || ! in_array( strtoupper( $file_type['ext'] ), $file_formats, true ) ) {
			return hp\rest_error( 400, sprintf( esc_html__( 'Only %s files are allowed.', 'hivepress' ), implode( ', ', $file_formats ) ) );
		}

		// Get parent ID.
		$parent_id = 0;

		if ( $parent::_get_meta( 'type' ) === 'post' ) {
			$parent_id = $parent->get_id();
		}

		// Upload attachment.
		$attachment_id = media_handle_upload( 'file', $parent_id );

		if ( is_wp_error( $attachment_id ) ) {
			return hp\rest_error( 400, $attachment_id->get_error_messages() );
		}

		// Get attachment.
		$attachment = Models\Attachment::query()->get_by_id( $attachment_id );

		// Update attachment.
		$attachment->fill(
			[
				'sort_order'   => $attachments->count(),
				'parent_model' => $parent::_get_meta( 'name' ),
				'parent_field' => $parent_field->get_name(),
				'parent'       => $parent->get_id(),
			]
		);

		if ( ! $attachment->save() ) {
			return hp\rest_error( 400, $attachment->_get_errors() );
		}

		if ( ! $parent_field->is_multiple() ) {

			// Update parent object.
			$parent->fill(
				[
					$parent_field->get_name() => $attachment->get_id(),
				]
			)->save();

			// Delete attachments.
			$attachments->filter(
				[
					'id__not_in' => [ $attachment->get_id() ],
				]
			)->delete();
		} else {

			// Fire update action.
			do_action( 'hivepress/v1/models/' . $attachment->get_parent_model() . '/update_' . $attachment->get_parent_field(), $attachment->get_parent__id() );
		}

		// Render attachment.
		$data = [
			'id' => $attachment->get_id(),
		];

		if ( $request->get_param( 'render' ) ) {
			$data['html'] = $parent_field->render_attachment( $attachment->get_id() );
		}

		return hp\rest_response( 201, $data );
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

		// Get attachment.
		$attachment = Models\Attachment::query()->get_by_id( $request->get_param( 'attachment_id' ) );

		if ( empty( $attachment ) ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_others_posts' ) && get_current_user_id() !== $attachment->get_user__id() ) {
			return hp\rest_error( 403 );
		}

		// Update attachment.
		$attachment->set_sort_order( $request->get_param( 'sort_order' ) );

		if ( ! $attachment->save() ) {
			return hp\rest_error( 400, $attachment->_get_errors() );
		}

		// Fire update action.
		do_action( 'hivepress/v1/models/' . $attachment->get_parent_model() . '/update_' . $attachment->get_parent_field(), $attachment->get_parent__id() );

		return hp\rest_response(
			200,
			[
				'id' => $attachment->get_id(),
			]
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

		// Get attachment.
		$attachment = Models\Attachment::query()->get_by_id( $request->get_param( 'attachment_id' ) );

		if ( empty( $attachment ) ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'delete_others_posts' ) && get_current_user_id() !== $attachment->get_user__id() ) {
			return hp\rest_error( 403 );
		}

		// Delete attachment.
		if ( ! $attachment->delete() ) {
			return hp\rest_error( 400 );
		}

		// Fire update action.
		do_action( 'hivepress/v1/models/' . $attachment->get_parent_model() . '/update_' . $attachment->get_parent_field(), $attachment->get_parent__id() );

		return hp\rest_response( 204 );
	}
}
