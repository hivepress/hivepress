<?php
/**
 * User view page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User page in view context.
 */
class User_View_Page extends Page_Sidebar_Left {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'user' ),
				'model' => 'user',
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
				'attributes' => [
					'class' => [ 'hp-vendor', 'hp-vendor--view-page' ],
				],

				'blocks'     => [
					'page_sidebar' => [
						'attributes' => [
							'class'          => [ 'hp-vendor', 'hp-vendor--view-page' ],
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'user_summary'            => [
								'type'       => 'container',
								'_label'     => esc_html__( 'Summary', 'hivepress' ),
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-vendor__summary', 'hp-widget', 'widget' ],
								],

								'blocks'     => [
									'user_image'       => [
										'type'   => 'part',
										'path'   => 'user/view/page/user-image',
										'_label' => hivepress()->translator->get_string( 'image' ),
										'_order' => 10,
									],

									'user_name'        => [
										'type'       => 'container',
										'tag'        => 'h3',
										'_label'     => hivepress()->translator->get_string( 'name' ),
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-vendor__name' ],
										],

										'blocks'     => [
											'vendor_name_text'           => [
												'type'   => 'part',
												'path'   => 'user/view/page/user-name',
												'_order' => 10,
											],
										],
									],

									'user_description' => [
										'type'   => 'part',
										'path'   => 'user/view/page/user-description',
										'_label' => hivepress()->translator->get_string( 'description' ),
										'_order' => 50,
									],
								],
							],

							'page_sidebar_widgets'      => [
								'type'   => 'widgets',
								'area'   => 'hp_view_view_sidebar',
								'_label' => hivepress()->translator->get_string( 'widgets' ),
								'_order' => 100,
							],
						],
					],

					'page_content' => [],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
