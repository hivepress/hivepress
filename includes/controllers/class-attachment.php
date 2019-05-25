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
	 * Controller name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Controller routes.
	 *
	 * @var array
	 */
	protected static $routes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Controller arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
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

		parent::init( $args );
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

		// Get model class.
		$model_class = '\HivePress\Models\\' . $request->get_param( 'parent_model' );

		if ( ! class_exists( $model_class ) ) {
			return hp\rest_error( 400 );
		}

		// Get field.
		$field = hp\get_array_value( $model_class::get_fields(), $request->get_param( 'parent_field' ) );

		if ( is_null( $field ) || $field::get_type() !== 'attachment_upload' ) {
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
			return hp\rest_error( 403, sprintf( esc_html__( 'Only up to %s files can be uploaded', 'hivepress' ), number_format_i18n( $field->get_max_files() ) ) );
		}

		// Check file format.
		$file_type    = wp_check_filetype( wp_unslash( $_FILES['file']['name'] ) );
		$file_formats = array_map( 'strtoupper', $field->get_file_formats() );

		if ( ! in_array( strtoupper( $file_type['ext'] ), $file_formats, true ) ) {
			return hp\rest_error( 400, sprintf( esc_html__( 'Only %s files are allowed', 'hivepress' ), implode( ', ', $file_extensions ) ) );
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
				'post__in'  => [ absint( $request->get_param( 'id' ) ) ],
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
				'post__in'  => [ absint( $request->get_param( 'id' ) ) ],
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
