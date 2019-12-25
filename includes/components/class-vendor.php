<?php
/**
 * Vendor component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor component class.
 *
 * @class Vendor
 */
final class Vendor extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Update vendor.
		add_action( 'hivepress/v1/models/user/update_image_id', [ $this, 'update_vendor' ] );
		add_action( 'hivepress/v1/models/user/update_first_name', [ $this, 'update_vendor' ] );
		add_action( 'hivepress/v1/models/user/update_description', [ $this, 'update_vendor' ] );

		parent::__construct( $args );
	}

	/**
	 * Updates vendor.
	 *
	 * @param int $user_id User ID.
	 */
	public function update_vendor( $user_id ) {

		// Get vendor.
		$vendor = Models\Vendor::filter(
			[
				'status'  => 'publish',
				'user_id' => $user_id,
			]
		)->get_first();

		if ( is_null( $vendor ) ) {
			return;
		}

		// Get user.
		$user = Models\User::get_by_id( $user_id );

		// Update vendor.
		$vendor->fill(
			[
				'image_id'    => $user->get_image_id(),
				'name'        => $user->get_first_name(),
				'description' => $user->get_description(),
			]
		)->save();
	}
}
