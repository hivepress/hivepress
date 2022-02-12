<?php
/**
 * Listing update form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Updates listing.
 */
class Listing_Update extends Model_Form {

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
				'message' => hivepress()->translator->get_string( 'changes_have_been_saved' ),

				'fields'  => [
					'images'      => [
						'_order' => 10,
					],

					'title'       => [
						'_order' => 20,
					],

					'description' => [
						'html'   => false,
						'_order' => 200,
					],
				],

				'button'  => [
					'label' => hivepress()->translator->get_string( 'save_changes' ),
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

		// Set action.
		if ( $this->model->get_id() ) {
			$this->action = hivepress()->router->get_url(
				'listing_update_action',
				[
					'listing_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
