<?php
/**
 * Abstract narrow page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Narrow page template class.
 *
 * @class Page_Narrow
 */
abstract class Page_Narrow extends Page {

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
									'page_content' => [
										'type'       => 'container',
										'tag'        => 'main',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-page__content', 'hp-col-sm-4', 'hp-col-sm-offset-4', 'hp-col-xs-12' ],
										],

										'blocks'     => [
											'breadcrumb_menu' => [
												'type'   => 'menu',
												'menu'   => 'breadcrumb',
												'_order' => 1,
											],

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
