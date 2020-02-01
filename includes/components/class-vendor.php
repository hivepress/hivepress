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
		add_action( 'hivepress/v1/models/user/update_image', [ $this, 'update_vendor' ] );
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
		$vendor = Models\Vendor::query()->filter( [ 'user' => $user_id ] )->get_first();

		if ( empty( $vendor ) ) {
			return;
		}

		// Get user.
		$user = Models\User::query()->get_by_id( $user_id );

		// Update vendor.
		$vendor->fill(
			[
				'name'        => $user->get_display_name(),
				'description' => $user->get_description(),
				'slug'        => $user->get_username(),
				'image'       => $user->get_image__id(),
			]
		)->save();
	}
}
