<?php
/**
 * User view block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User block in view context.
 */
class User_View_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'user_container' => [
						'type'       => 'container',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-vendor', 'hp-vendor--view-block' ],
						],

						'blocks'     => [
							'user_header'  => [
								'type'       => 'container',
								'tag'        => 'header',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-vendor__header' ],
								],

								'blocks'     => [
									'user_image' => [
										'type'   => 'part',
										'path'   => 'user/view/block/user-image',
										'_order' => 10,
									],
								],
							],

							'user_content' => [
								'type'       => 'container',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-vendor__content' ],
								],

								'blocks'     => [
									'user_name'            => [
										'type'       => 'container',
										'tag'        => 'h4',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-vendor__name' ],
										],

										'blocks'     => [
											'user_name_text'           => [
												'type'   => 'part',
												'path'   => 'user/view/block/user-name',
												'_order' => 10,
											],

											'user_verified_badge' => [
												'type'   => 'part',
												'path'   => 'user/view/user-verified-badge',
												'_order' => 20,
											],
										],
									],

									'user_details_primary' => [
										'type'       => 'container',
										'optional'   => true,
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-vendor__details', 'hp-vendor__details--primary' ],
										],

										'blocks'     => [
											'user_registered_date' => [
												'type'   => 'part',
												'path'   => 'user/view/user-registered-date',
												'_order' => 10,
											],
										],
									],

									'user_attributes_secondary' => [
										'type'    => 'attributes',
										'model'   => 'user',
										'alias'   => 'vendor',
										'area'    => 'view_block_secondary',
										'columns' => 2,
										'_order'  => 30,
									],

									'user_attributes_ternary' => [
										'type'   => 'attributes',
										'model'  => 'user',
										'alias'  => 'vendor',
										'area'   => 'view_block_ternary',
										'_order' => 40,
									],
								],
							],

							'user_footer'  => [
								'type'       => 'container',
								'tag'        => 'footer',
								'_order'     => 30,

								'attributes' => [
									'class' => [ 'hp-vendor__footer' ],
								],

								'blocks'     => [
									'user_attributes_primary' => [
										'type'   => 'attributes',
										'model'  => 'user',
										'alias'  => 'vendor',
										'area'   => 'view_block_primary',
										'_order' => 10,
									],

									'user_actions_primary' => [
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
