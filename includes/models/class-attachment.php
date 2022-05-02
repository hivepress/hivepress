<?php
/**
 * Attachment model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * File attachment.
 *
 * @OA\Schema(description="")
 */
class Attachment extends Post {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'alias' => 'attachment',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'mime_type'    => [
						'type'       => 'text',
						'max_length' => 256,
						'_alias'     => 'post_mime_type',
					],

					/**
					* @OA\Property(
					*   property="sort_order",
					*   type="integer",
					*   description="Sort order.",
					* )
					 */
					'sort_order'   => [
						'type'      => 'number',
						'min_value' => 0,
						'_alias'    => 'menu_order',
					],

					'parent_model' => [
						'type'       => 'text',
						'max_length' => 128,
						'_external'  => true,
					],

					'parent_field' => [
						'type'       => 'text',
						'max_length' => 128,
						'_external'  => true,
					],

					'parent__id'   => [
						'type'   => 'id',
						'_alias' => 'post_parent',
					],

					'parent'       => [
						'type'   => 'id',
						'_alias' => 'comment_count',
					],

					'user'         => [
						'type'     => 'id',
						'required' => true,
						'_alias'   => 'post_author',
						'_model'   => 'user',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets parent object ID.
	 *
	 * @return int
	 */
	final public function get_parent__id() {
		$id = $this->fields['parent']->get_value();

		if ( $this->fields['parent__id']->get_value() ) {
			$id = $this->fields['parent__id']->get_value();
		}

		return $id;
	}

	/**
	 * Gets parent object.
	 *
	 * @return object
	 */
	final public function get_parent() {

		// Get object ID.
		$id = $this->get_parent__id();

		if ( $id ) {

			// @todo remove temporary fix.
			if ( ! $this->get_parent_model() && $this->fields['parent__id']->get_value() ) {
				$this->set_parent_model( hp\unprefix( get_post_type( $id ) ) );

				if ( ! $this->get_parent_field() ) {
					$this->set_parent_field( 'images' );
				}

				if ( ! $this->fields['parent']->get_value() ) {
					$this->fields['parent']->set_value( $id );
				}
			}

			return hivepress()->model->get_model_object( $this->get_parent_model(), $id );
		}
	}

	/**
	 * Gets attachment filepath.
	 *
	 * @return string
	 */
	final public function get_path() {
		$path = null;

		if ( $this->id ) {
			if ( ! isset( $this->values['path'] ) ) {
				$this->values['path'] = get_attached_file( $this->id );
			}

			if ( $this->values['path'] ) {
				$path = $this->values['path'];
			}
		}

		return $path;
	}

	/**
	 * Gets attachment URL.
	 *
	 * @param string $size Image size.
	 * @return string
	 */
	final public function get_url( $size = 'full' ) {
		$url  = null;
		$name = 'url';

		if ( $this->id ) {

			// Get image flag.
			$image = strpos( $this->get_mime_type(), 'image/' ) === 0;

			if ( $image ) {
				$name .= '__' . $size;
			}

			if ( ! isset( $this->values[ $name ] ) ) {
				$this->values[ $name ] = '';

				if ( $image ) {

					// Get image URL.
					$urls = wp_get_attachment_image_src( $this->id, $size );

					if ( $urls ) {
						$this->values[ $name ] = hp\get_first_array_value( $urls );
					}
				} else {

					// Get file URL.
					$this->values[ $name ] = wp_get_attachment_url( $this->id );
				}
			}

			if ( $this->values[ $name ] ) {
				$url = $this->values[ $name ];
			}
		}

		return $url;
	}

	/**
	 * Gets attachment name.
	 *
	 * @return string
	 */
	final public function get_name() {
		$name = null;
		$url  = $this->get_url();

		if ( $url ) {
			$name = wp_basename( $url );
		}

		return $name;
	}
}
