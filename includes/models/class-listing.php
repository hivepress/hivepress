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
					'title'         => [
						'label'      => esc_html__( 'Title', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'post_title',
					],

					'description'   => [
						'label'      => esc_html__( 'Description', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'required'   => true,
						'_alias'     => 'post_content',
					],

					'status'        => [
						'type'       => 'text',
						'max_length' => 128,
						'_alias'     => 'post_status',
					],

					'featured'      => [
						'type'      => 'checkbox',
						'_external' => true,
					],

					'verified'      => [
						'type'      => 'checkbox',
						'_external' => true,
					],

					'date_created'  => [
						'type'   => 'date',
						'_alias' => 'post_date',
					],

					'date_modified' => [
						'type'   => 'date',
						'_alias' => 'post_modified',
					],

					'user'          => [
						'type'      => 'number',
						'min_value' => 1,
						'required'  => true,
						'_alias'    => 'post_author',
						'_model'    => 'user',
					],

					'vendor'        => [
						'type'      => 'number',
						'min_value' => 1,
						'required'  => true,
						'_alias'    => 'post_parent',
						'_model'    => 'vendor',
					],

					'categories'    => [
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'multiple'    => true,
						'_model'      => 'listing_category',
						'_relation'   => 'many_to_many',
					],

					'image'         => [
						'type'      => 'number',
						'min_value' => 1,
						'_alias'    => '_thumbnail_id',
						'_model'    => 'attachment',
						'_external' => true,
					],

					'images'        => [
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
		// todo.
		return $this->fields;
	}

	/**
	 * Gets image IDs.
	 *
	 * @return array
	 */
	final public function get_images__id() {
		if ( ! isset( $this->values['images__id'] ) ) {
			$image_ids = wp_list_pluck( get_attached_media( 'image', $this->id ), 'ID' );

			if ( has_post_thumbnail( $this->id ) ) {
				array_unshift( $image_ids, get_post_thumbnail_id( $this->id ) );
			}

			$this->values['images__id'] = array_unique( $image_ids );
		}

		return $this->values['images__id'];
	}

	/**
	 * Gets image URLs.
	 *
	 * @param string $size Image size.
	 * @return array
	 */
	final public function get_images__url( $size = 'thumbnail' ) {
		if ( ! isset( $this->values['images__url'] ) ) {
			$image_urls = [];

			foreach ( $this->get_images__id() as $image_id ) {
				$urls = wp_get_attachment_image_src( $image_id, $size );

				if ( $urls ) {
					$image_urls[ $image_id ] = reset( $urls );
				}
			}

			$this->values['images__url'] = $image_urls;
		}

		return $this->values['images__url'];
	}
}
