<?php
/**
 * File upload form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * File upload form class.
 *
 * @class File_Upload
 */
class File_Upload extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		parent::__construct( $args );

		// Set fields.
		$this->set_fields(
			[
				'form_name'  => [
					'type'     => 'hidden',
					'required' => true,
				],

				'field_name' => [
					'type'     => 'hidden',
					'required' => true,
				],
			]
		);
	}

	/**
	 * Submits form.
	 *
	 * @return bool
	 */
	public function submit() {
		parent::submit();

		if ( is_user_logged_in() ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			// Get form class.
			$form_class = '\HivePress\Forms\\' . $this->get_value( 'form_name' );

			if ( class_exists( $form_class ) ) {

				// Create form.
				$form = new $form_class();

				// Get field.
				$field = hp_get_array_value( $form->get_fields(), $this->get_value( 'field_name' ) );

				if ( ! is_null( $field ) && $field->get_type() === 'file_upload' ) {
					if ( $field->validate() ) {

						// Upload file.
						$attachment_id = media_handle_upload( $this->get_value( 'field_name' ) );

						if ( ! is_wp_error( $attachment_id ) ) {
							$this->set_response( $field->render_file( $attachment_id ) );
						} else {
							$this->errors[] = esc_html__( 'Error uploading file.', 'hivepress' );
						}
					} else {
						$this->errors = array_merge( $this->errors, $field->get_errors() );
					}
				}
			}
		}

		return empty( $this->errors );
	}
}
