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
		$form = hp_get_array_value( hivepress()->get_forms(), $request->get_param( 'form_name' ) );

		if ( is_null( $form ) && ! current_user_can( 'upload_files' ) ) {
			return hp_rest_error( 400 );
		}

		// Get field.
		$field = null;

		if ( ! is_null( $form ) ) {
			$field = hp_get_array_value( $form->get_fields(), $request->get_param( 'field_name' ) );

			if ( is_null( $field ) && ! current_user_can( 'upload_files' ) ) {
				return hp_rest_error( 400 );
			}
		}

		// Get parameters.
		$parent_id = absint( $request->get_param( 'parent_id' ) );
		$order     = absint( $request->get_param( 'order' ) );

		if ( ! is_null( $field ) ) {
			$parent_id = hp_get_post_id(
				[
					'post_type'   => 'any',
					'post_status' => [ 'auto-draft', 'draft', 'publish' ],
					'post__in'    => [ absint( $request->get_param( 'parent_id' ) ) ],
					'author'      => get_current_user_id(),
				]
			);

			// Get attachment IDs.
			$attachment_ids = get_posts(
				[
					'post_type'      => 'attachment',
					'post_parent'    => $parent_id,
					'author'         => get_current_user_id(),
					'meta_key'       => 'hp_field_name',
					'meta_value'     => $field->get_name(),
					'posts_per_page' => -1,
					'fields'         => 'ids',
				]
			);

			// Check attachment quantity.
			if ( $field->get_multiple() && count( $attachment_ids ) >= absint( $field->get_max_files() ) ) {
				return hp_rest_error( 400, sprintf( esc_html__( 'Only up to %s files can be uploaded.', 'hivepress' ), numer_format_i18n( $field->get_max_files() ) ) );
			}

			// Check file format.
			$file_type    = wp_check_filetype( wp_unslash( $_FILES['file']['name'] ) );
			$file_formats = array_map( 'strtoupper', $field->get_file_formats() );

			if ( ! in_array( strtoupper( $file_type['ext'] ), $file_formats, true ) ) {
				return hp_rest_error( 400, sprintf( esc_html__( 'Only %s files are allowed.', 'hivepress' ), implode( ', ', $file_extensions ) ) );
			}

			if ( $field->get_multiple() ) {

				// Get order.
				$order = count( $attachment_ids );
			} else {

				// Delete attachments.
				foreach ( $attachment_ids as $attachment_id ) {
					wp_delete_attachment( $attachment_id, true );
				}
			}
		}

		// Upload attachment.
		$attachment_id = media_handle_upload( 'file', $parent_id );

		if ( is_wp_error( $attachment_id ) ) {
			return hp_rest_error( 400, $attachment_id->get_error_messages() );
		}

		// Update attachment.
		wp_update_post(
			[
				'ID'          => $attachment_id,
				'post_parent' => $parent_id,
				'menu_order'  => $order,
			]
		);

		if ( ! is_null( $field ) ) {
			update_post_meta( $attachment_id, 'hp_field_name', $field->get_name() );
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
