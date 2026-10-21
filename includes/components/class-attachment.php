<?php
/**
 * Attachment component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles file attachments.
 */
final class Attachment extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set parent ID.
		add_filter( 'wp_insert_attachment_data', [ $this, 'set_parent_id' ], 10, 2 );

		// Delete attachment.
		add_action( 'hivepress/v1/models/attachment/delete', [ $this, 'delete_attachment' ], 10, 2 );

		// Delete attachments.
		add_action( 'hivepress/v1/models/user/delete', [ $this, 'delete_attachments' ], 10, 2 );
		add_action( 'hivepress/v1/models/post/delete', [ $this, 'delete_attachments' ], 10, 2 );
		add_action( 'hivepress/v1/models/term/delete', [ $this, 'delete_attachments' ], 10, 2 );
		add_action( 'hivepress/v1/models/comment/delete', [ $this, 'delete_attachments' ], 10, 2 );

		// Generate filename.
		add_filter( 'hivepress/v1/models/attachment/filename', [ $this, 'generate_filename' ], 10, 3 );

		// Restrict REST attachments.
		add_filter( 'rest_attachment_query', [ $this, 'filter_attachments' ] );
		add_filter( 'rest_prepare_attachment', [ $this, 'restrict_rest_attachment' ], 1000, 2 );

		// Restrict attachment page.
		add_filter( 'posts_results', [ $this, 'restrict_attachment_page' ], 10, 2 );

		// Restrict attachment embed.
		add_filter( 'oembed_request_post_id', [ $this, 'restrict_attachment_embed' ] );

		// Restrict attachment shortcode.
		add_filter( 'shortcode_atts_gallery', [ $this, 'restrict_attachment_shortcode' ] );
		add_filter( 'shortcode_atts_playlist', [ $this, 'restrict_attachment_shortcode' ] );

		parent::__construct( $args );
	}

	/**
	 * Checks if the attachment is restricted.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	protected function is_restricted_attachment( $attachment_id ) {
		return $attachment_id && get_post_meta( $attachment_id, 'hp_parent_model', true ) && ! current_user_can( 'edit_post', $attachment_id );
	}

	/**
	 * Checks if the file is valid.
	 *
	 * @param string $path File path.
	 * @param string $name File name.
	 * @param array  $exts Allowed extensions.
	 * @return bool
	 */
	public function is_valid_file( $path, $name, $exts ) {
		$type = wp_check_filetype_and_ext( $path, $name );

		if ( ! $type['ext'] || ! in_array( strtoupper( $type['ext'] ), array_map( 'strtoupper', $exts ), true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets allowed parent statuses.
	 *
	 * @return array
	 */
	public function get_parent_statuses() {
		$statuses = [ 'auto-draft', 'draft', 'publish' ];

		if ( hp\is_plugin_active( 'woocommerce' ) ) {
			$statuses[] = 'wc-processing';
		}

		return $statuses;
	}

	/**
	 * Checks if the parent object is editable.
	 *
	 * @param object $object Parent object.
	 * @return bool
	 */
	public function is_editable_parent( $object ) {

		// Get user ID.
		$user_id = $object->get_user__id();

		if ( $object::_get_meta( 'type' ) === 'user' ) {
			$user_id = $object->get_id();
		}

		// Check user.
		if ( get_current_user_id() !== $user_id ) {
			return false;
		}

		// Check status.
		if ( $object::_get_meta( 'type' ) === 'post' ) {
			return in_array( $object->get_status(), $this->get_parent_statuses(), true );
		}

		// Check draft.
		if ( $object::_get_meta( 'type' ) === 'comment' ) {
			$comment = get_comment( $object->get_id() );

			return $comment && ! $comment->comment_post_ID && ! $comment->comment_karma;
		}

		return true;
	}

	/**
	 * Sets the attachment parent ID.
	 *
	 * @param array $attachment Attachment arguments.
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function set_parent_id( $attachment, $args ) {
		if ( isset( $args['comment_count'] ) ) {
			$attachment['comment_count'] = absint( $args['comment_count'] );
		}

		return $attachment;
	}

	/**
	 * Deletes attachment.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param object $attachment Attachment object.
	 */
	public function delete_attachment( $attachment_id, $attachment ) {

		// Get parent object.
		$parent = $attachment->get_parent();

		if ( empty( $parent ) ) {
			return;
		}

		// Get parent field.
		$parent_field = hp\get_array_value( $parent->_get_fields(), $attachment->get_parent_field() );

		if ( empty( $parent_field ) || $parent_field::get_meta( 'name' ) !== 'attachment_upload' ) {
			return;
		}

		if ( ! $parent_field->is_multiple() ) {

			// Update parent object.
			$parent->fill(
				[
					$parent_field->get_name() => null,
				]
			)->save( [ $parent_field->get_name() ] );
		}
	}

	/**
	 * Deletes attachments.
	 *
	 * @param int    $parent_id Parent ID.
	 * @param string $parent_alias Parent alias.
	 */
	public function delete_attachments( $parent_id, $parent_alias ) {

		// Get parent type.
		$parent_type = hp\get_first_array_value( array_slice( explode( '/', current_action() ), -2, 1 ) );

		// Get parent model.
		$parent_model = hivepress()->model->get_model_name( $parent_type, $parent_alias );

		if ( empty( $parent_model ) || 'attachment' === $parent_model ) {
			return;
		}

		// Delete attachments.
		Models\Attachment::query()->filter(
			[
				'parent'       => $parent_id,
				'parent_model' => $parent_model,
			]
		)->delete();
	}

	/**
	 * Moves attachments.
	 *
	 * @param object $from Source object.
	 * @param object $to Target object.
	 * @param string $field Field name.
	 */
	public function move_attachments( $from, $to, $field ) {

		// Get attachment IDs.
		$attachment_ids = array_filter( (array) call_user_func( [ $from, 'get_' . $field . '__id' ] ) );

		if ( ! $attachment_ids ) {
			return;
		}

		// Get attachments.
		$attachments = Models\Attachment::query()->filter(
			[
				'id__in'       => $attachment_ids,
				'parent'       => $from->get_id(),
				'parent_model' => $from::_get_meta( 'name' ),
			]
		)->limit( count( $attachment_ids ) )
		->get();

		// Get parent values.
		$values = [
			'parent' => $to->get_id(),
		];

		if ( $to::_get_meta( 'name' ) !== $from::_get_meta( 'name' ) ) {
			$values['parent_model'] = $to::_get_meta( 'name' );
			$values['parent__id']   = $to::_get_meta( 'type' ) === 'post' ? $to->get_id() : null;
		}

		// Set parent.
		foreach ( $attachments as $attachment ) {
			$attachment->fill( $values )->save( array_keys( $values ) );
		}

		// Clear field.
		$from->fill(
			[
				$field => null,
			]
		)->save( [ $field ] );
	}

	/**
	 * Filters attachments.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function filter_attachments( $args ) {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			$meta_query = [
				[
					'key'     => 'hp_parent_model',
					'compare' => 'NOT EXISTS',
				],
			];

			if ( ! empty( $args['meta_query'] ) ) {
				$meta_query[] = $args['meta_query'];
			}

			$args['meta_query'] = $meta_query;
		}

		return $args;
	}

	/**
	 * Restricts REST attachment.
	 *
	 * @param object $response REST response.
	 * @param object $attachment Attachment object.
	 * @return object
	 */
	public function restrict_rest_attachment( $response, $attachment ) {
		if ( $this->is_restricted_attachment( $attachment->ID ) ) {
			return hp\rest_error( 403 );
		}

		return $response;
	}

	/**
	 * Restricts attachment page.
	 *
	 * @param array  $posts Post objects.
	 * @param object $query Query object.
	 * @return array
	 */
	public function restrict_attachment_page( $posts, $query ) {
		if ( $query->is_main_query() && $query->is_singular() && count( $posts ) === 1 ) {
			$attachment = hp\get_first_array_value( $posts );

			if ( get_post_type( $attachment ) === 'attachment' && $this->is_restricted_attachment( $attachment->ID ) ) {
				$posts = [];

				remove_action( 'template_redirect', 'redirect_canonical' );
			}
		}

		return $posts;
	}

	/**
	 * Restricts attachment embed.
	 *
	 * @param int $post_id Post ID.
	 * @return int
	 */
	public function restrict_attachment_embed( $post_id ) {
		if ( get_post_type( $post_id ) === 'attachment' && $this->is_restricted_attachment( $post_id ) ) {
			return 0;
		}

		return $post_id;
	}

	/**
	 * Restricts attachment shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return array
	 */
	public function restrict_attachment_shortcode( $atts ) {

		// Get author ID.
		$author_id = wp_doing_ajax() ? get_current_user_id() : get_post_field( 'post_author' );

		if ( ! user_can( $author_id, 'edit_others_posts' ) ) {
			add_action( 'pre_get_posts', [ $this, 'filter_shortcode_attachments' ] );
		}

		return $atts;
	}

	/**
	 * Filters shortcode attachments.
	 *
	 * @param object $query Query object.
	 */
	public function filter_shortcode_attachments( $query ) {
		if ( $query->get( 'post_type' ) === 'attachment' ) {
			remove_action( 'pre_get_posts', [ $this, 'filter_shortcode_attachments' ] );

			$query->query_vars = $this->filter_attachments( $query->query_vars );
		}
	}

	/**
	 * Generates a unique filename.
	 *
	 * @param string $filename Filename.
	 * @param string $ext Extension.
	 * @param string $dir Directory.
	 * @return string
	 */
	public function generate_filename( $filename, $ext, $dir ) {
		$name = pathinfo( $filename, PATHINFO_FILENAME );

		do {
			$filename = $name . '-' . strtolower( wp_generate_password( 6, false, false ) ) . $ext;
		} while ( file_exists( $dir . '/' . $filename ) );

		return $filename;
	}
}
