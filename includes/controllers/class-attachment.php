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
 * Manages file attachments.
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
					'attachments_resource'     => [
						'path' => '/attachments',
						'rest' => true,
					],

					/**
					* @OA\Parameter(
					*     name="attachment_id",
					*     description="Attachment ID.",
					*     in="path",
					*     required=true,
					*     @OA\Schema(type="integer"),
					* ),
					*/
					'attachment_resource'      => [
						'base' => 'attachments_resource',
						'path' => '/(?P<attachment_id>\d+)',
						'rest' => true,
					],

					/**
					 * @OA\Post(
					 *     path="/attachments",
					 *     summary="Upload an attachment",
					 *     tags={"Attachments"},
					 *     @OA\RequestBody(
					 *       @OA\JsonContent(
					 *         @OA\Property(property="parent", type="integer", description="Parent object ID."),
					 *         @OA\Property(property="parent_model", type="string", description="Parent model name (e.g. `user`)."),
					 *         @OA\Property(property="parent_field", type="string", description="Parent model field (e.g. `image`)."),
					 *         @OA\Property(property="file", type="string", format="file", description="File contents."),
					 *       ),
					 *     ),
					 *     @OA\Response(response="201", description="")
					 * )
					 */
					'attachment_upload_action' => [
						'base'   => 'attachments_resource',
						'method' => 'POST',
						'action' => [ $this, 'upload_attachment' ],
						'rest'   => true,
					],

					/**
					 * @OA\Post(
					 *     path="/attachments/{attachment_id}",
					 *     summary="Update an attachment",
					 *     tags={"Attachments"},
					 *     @OA\Parameter(ref="#/components/parameters/attachment_id"),
					 *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Attachment")),
					 *     @OA\Response(response="200", description="")
					 * )
					 */
					'attachment_update_action' => [
						'base'   => 'attachment_resource',
						'method' => 'POST',
						'action' => [ $this, 'update_attachment' ],
						'rest'   => true,
					],

					/**
					 * @OA\Delete(
					 *     path="/attachments/{attachment_id}",
					 *     summary="Delete an attachment",
					 *     tags={"Attachments"},
					 *     @OA\Parameter(ref="#/components/parameters/attachment_id"),
					 *     @OA\Response(response="204", description="")
					 * )
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
		$parent = $parent_model->query()->get_by_id( $request->get_param( 'parent' ) );

		if ( empty( $parent ) ) {
			return hp\rest_error( 400 );
		}

		// Get user ID.
		$user_id = $parent->get_user__id();

		if ( $parent::_get_meta( 'type' ) === 'user' ) {
			$user_id = $parent->get_id();
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_others_posts' ) && ( get_current_user_id() !== $user_id || ( $parent::_get_meta( 'type' ) === 'post' && ! in_array( $parent->get_status(), [ 'auto-draft', 'draft', 'publish' ], true ) ) ) ) {
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
			/* translators: %s: files number. */
			return hp\rest_error( 403, sprintf( esc_html__( 'Only up to %s files can be uploaded.', 'hivepress' ), number_format_i18n( $parent_field->get_max_files() ) ) );
		}

		// Check file.
		if ( ! isset( $_FILES['file'] ) ) {
			return hp\rest_error( 400 );
		}

		// Check file format.
		if ( $parent_field->get_formats() && ! hivepress()->attachment->is_valid_file( $_FILES['file']['tmp_name'], $_FILES['file']['name'], $parent_field->get_formats() ) ) {

			/* translators: %s: file extensions. */
			return hp\rest_error( 400, sprintf( esc_html__( 'Only %s files are allowed.', 'hivepress' ), strtoupper( implode( ', ', $parent_field->get_formats() ) ) ) );
		}

		// Get file callback.
		$file_callback = null;

		if ( $parent_field->is_protected() ) {
			$file_callback = function( $dir, $filename, $ext ) {
				if ( strlen( $filename ) ) {

					/**
					 * Filters the attachment filename before the uploading.
					 *
					 * @hook hivepress/v1/models/attachment/filename
					 * @param {string} $filename Filename.
					 * @param {string} $ext File extension.
					 * @param {string} $path Directory path.
					 * @return {string} Filename.
					 */
					$filename = apply_filters( 'hivepress/v1/models/attachment/filename', $filename, $ext, $dir );
				}

				return $filename;
			};
		}

		// Get parent ID.
		$parent_id = 0;

		if ( $parent::_get_meta( 'type' ) === 'post' ) {
			$parent_id = $parent->get_id();
		}

		// Upload attachment.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attachment_id = media_handle_upload(
			'file',
			$parent_id,
			[],
			[
				'test_form'                => false,
				'unique_filename_callback' => $file_callback,
			]
		);

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

			// Delete attachments.
			$attachments->filter(
				[
					'id__not_in' => [ $attachment->get_id() ],
				]
			)->delete();

			// Update parent object.
			$parent->fill(
				[
					$parent_field->get_name() => $attachment->get_id(),
				]
			)->save( [ $parent_field->get_name() ] );
		} else {

			// Fire update action.
			do_action( 'hivepress/v1/models/' . $attachment->get_parent_model() . '/update_' . $attachment->get_parent_field(), $attachment->get_parent__id() );
		}

		// Render attachment.
		$data = [
			'id' => $attachment->get_id(),
		];

		if ( $request->get_param( 'render' ) ) {
			$data['html'] = $parent_field->render_attachment( $attachment );
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

		// Get parent object.
		$parent = $attachment->get_parent();

		if ( empty( $parent ) ) {
			return hp\rest_error( 400 );
		}

		// Get user ID.
		$user_id = $parent->get_user__id();

		if ( $parent::_get_meta( 'type' ) === 'user' ) {
			$user_id = $parent->get_id();
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_others_posts' ) && ( get_current_user_id() !== $user_id || ( $parent::_get_meta( 'type' ) === 'post' && ! in_array( $parent->get_status(), [ 'auto-draft', 'draft', 'publish' ], true ) ) ) ) {
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

		// Get parent object.
		$parent = $attachment->get_parent();

		if ( empty( $parent ) ) {
			return hp\rest_error( 400 );
		}

		// Get user ID.
		$user_id = $parent->get_user__id();

		if ( $parent::_get_meta( 'type' ) === 'user' ) {
			$user_id = $parent->get_id();
		}

		// Check permissions.
		if ( ! current_user_can( 'delete_others_posts' ) && ( get_current_user_id() !== $user_id || ( $parent::_get_meta( 'type' ) === 'post' && ! in_array( $parent->get_status(), [ 'auto-draft', 'draft', 'publish' ], true ) ) ) ) {
			return hp\rest_error( 403 );
		}

		// Get parent field.
		$parent_field = hp\get_array_value( $parent->_get_fields(), $attachment->get_parent_field() );

		if ( empty( $parent_field ) || $parent_field::get_meta( 'name' ) !== 'attachment_upload' ) {
			return hp\rest_error( 400 );
		}

		// Check requirements.
		if ( $parent_field->is_required() && ! $parent_field->is_multiple() ) {
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
