<?php
/**
 * Listing model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing.
 *
 * @OA\Schema(description="")
 */
class Listing extends Post {

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [

					/**
					* @OA\Property(
					*   property="title",
					*   type="string",
					*   description="Listing title.",
					* )
					 */
					'title'            => [
						'label'      => hivepress()->translator->get_string( 'title' ),
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'post_title',
					],

					'slug'             => [
						'type'       => 'text',
						'max_length' => 256,
						'_alias'     => 'post_name',
					],

					/**
					* @OA\Property(
					*   property="description",
					*   type="string",
					*   description="Listing description.",
					* )
					 */
					'description'      => [
						'label'      => hivepress()->translator->get_string( 'description' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'html'       => true,
						'required'   => true,
						'_alias'     => 'post_content',
					],

					'status'           => [
						'type'    => 'select',
						'_alias'  => 'post_status',

						'options' => [
							'publish'    => '',
							'future'     => '',
							'draft'      => esc_html_x( 'Hidden', 'listing', 'hivepress' ),
							'pending'    => esc_html_x( 'Pending', 'listing', 'hivepress' ),
							'private'    => '',
							'trash'      => '',
							'auto-draft' => '',
							'inherit'    => '',
						],
					],

					'drafted'          => [
						'type'      => 'checkbox',
						'_external' => true,
					],

					'featured'         => [
						'label'     => esc_html_x( 'Featured', 'listing', 'hivepress' ),
						'type'      => 'checkbox',
						'_external' => true,
					],

					'verified'         => [
						'label'     => esc_html_x( 'Verified', 'listing', 'hivepress' ),
						'type'      => 'checkbox',
						'_external' => true,
					],

					'created_date'     => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_date',
					],

					'created_date_gmt' => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_date_gmt',
					],

					'modified_date'    => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_modified',
					],

					'expired_time'     => [
						'type'      => 'number',
						'min_value' => 0,
						'_external' => true,
					],

					'featured_time'    => [
						'type'      => 'number',
						'min_value' => 0,
						'_external' => true,
					],

					'user'             => [
						'type'     => 'id',
						'required' => true,
						'_alias'   => 'post_author',
						'_model'   => 'user',
					],

					'vendor'           => [
						'type'   => 'id',
						'_alias' => 'post_parent',
						'_model' => 'vendor',
					],

					'categories'       => [
						'label'       => hivepress()->translator->get_string( 'category' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'multiple'    => true,
						'required'    => true,
						'_indexable'  => true,
						'_model'      => 'listing_category',
						'_relation'   => 'many_to_many',
					],

					'image'            => [
						'type'      => 'id',
						'_alias'    => '_thumbnail_id',
						'_model'    => 'attachment',
						'_external' => true,
					],

					'images'           => [
						'label'     => hivepress()->translator->get_string( 'images' ),
						'caption'   => hivepress()->translator->get_string( 'select_images' ),
						'type'      => 'attachment_upload',
						'multiple'  => true,
						'max_files' => 10,
						'formats'   => [ 'jpg', 'jpeg', 'png' ],
						'_model'    => 'attachment',
						'_relation' => 'one_to_many',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets model fields.
	 *
	 * @param string $area Display area.
	 * @return array
	 */
	final public function _get_fields( $area = null ) {
		return array_filter(
			$this->fields,
			function( $field ) use ( $area ) {
				return empty( $area ) || in_array( $area, (array) $field->get_arg( '_display_areas' ), true );
			}
		);
	}

	/**
	 * Gets image IDs.
	 *
	 * @return array
	 */
	final public function get_images__id() {
		if ( ! isset( $this->values['images__id'] ) ) {

			// Get cached image IDs.
			$image_ids = hivepress()->cache->get_post_cache( $this->id, 'image_ids', 'models/attachment' );

			if ( is_null( $image_ids ) ) {
				$image_ids = [];

				// Get file formats.
				$formats = [ 'image' ];

				if ( get_option( 'hp_listing_allow_video' ) ) {
					$formats[] = 'video';
				}

				// Get image IDs.
				$fallback = get_option( 'hp_installed_time' ) < strtotime( '2024-07-08' );

				foreach ( get_attached_media( $formats, $this->id ) as $image ) {
					if ( 'images' === $image->hp_parent_field || ( $fallback && ! $image->hp_parent_field ) ) {
						$image_ids[] = $image->ID;
					}
				}

				if ( has_post_thumbnail( $this->id ) ) {
					$image_id = absint( get_post_thumbnail_id( $this->id ) );

					if ( ! in_array( $image_id, $image_ids, true ) ) {
						array_unshift( $image_ids, $image_id );
					}
				}

				// Cache image IDs.
				hivepress()->cache->set_post_cache( $this->id, 'image_ids', 'models/attachment', $image_ids );
			}

			// Set field value.
			$this->set_images( $image_ids );
			$this->values['images__id'] = $image_ids;
		}

		return $this->fields['images']->get_value();
	}
}
