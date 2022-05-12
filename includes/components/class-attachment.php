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

		parent::__construct( $args );
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
