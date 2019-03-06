<?php
/**
 * Listing update form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing update form class.
 *
 * @class Listing_Update
 */
class Listing_Update extends Model_Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'model'  => 'listing',
				'fields' => [
					'image_ids'   => [
						'label'        => esc_html__( 'Images', 'hivepress' ),
						'caption'      => esc_html__( 'Select Images', 'hivepress' ),
						'type'         => 'attachment_upload',
						'multiple'     => true,
						'max_files'    => 10,
						'file_formats' => [ 'jpg', 'jpeg', 'png' ],
						'order'        => 10,
					],

					'title'       => [
						'order' => 20,
					],

					'description' => [
						'order' => 30,
					],
				],
			]
		);

		parent::__construct( $args );
	}
}
