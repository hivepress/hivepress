<?php
/**
 * Vendor model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor.
 */
class Vendor extends Entity {

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'name'            => [
						'label'      => hivepress()->translator->get_string( 'name' ),
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'post_title',
					],

					'description'     => [
						'required' => false,
					],

					'status'          => [
						'max_length' => 128,
					],

					'verified'        => [
						'type'      => 'checkbox',
						'_external' => true,
					],

					'registered_date' => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_date',
					],

					'categories'      => [
						'option_args' => [ 'taxonomy' => 'hp_vendor_category' ],
						'_model'      => 'vendor_category',
					],

					'image'           => [
						'type'      => 'id',
						'_alias'    => '_thumbnail_id',
						'_model'    => 'attachment',
						'_external' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
