<?php
/**
 * Listing delete form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing delete form class.
 *
 * @class Listing_Delete
 */
class Listing_Delete extends Model_Form {

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'model' => 'listing',
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'description' => hivepress()->translator->get_string( 'confirm_listing_deletion' ),
				'method'      => 'DELETE',
				'redirect'    => true,

				'button'      => [
					'label' => hivepress()->translator->get_string( 'delete_listing' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function bootstrap() {

		// Set action.
		if ( $this->object->get_id() ) {
			$this->action = hivepress()->router->get_url(
				'listing_delete_action',
				[
					'listing_id' => $this->object->get_id(),
				]
			);
		}

		parent::bootstrap();
	}
}
