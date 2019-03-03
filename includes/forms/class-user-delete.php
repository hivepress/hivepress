<?php
/**
 * User delete form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

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
		$args = array_replace_recursive(
			$args,
			[
				'model'  => 'user',
				'method' => 'DELETE',
				'fields' => [
					'password' => [
						'order' => 10,
					],
				],
			]
		);

		parent::__construct( $args );
	}
}
