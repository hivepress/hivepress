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
	 * Class initializer.
	 *
	 * @param array $args Model arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'fields'  => [
					'title'       => [
						'label'      => esc_html__( 'Title', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 128,
						'required'   => true,
					],

					'description' => [
						'label'      => esc_html__( 'Description', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'required'   => true,
					],

					'status'      => [
						'type'       => 'text',
						'max_length' => 128,
					],

					'user_id'     => [
						'type'      => 'number',
						'min_value' => 0,
					],
				],

				'aliases' => [
					'post_title'   => 'title',
					'post_content' => 'description',
					'post_status'  => 'status',
					'post_author'  => 'user_id',
				],
			]
		);

		parent::init( $args );
	}

	/**
	 * Gets image IDs.
	 *
	 * @return array
	 */
	final public function get_image_ids() {
		$image_ids = wp_list_pluck( get_attached_media( 'image', $this->get_id() ), 'ID' );

		if ( has_post_thumbnail( $this->get_id() ) ) {
			array_unshift( $image_ids, get_post_thumbnail_id( $this->get_id() ) );
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

			if ( false !== $urls ) {
				$image_urls[ $image_id ] = reset( $urls );
			}
		}

		return $image_urls;
	}

	/**
	 * Gets attributes.
	 *
	 * @param string $area Area name.
	 * @return array
	 */
	final public function get_attributes( $area ) {
		// todo.
		return [];
	}
}
