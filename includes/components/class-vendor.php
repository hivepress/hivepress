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

		// Create listing.
		add_action( 'hivepress/v1/models/listing/create', [ $this, 'create_listing' ], 10, 2 );

		// Update user.
		add_action( 'hivepress/v2/models/user/update', [ $this, 'update_user' ], 100, 2 );

		// Update vendor.
		add_action( 'hivepress/v1/models/vendor/update', [ $this, 'update_vendor' ], 10, 2 );
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
	 * Updates listings.
	 *
	 * @param object $vendor Vendor object.
	 * @param array  $listings Listing objects.
	 */
	protected function update_listings( $vendor, $listings ) {

		// Get attributes.
		$attributes = array_filter(
			hivepress()->attribute->get_attributes( 'listing' ),
			function( $attribute ) {
				return hp\get_array_value( $attribute, 'synced' );
			}
		);

		if ( ! $attributes ) {
			return;
		}

		// Get values.
		$values = array_intersect_key( $vendor->serialize(), $attributes );

		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $attribute['edit_field']['options'] ) || isset( $attribute['edit_field']['_external'] ) ) {
				continue;
			}

			// Get field.
			$attribute_field = hp\get_array_value( $vendor->_get_fields(), $attribute_name );

			if ( ! $attribute_field || ! $attribute_field->get_value() ) {
				continue;
			}

			// Get term names.
			$term_names = get_terms(
				[
					'taxonomy'   => $attribute_field->get_arg( 'option_args' )['taxonomy'],
					'include'    => (array) $attribute_field->get_value(),
					'fields'     => 'names',
					'hide_empty' => false,
				]
			);

			if ( ! $term_names ) {
				continue;
			}

			// Get term IDs.
			$term_ids = get_terms(
				[
					'taxonomy'   => $attribute['edit_field']['option_args']['taxonomy'],
					'name'       => $term_names,
					'fields'     => 'ids',
					'hide_empty' => false,
				]
			);

			if ( ! $term_ids ) {
				continue;
			}

			// Set value.
			$values[ $attribute_name ] = $term_ids;
		}

		// Update listings.
		foreach ( $listings as $listing ) {
			if ( array_intersect_key( $listing->serialize(), $attributes ) !== $values ) {
				$listing->fill( $values )->save( array_keys( $values ) );
			}
		}
	}

	/**
	 * Creates listing.
	 *
	 * @param int    $listing_id Listing ID.
	 * @param object $listing Listing object.
	 */
	public function create_listing( $listing_id, $listing ) {

		// Get vendor.
		$vendor = $listing->get_vendor();

		if ( ! $vendor ) {
			return;
		}

		// Update listing.
		$this->update_listings( $vendor, [ $listing ] );
	}

	/**
	 * Updates vendor.
	 *
	 * @param int    $vendor_id Vendor ID.
	 * @param object $vendor Vendor object.
	 */
	public function update_vendor( $vendor_id, $vendor ) {

		// Remove action.
		remove_action( 'hivepress/v1/models/vendor/update', [ $this, 'update_vendor' ] );

		// Get listings.
		$listings = Models\Listing::query()->filter(
			[
				'status__in' => [ 'auto-draft', 'draft', 'pending', 'publish' ],
				'user'       => $vendor->get_user__id(),
			]
		)->get()
		->serialize();

		// Update listings.
		$this->update_listings( $vendor, $listings );
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
			if ( $user_object && ! user_can( $user_object, 'edit_posts' ) ) {
				$user_object->set_role( 'contributor' );
			}
		}
	}

	/**
	 * Updates user.
	 *
	 * @param int    $user_id User ID.
	 * @param object $user User object.
	 */
	public function update_user( $user_id, $user ) {

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

		// @todo remove temporary fix after adding context to the editor.
		if ( hp\is_rest() ) {
			return $template;
		}

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
