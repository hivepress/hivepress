<?php
/**
 * Listing model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing model class.
 *
 * @class Listing
 */
class Listing extends Post {

	/**
	 * Class initializer.
	 *
	 * @param array $args Model arguments.
	 */
	public static function init( $args = [] ) {
		$args = array_replace_recursive(
			$args,
			[
				'fields'  => [
					'title'       => [
						'label'      => esc_html__( 'Title', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 128,
						'required'   => true,
					],

					'description' => [
						'label'      => esc_html__( 'Description', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'required'   => true,
					],

					// todo move to form.
					'image_ids'   => [
						'label'        => esc_html__( 'Images', 'hivepress' ),
						'caption'      => esc_html__( 'Select Images', 'hivepress' ),
						'type'         => 'attachment_upload',
						'multiple'     => true,
						'file_formats' => [ 'jpg', 'jpeg', 'png' ],
						'max_files'    => 10,
					],
				],

				'aliases' => [
					'post_title'   => 'title',
					'post_content' => 'description',
				],
			]
		);

		parent::init( $args );
	}
}
