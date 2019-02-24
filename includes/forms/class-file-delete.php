<?php
/**
 * File delete form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * File delete form class.
 *
 * @class File_Delete
 */
class File_Delete extends Button {

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
				'attachment_id' => [
					'type'     => 'file_upload',
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

			// Delete attachment.
			wp_delete_attachment( $this->get_value( 'attachment_id' ), true );
		}

		return empty( $this->errors );
	}
}
