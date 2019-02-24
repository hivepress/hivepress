<?php
/**
 * Listing update form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing update form class.
 *
 * @class Listing_Update
 */
class Listing_Update extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		parent::__construct( $args );

		// todo.
		$this->set_action(get_rest_url(null, '/hivepress/v1/listings/1'));

		// Set fields.
		$this->set_fields(
			[
				'title'       => [
					'label'      => esc_html__( 'Title', 'hivepress' ),
					'type'       => 'text',
					'max_length' => 128,
					'required'   => true,
					'order'      => 10,
				],

				'description' => [
					'label'      => esc_html__( 'Description', 'hivepress' ),
					'type'       => 'textarea',
					'max_length' => 10240,
					'required'   => true,
					'order'      => 20,
				],

				'images'      => [
					'label'        => esc_html__( 'Images', 'hivepress' ),
					'caption'      => esc_html__( 'Select Images', 'hivepress' ),
					'type'         => 'file_upload',
					'multiple'     => true,
					'file_formats' => [ 'jpg', 'jpeg', 'png' ],
					'max_files'    => 10,
					'order'        => 30,
				],
			]
		);
	}
}
