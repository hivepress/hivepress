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
 * User view block template class.
 *
 * @class User_View_Block
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
										'type'   => 'part',
										'path'   => 'user/view/block/user-name',
										'_order' => 10,
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
