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
	 * Template blocks.
	 *
	 * @var array
	 */
	protected static $blocks = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Template arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'vendor_container' => [
						'type'       => 'container',
						'tag'        => 'article',
						'order'      => 10,

						'attributes' => [
							'class' => [ 'hp-vendor', 'hp-vendor--view-block' ],
						],

						'blocks'     => [
							'vendor_header'  => [
								'type'       => 'container',
								'tag'        => 'header',
								'order'      => 10,

								'attributes' => [
									'class' => [ 'hp-vendor__header' ],
								],

								'blocks'     => [
									'vendor_image' => [
										'type'     => 'element',
										'filepath' => 'vendor/view/block/vendor-image',
										'order'    => 10,
									],
								],
							],

							'vendor_content' => [
								'type'       => 'container',
								'order'      => 20,

								'attributes' => [
									'class' => [ 'hp-vendor__content' ],
								],

								'blocks'     => [
									'vendor_name' => [
										'type'     => 'element',
										'filepath' => 'vendor/view/block/vendor-name',
										'order'    => 10,
									],

									'vendor_details_primary' => [
										'type'       => 'container',
										'order'      => 20,

										'attributes' => [
											'class' => [ 'hp-vendor__details', 'hp-vendor__details--primary' ],
										],

										'blocks'     => [
											'vendor_date' => [
												'type'     => 'element',
												'filepath' => 'vendor/view/vendor-date',
												'order'    => 10,
											],
										],
									],
								],
							],

							'vendor_footer'  => [
								'type'       => 'container',
								'tag'        => 'footer',
								'order'      => 30,

								'attributes' => [
									'class' => [ 'hp-vendor__footer' ],
								],

								'blocks'     => [
									'vendor_actions_primary'    => [
										'type'       => 'container',
										'order'      => 10,

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

		parent::init( $args );
	}
}
