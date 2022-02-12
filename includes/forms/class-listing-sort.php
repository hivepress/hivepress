<?php
/**
 * Listing sort form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Sorts listings.
 */
class Listing_Sort extends Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'listing',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'action' => home_url(),
				'method' => 'GET',
				'button' => null,

				'fields' => [
					'_sort'     => [
						'label'       => hivepress()->translator->get_string( 'sort_by' ),
						'type'        => 'select',
						'placeholder' => null,
						'required'    => true,
						'_order'      => 10,

						'options'     => [
							''      => hivepress()->translator->get_string( 'date' ),
							'title' => hivepress()->translator->get_string( 'title' ),
						],

						'attributes'  => [
							'data-style' => 'inline',
						],
					],

					'_category' => [
						'type' => 'hidden',
					],

					's'         => [
						'type' => 'hidden',
					],

					'post_type' => [
						'type'    => 'hidden',
						'default' => 'hp_listing',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function boot() {

		// Set attributes.
		$this->attributes['data-autosubmit'] = 'true';

		parent::boot();
	}
}
