<?php
/**
 * Abstract wide page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Wide page template class.
 *
 * @class Page_Wide
 */
abstract class Page_Wide extends Page {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_header'    => [],
					'page_footer'    => [],

					'page_container' => [
						'blocks' => [
							'page_content' => [
								'type'       => 'container',
								'tag'        => 'main',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-page__content' ],
								],

								'blocks'     => [
									'breadcrumb_menu' => [
										'type'   => 'menu',
										'menu'   => 'breadcrumb',
										'_order' => 1,
									],

									'page_title'      => [
										'type'   => 'part',
										'path'   => 'page/page-title',
										'_order' => 5,
									],

									'page_topbar'     => [
										'type'       => 'container',
										'blocks'     => [],
										'optional'   => true,
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-page__topbar' ],
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
