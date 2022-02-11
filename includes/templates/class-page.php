<?php
/**
 * Abstract page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Base page.
 */
abstract class Page extends Template {

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
						'type'   => 'page',
						'_order' => 10,

						'blocks' => [
							'page_header' => [
								'type'       => 'container',
								'tag'        => 'header',
								'optional'   => true,
								'blocks'     => [],
								'_order'     => 5,

								'attributes' => [
									'class' => [ 'hp-page__header' ],
								],
							],

							'page_footer' => [
								'type'       => 'container',
								'tag'        => 'footer',
								'optional'   => true,
								'blocks'     => [],
								'_order'     => 1000,

								'attributes' => [
									'class' => [ 'hp-page__footer' ],
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
