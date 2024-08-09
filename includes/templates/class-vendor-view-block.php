<?php
/**
 * Vendor view block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor block in view context.
 */
class Vendor_View_Block extends Template {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'vendor',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'vendor_container' => [
						'type'       => 'container',
						'tag'        => 'article',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-vendor', 'hp-vendor--view-block' ],
						],

						'blocks'     => [
							'vendor_header'  => [
								'type'       => 'container',
								'tag'        => 'header',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-vendor__header' ],
								],

								'blocks'     => [
									'vendor_image' => [
										'type'   => 'part',
										'path'   => 'vendor/view/block/vendor-image',
										'_order' => 10,
									],
								],
							],

							'vendor_content' => [
								'type'       => 'container',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-vendor__content' ],
								],

								'blocks'     => [
									'vendor_name' => [
										'type'       => 'container',
										'tag'        => 'h4',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-vendor__name' ],
										],

										'blocks'     => [
											'vendor_name_text'           => [
												'type'   => 'part',
												'path'   => 'vendor/view/block/vendor-name',
												'_order' => 10,
											],

											'vendor_verified_badge' => [
												'type'   => 'part',
												'path'   => 'vendor/view/vendor-verified-badge',
												'_order' => 20,
											],
										],
									],

									'vendor_details_primary' => [
										'type'       => 'container',
										'optional'   => true,
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-vendor__details', 'hp-vendor__details--primary' ],
										],

										'blocks'     => [
											'vendor_registered_date' => [
												'type'   => 'part',
												'path'   => 'vendor/view/vendor-registered-date',
												'_order' => 10,
											],
										],
									],

									'vendor_attributes_secondary' => [
										'type'    => 'attributes',
										'model'   => 'vendor',
										'area'    => 'view_block_secondary',
										'columns' => 2,
										'_order'  => 30,
									],

									'vendor_attributes_ternary' => [
										'type'   => 'attributes',
										'model'  => 'vendor',
										'area'   => 'view_block_ternary',
										'_order' => 40,
									],
								],
							],

							'vendor_footer'  => [
								'type'       => 'container',
								'tag'        => 'footer',
								'_order'     => 30,

								'attributes' => [
									'class' => [ 'hp-vendor__footer' ],
								],

								'blocks'     => [
									'vendor_attributes_primary' => [
										'type'   => 'attributes',
										'model'  => 'vendor',
										'area'   => 'view_block_primary',
										'_order' => 10,
									],

									'vendor_actions_primary'    => [
										'type'       => 'container',
										'blocks'     => [],
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-vendor__actions', 'hp-vendor__actions--primary' ],
										],
									],
								],
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
