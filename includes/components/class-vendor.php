<?php
/**
 * Vendor component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Forms;

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
		add_action( 'hivepress/v1/models/user/update', [ $this, 'update_vendor' ], 100 );

		// todo.
		add_filter( 'hivepress/v1/forms/user_update', [ $this, 'todo' ], 100, 2 );

		parent::__construct( $args );
	}

	// todo.
	public function todo( $form_args, $form ) {

		// Get vendor.
		$vendor = Models\Vendor::query()->filter( [ 'user' => $form->get_model()->get_id() ] )->get_first();

		if ( $vendor ) {
			$vendor_form = ( new Forms\Vendor_Update(
				[
					'model' => $vendor,
				]
			) );

			$form_args['fields'] = array_merge(
				array_map(
					function( $field ) {
						return array_merge(
							$field->get_args(),
							[
								// todo doesn't work.
								'default' => $field->get_value(),
							]
						);
					},
					$vendor_form->get_fields()
				),
				$form_args['fields']
			);
		}

		return $form_args;
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
