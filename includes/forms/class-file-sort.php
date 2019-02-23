<?php
/**
 * File sort form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * File sort form class.
 *
 * @class File_Sort
 */
class File_Sort extends Form {

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
				'attachment_ids' => [
					'type'     => 'file_upload',
					'multiple' => true,
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

		return empty( $this->errors );
	}
}
