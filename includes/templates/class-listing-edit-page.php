<?php
/**
 * Listing edit page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing edit page template class.
 *
 * @class Listing_Edit_Page
 */
class Listing_Edit_Page extends Account_Page {

	/**
	 * Template name.
	 *
	 * @var string
	 */
	protected static $name;

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
					'page_content' => [
						'blocks' => [
							'listing_delete_modal' => [
								'type'    => 'modal',
								'caption' => esc_html__( 'Delete Listing', 'hivepress' ),
								'order'   => 5,

								'blocks'  => [
									'listing_delete_form' => [
										'type'       => 'form',
										'form'       => 'listing_delete',
										'order'      => 10,

										'attributes' => [
											'class' => [ 'hp-form--narrow' ],
										],
									],
								],
							],

							'listing_update_form'  => [
								'type'   => 'form',
								'form'   => 'listing_update',
								'order'  => 10,

								'footer' => [
									'form_actions' => [
										'type'       => 'container',
										'order'      => 10,

										'attributes' => [
											'class' => [ 'hp-form__actions' ],
										],

										'blocks'     => [
											'listing_delete_link' => [
												'type'     => 'element',
												'filepath' => 'listing/edit/page/delete-link',
												'order'    => 10,
											],
										],
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
