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
		add_action( 'hivepress/v2/models/user/update', [ $this, 'update_vendor' ], 100, 2 );

		// Add vendor fields.
		add_filter( 'hivepress/v1/forms/user_update', [ $this, 'add_vendor_fields' ], 100, 2 );

		// Update vendor fields.
		add_filter( 'hivepress/v1/forms/user_update/errors', [ $this, 'update_vendor_fields' ], 100, 2 );

		// Alter post types.
		add_filter( 'hivepress/v1/post_types', [ $this, 'alter_post_types' ] );

		if ( ! is_admin() ) {

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Updates vendor.
	 *
	 * @param int    $user_id User ID.
	 * @param object $user User object.
	 */
	public function update_vendor( $user_id, $user ) {

		// Get vendor.
		$vendor = Models\Vendor::query()->filter(
			[
				'status' => [ 'auto-draft', 'publish' ],
				'user'   => $user_id,
			]
		)->get_first();

		if ( ! $vendor ) {
			return;
		}

		// Get slug.
		$slug = $user->get_username();

		// Get name.
		$name = $user->get_display_name();

		// Get name attribute.
		$name_attribute_id = get_option( 'hp_vendor_display_name' );

		if ( $name_attribute_id ) {
			$name_attribute = hivepress()->attribute->get_attribute_name( get_post_field( 'post_name', $name_attribute_id ) );

			if ( $name_attribute ) {

				// Get attribute value.
				$name_attribute_value = hp\get_array_value( $vendor->serialize(), $name_attribute );

				// Set name and slug.
				if ( $name_attribute_value ) {
					$name = $name_attribute_value;
					$slug = $name;
				}
			}
		}

		// Update vendor.
		$vendor->fill(
			[
				'name'        => $name,
				'description' => $user->get_description(),
				'slug'        => $slug,
				'image'       => $user->get_image__id(),
			]
		)->save(
			[
				'name',
				'description',
				'slug',
				'image',
			]
		);
	}

	/**
	 * Adds vendor fields.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function add_vendor_fields( $form_args, $form ) {

		// Get user.
		$user = $form->get_model();

		if ( $user->get_id() ) {

			// Get vendor.
			$vendor = Models\Vendor::query()->filter(
				[
					'status' => [ 'auto-draft', 'publish' ],
					'user'   => $user->get_id(),
				]
			)->get_first();

			if ( $vendor && ( $vendor->get_status() === 'publish' || $form::get_meta( 'name' ) === 'user_update_profile' ) ) {

				// Get form.
				$vendor_form = ( new Forms\Vendor_Update( [ 'model' => $vendor ] ) );

				// Add fields.
				foreach ( $vendor_form->get_fields() as $field_name => $field ) {
					if ( ! isset( $form_args['fields'][ $field_name ] ) ) {
						$form_args['fields'][ $field_name ] = array_merge(
							$field->get_args(),
							[
								'default'   => $field->get_value(),
								'_separate' => true,
							]
						);
					}
				}
			}
		}

		return $form_args;
	}

	/**
	 * Updates vendor fields.
	 *
	 * @param array  $errors Form errors.
	 * @param object $form Form object.
	 * @return array
	 */
	public function update_vendor_fields( $errors, $form ) {
		if ( empty( $errors ) ) {

			// Get user.
			$user = $form->get_model();

			if ( $user->get_id() ) {

				// Get vendor.
				$vendor = Models\Vendor::query()->filter(
					[
						'status' => [ 'auto-draft', 'publish' ],
						'user'   => $user->get_id(),
					]
				)->get_first();

				if ( $vendor && ( $vendor->get_status() === 'publish' || $form::get_meta( 'name' ) === 'user_update_profile' ) ) {

					// Get fields.
					$vendor_fields = array_keys( ( new Forms\Vendor_Update( [ 'model' => $vendor ] ) )->get_fields() );

					// Get values.
					$vendor_values = array_map(
						function( $field ) {
							return $field->get_value();
						},
						array_filter(
							$form->get_fields(),
							function( $field ) use ( $vendor_fields ) {
								return in_array( $field->get_name(), $vendor_fields, true ) && hp\get_array_value( $field->get_args(), '_separate' );
							}
						)
					);

					// Update vendor.
					$vendor->fill( $vendor_values )->save();
				}
			}
		}

		return $errors;
	}

	/**
	 * Alters post types.
	 *
	 * @param array $post_types Post type arguments.
	 * @return array
	 */
	public function alter_post_types( $post_types ) {
		$post_types['vendor']['public'] = (bool) get_option( 'hp_vendor_enable_display' );

		return $post_types;
	}

	/**
	 * Alters listing view page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_listing_view_page( $template ) {

		// Get vendor.
		$vendor = hivepress()->request->get_context( 'vendor' );

		if ( ! get_option( 'hp_vendor_enable_display' ) || ! $vendor || $vendor->get_status() !== 'publish' ) {

			// Hide vendor.
			$template = hp\merge_trees(
				$template,
				[
					'blocks' => [
						'listing_vendor' => [
							'type' => 'content',
						],
					],
				]
			);
		}

		return $template;
	}
}
