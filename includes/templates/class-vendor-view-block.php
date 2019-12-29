<?php
/**
 * Vendor view block template.
 *
 * @template vendor_view_block
 * @description Vendor block in view context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor view block template class.
 *
 * @class Vendor_View_Block
 */
class Vendor_View_Block extends Template {

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
										'type'     => 'element',
										'filepath' => 'vendor/view/block/vendor-image',
										'_order'   => 10,
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
										'type'     => 'element',
										'filepath' => 'vendor/view/block/vendor-name',
										'_order'   => 10,
									],

									'vendor_details_primary' => [
										'type'       => 'container',
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-vendor__details', 'hp-vendor__details--primary' ],
										],

										'blocks'     => [
											'vendor_date' => [
												'type'     => 'element',
												'filepath' => 'vendor/view/vendor-date',
												'_order'   => 10,
											],
										],
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
									'vendor_actions_primary'    => [
										'type'       => 'container',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-vendor__actions', 'hp-vendor__actions--primary' ],
										],

										'blocks'     => [],
									],
								],
							],
						],
					],
				],
			],
			$args,
			'blocks'
		);

		parent::__construct( $args );
	}
}
