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
									'user_image'           => [
										'type'   => 'part',
										'path'   => 'user/view/page/user-image',
										'_label' => hivepress()->translator->get_string( 'image' ),
										'_order' => 10,
									],

									'user_name'            => [
										'type'       => 'container',
										'tag'        => 'h3',
										'_label'     => hivepress()->translator->get_string( 'name' ),
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-vendor__name' ],
										],

										'blocks'     => [
											'user_name_text' => [
												'type'   => 'part',
												'path'   => 'user/view/page/user-name',
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
										'_label'     => hivepress()->translator->get_string( 'details' ),
										'_order'     => 30,

										'attributes' => [
											'class' => [ 'hp-vendor__details', 'hp-vendor__details--primary' ],
										],

										'blocks'     => [
											'user_registered_date' => [
												'type'   => 'part',
												'path'   => 'user/view/user-registered-date',
												'_label' => hivepress()->translator->get_string( 'date' ),
												'_order' => 10,
											],
										],
									],

									'user_attributes_secondary' => [
										'type'      => 'attributes',
										'model'     => 'user',
										'alias'     => 'vendor',
										'area'      => 'view_page_secondary',
										'columns'   => 2,
										'_label'    => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'secondary_plural' ) . ')',
										'_settings' => [ 'columns' ],
										'_order'    => 40,
									],

									'user_attributes_ternary' => [
										'type'      => 'attributes',
										'model'     => 'user',
										'alias'     => 'vendor',
										'area'      => 'view_page_ternary',
										'_label'    => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'ternary_plural' ) . ')',
										'_settings' => [ 'columns' ],
										'_order'    => 50,
									],

									'user_description'     => [
										'type'   => 'part',
										'path'   => 'user/view/page/user-description',
										'_label' => hivepress()->translator->get_string( 'description' ),
										'_order' => 60,
									],
								],
							],

							'user_attributes_primary' => [
								'type'      => 'attributes',
								'model'     => 'user',
								'alias'     => 'vendor',
								'area'      => 'view_page_primary',
								'_label'    => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'primary_plural' ) . ')',
								'_settings' => [ 'columns' ],
								'_order'    => 20,
							],

							'user_actions_primary'    => [
								'type'       => 'container',
								'blocks'     => [],
								'_label'     => hivepress()->translator->get_string( 'actions' ),
								'_order'     => 30,

								'attributes' => [
									'class' => [ 'hp-vendor__actions', 'hp-vendor__actions--primary', 'hp-widget', 'widget' ],
								],
							],

							'page_sidebar_widgets'    => [
								'type'   => 'widgets',
								'area'   => 'hp_user_view_sidebar',
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
