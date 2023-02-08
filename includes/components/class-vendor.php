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
 * Handles vendors.
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

		// Update vendor status.
		add_action( 'hivepress/v1/models/vendor/update_status', [ $this, 'update_vendor_status' ], 10, 4 );

		// Add vendor fields.
		add_filter( 'hivepress/v1/forms/user_update', [ $this, 'add_vendor_fields' ], 100, 2 );

		// Update vendor fields.
		add_filter( 'hivepress/v1/forms/user_update/errors', [ $this, 'update_vendor_fields' ], 100, 2 );

		// Alter post types.
		add_filter( 'hivepress/v1/post_types', [ $this, 'alter_post_types' ] );

		if ( ! is_admin() ) {

			// Set request context.
			add_filter( 'hivepress/v1/components/request/context', [ $this, 'set_request_context' ] );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_page', [ $this, 'alter_listing_view_page' ] );
			add_filter( 'hivepress/v1/templates/user_edit_settings_page/blocks', [ $this, 'alter_user_edit_settings_page' ], 100, 2 );
		}

		parent::__construct( $args );
	}

	/**
	 * Updates vendor status.
	 *
	 * @param int    $vendor_id Vendor ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 * @param object $vendor Vendor object.
	 */
	public function update_vendor_status( $vendor_id, $new_status, $old_status, $vendor ) {

		// Check user.
		if ( ! $vendor->get_user__id() ) {
			return;
		}

		if ( 'publish' === $new_status ) {

			// Get user object.
			$user_object = get_userdata( $vendor->get_user__id() );

			// Update user role.
			if ( $user_object && array_intersect( (array) $user_object->roles, [ 'subscriber', 'customer' ] ) ) {
				$user_object->set_role( 'contributor' );
			}
		}
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
				'status' => [ 'auto-draft', 'draft', 'publish' ],
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
					'status' => [ 'auto-draft', 'draft', 'publish' ],
					'user'   => $user->get_id(),
				]
			)->get_first();

			if ( $vendor && ( $vendor->get_status() === 'publish' || $form::get_meta( 'name' ) === 'user_update_profile' ) ) {

				// Get form.
				$vendor_form = hp\create_class_instance( '\HivePress\Forms\\' . ( 'user_update_profile' === $form::get_meta( 'name' ) ? 'vendor_submit' : 'vendor_update' ), [ [ 'model' => $vendor ] ] );

				// Add fields.
				foreach ( $vendor_form->get_fields() as $field_name => $field ) {
					if ( ! isset( $form_args['fields'][ $field_name ] ) ) {
						$field_args = $field->get_args();

						if ( 'attachment_upload' === $field_args['type'] ) {
							$field_args['attributes']['data-model'] = 'vendor';
							$field_args['attributes']['data-id']    = $vendor->get_id();
						}

						$form_args['fields'][ $field_name ] = array_merge(
							$field_args,
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
						'status' => [ 'auto-draft', 'draft', 'publish' ],
						'user'   => $user->get_id(),
					]
				)->get_first();

				if ( $vendor && ( $vendor->get_status() === 'publish' || $form::get_meta( 'name' ) === 'user_update_profile' ) ) {

					// Get fields.
					$vendor_fields = array_keys( ( new Forms\Vendor_Update( [ 'model' => $vendor ] ) )->get_fields() );

					if ( $vendor_fields ) {

						// Get values.
						$vendor_values = array_map(
							function( $field ) {
								return $field->get_value();
							},
							array_filter(
								$form->get_fields(),
								function( $field ) use ( $vendor_fields ) {
									return ! $field->is_disabled() && in_array( $field->get_name(), $vendor_fields, true ) && hp\get_array_value( $field->get_args(), '_separate' );
								}
							)
						);

						// Update vendor.
						if ( ! $vendor->fill( $vendor_values )->save( $vendor_fields ) ) {
							$errors = array_merge( $errors, $vendor->_get_errors() );
						}
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Sets request context.
	 *
	 * @param array $context Request context.
	 * @return array
	 */
	public function set_request_context( $context ) {

		// Check permissions.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return $context;
		}

		// Get cached vendor ID.
		$vendor_id = hivepress()->cache->get_user_cache( get_current_user_id(), 'vendor_id', 'models/vendor' );

		if ( is_null( $vendor_id ) ) {

			// Get vendor ID.
			$vendor_id = (int) Models\Vendor::query()->filter(
				[
					'status' => 'publish',
					'user'   => get_current_user_id(),
				]
			)->get_first_id();

			// Cache vendor ID.
			hivepress()->cache->set_user_cache( get_current_user_id(), 'vendor_id', 'models/vendor', $vendor_id );
		}

		// Set request context.
		$context['vendor_id'] = $vendor_id;

		return $context;
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
			hivepress()->template->fetch_block( $template, 'listing_vendor' );
		}

		return $template;
	}

	/**
	 * Alters user edit settings page.
	 *
	 * @param array  $blocks Template arguments.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_user_edit_settings_page( $blocks, $template ) {

		// Get vendor ID.
		$vendor_id = hivepress()->request->get_context( 'vendor_id' );

		if ( ! $vendor_id ) {
			return $blocks;
		}

		// Get vendor.
		$vendor = Models\Vendor::query()->get_by_id( $vendor_id );

		if ( ! $vendor || $vendor->get_status() !== 'publish' ) {
			return $blocks;
		}

		// Set template context.
		$template->set_context( 'vendor', $vendor );

		return hivepress()->template->merge_blocks(
			$blocks,
			[
				'user_update_form' => [
					'footer' => [
						'form_actions' => [
							'blocks' => [
								'vendor_view_link' => [
									'type'   => 'part',
									'path'   => 'vendor/edit/page/vendor-view-link',
									'_order' => 5,
								],
							],
						],
					],
				],
			]
		);
	}
}
