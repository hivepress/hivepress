<?php
/**
 * Abstract left sidebar page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Left sidebar page template class.
 *
 * @class Page_Sidebar_Left
 */
abstract class Page_Sidebar_Left extends Page {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_container' => [
						'blocks' => [
							'page_columns' => [
								'type'       => 'container',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-row' ],
								],

								'blocks'     => [
									'page_sidebar' => [
										'type'       => 'container',
										'tag'        => 'aside',
										'blocks'     => [],
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
										],
									],

									'page_content' => [
										'type'       => 'container',
										'tag'        => 'main',
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
										],

										'blocks'     => [
											'page_title' => [
												'type'   => 'part',
												'path'   => 'page/page-title',
												'_order' => 5,
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
