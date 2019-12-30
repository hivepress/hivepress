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
				'fields'    => [
					'title'         => [
						'label'      => esc_html__( 'Title', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 128,
						'required'   => true,
					],

					'description'   => [
						'label'      => esc_html__( 'Description', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'required'   => true,
					],

					'status'        => [
						'type'       => 'text',
						'max_length' => 128,
					],

					'date_created'  => [
						'type' => 'date',
					],

					'date_modified' => [
						'type' => 'date',
					],

					'featured'      => [
						'type' => 'checkbox',
					],

					'verified'      => [
						'type' => 'checkbox',
					],

					'image_ids'     => [
						'label'     => esc_html__( 'Images', 'hivepress' ),
						'caption'   => esc_html__( 'Select Images', 'hivepress' ),
						'type'      => 'attachment_upload',
						'multiple'  => true,
						'max_files' => 10,
						'formats'   => [ 'jpg', 'jpeg', 'png' ],
					],

					'user_id'       => [
						'type'      => 'number',
						'min_value' => 1,
						'required'  => true,
					],

					'vendor_id'     => [
						'type'      => 'number',
						'min_value' => 1,
						'required'  => true,
					],

					'category_ids'  => [
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'multiple'    => true,
					],
				],

				'aliases'   => [
					'post_title'    => 'title',
					'post_content'  => 'description',
					'post_status'   => 'status',
					'post_date'     => 'date_created',
					'post_modified' => 'date_modified',
					'post_author'   => 'user_id',
					'post_parent'   => 'vendor_id',
				],

				'relations' => [
					'listing_category' => 'category_ids',
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets image IDs.
	 *
	 * @return array
	 */
	final public function get_image_ids() {
		$image_ids = wp_list_pluck( get_attached_media( 'image', $this->id ), 'ID' );

		if ( has_post_thumbnail( $this->id ) ) {
			array_unshift( $image_ids, get_post_thumbnail_id( $this->id ) );
		}

		return array_unique( $image_ids );
	}

	/**
	 * Gets image URLs.
	 *
	 * @param string $size Image size.
	 * @return array
	 */
	final public function get_image_urls( $size = 'thumbnail' ) {
		$image_urls = [];

		foreach ( $this->get_image_ids() as $image_id ) {
			$urls = wp_get_attachment_image_src( $image_id, $size );

			if ( $urls ) {
				$image_urls[ $image_id ] = reset( $urls );
			}
		}

		return $image_urls;
	}
}
