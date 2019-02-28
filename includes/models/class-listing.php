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
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = array_replace_recursive(
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
				],
				'aliases' => [
					'title'       => 'post_title',
					'description' => 'post_content',
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
