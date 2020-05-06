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
 * Listing model class.
 *
 * @class Listing
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
					'title'            => [
						'label'      => esc_html__( 'Title', 'hivepress' ),
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

					'description'      => [
						'label'      => esc_html__( 'Description', 'hivepress' ),
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
						'type'      => 'checkbox',
						'_external' => true,
					],

					'verified'         => [
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
						'type'      => 'number',
						'min_value' => 1,
						'required'  => true,
						'_alias'    => 'post_author',
						'_model'    => 'user',
					],

					'vendor'           => [
						'type'      => 'number',
						'min_value' => 1,
						'required'  => true,
						'_alias'    => 'post_parent',
						'_model'    => 'vendor',
					],

					'categories'       => [
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'multiple'    => true,
						'_model'      => 'listing_category',
						'_relation'   => 'many_to_many',
					],

					'image'            => [
						'type'      => 'number',
						'min_value' => 1,
						'_alias'    => '_thumbnail_id',
						'_model'    => 'attachment',
						'_external' => true,
					],

					'images'           => [
						'label'     => esc_html__( 'Images', 'hivepress' ),
						'caption'   => esc_html__( 'Select Images', 'hivepress' ),
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
	 * Gets image URL.
	 *
	 * @param string $size Image size.
	 * @return string
	 */
	final public function get_image__url( $size = 'thumbnail' ) {

		// Get field name.
		$name = 'image__url__' . $size;

		if ( ! isset( $this->values[ $name ] ) ) {
			$this->values[ $name ] = '';

			// Get image URL.
			if ( $this->get_image__id() ) {
				$urls = wp_get_attachment_image_src( $this->get_image__id(), $size );

				if ( $urls ) {
					$this->values[ $name ] = hp\get_first_array_value( $urls );
				}
			}
		}

		return $this->values[ $name ];
	}

	/**
	 * Gets image IDs.
	 *
	 * @return array
	 */
	final public function get_images__id() {
		if ( ! isset( $this->values['images__id'] ) ) {

			// Get image IDs.
			$image_ids = wp_list_pluck( get_attached_media( 'image', $this->id ), 'ID' );

			if ( has_post_thumbnail( $this->id ) ) {
				$image_id = absint( get_post_thumbnail_id( $this->id ) );

				if ( ! in_array( $image_id, $image_ids, true ) ) {
					array_unshift( $image_ids, $image_id );
				}
			}

			// Set field value.
			$this->set_images( $image_ids );
			$this->values['images__id'] = $image_ids;
		}

		return $this->fields['images']->get_value();
	}

	/**
	 * Gets image URLs.
	 *
	 * @param string $size Image size.
	 * @return array
	 */
	final public function get_images__url( $size = 'thumbnail' ) {

		// Get field name.
		$name = 'images__url__' . $size;

		if ( ! isset( $this->values[ $name ] ) ) {

			// Get image URLs.
			$image_urls = [];

			if ( $this->get_images__id() ) {
				foreach ( $this->get_images__id() as $image_id ) {
					$urls = wp_get_attachment_image_src( $image_id, $size );

					if ( $urls ) {
						$image_urls[] = hp\get_first_array_value( $urls );
					}
				}
			}

			// Set field value.
			$this->values[ $name ] = $image_urls;
		}

		return $this->values[ $name ];
	}
}
