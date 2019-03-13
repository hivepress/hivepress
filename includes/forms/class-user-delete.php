<?php
/**
 * User delete form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User delete form class.
 *
 * @class User_Delete
 */
class User_Delete extends Model_Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'model'  => 'user',
				'method' => 'DELETE',

				'fields' => [
					'password' => [
						'order' => 10,
					],
				],

				'button' => [
					'label' => esc_html__( 'Delete Account', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
