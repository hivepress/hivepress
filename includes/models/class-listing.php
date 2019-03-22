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
	 * Model name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Model fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Model aliases.
	 *
	 * @var array
	 */
	protected static $aliases = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Model arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
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

					'vendor_id'   => [
						'type'      => 'number',
						'min_value' => 0,
					],
				],

				'aliases' => [
					'post_title'   => 'title',
					'post_content' => 'description',
					'post_status'  => 'status',
					'post_author'  => 'user_id',
					'post_parent'  => 'vendor_id',
				],
			],
			$args
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
	 * Gets display fields.
	 *
	 * @param string $area Area name.
	 * @return array
	 */
	final public function get_display_fields( $area ) {
		$fields = [];

		foreach ( static::$fields as $field_name => $field ) {
			$field_args = $field->get_args();

			if ( in_array( $area, hp\get_array_value( $field_args, 'display_areas', [] ), true ) ) {

				// Get value.
				$field_value    = hp\get_array_value( $this->attributes, $field_name );
				$field_taxonomy = hp\prefix( static::$name . '_' . $field_name );

				if ( array_key_exists( 'options', $field_args ) && taxonomy_exists( $field_taxonomy ) ) {
					$field_value = implode( ', ', wp_get_post_terms( $this->id, $field_taxonomy, [ 'fields' => 'names' ] ) );
				}

				// Create field.
				$fields[ $field_name ] = new \HivePress\Fields\Text(
					[
						'label'   => $field->get_label(),
						'default' => hp\replace_placeholders(
							[
								'value' => $field_value,
							],
							hp\get_array_value( $field_args, 'display_format', '%value%' )
						),
					]
				);
			}
		}

		return $fields;
	}
}
