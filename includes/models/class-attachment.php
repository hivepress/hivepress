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
 * Attachment model class.
 *
 * @class Attachment
 */
class Attachment extends Post {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Model meta.
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
						'max_length' => 128,
						'_alias'     => 'post_mime_type',
					],

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
	 * Gets parent object.
	 *
	 * @return mixed
	 */
	final public function get_parent() {

		// Get object ID.
		$id = $this->fields['parent']->get_value();

		if ( $id ) {

			// Get model object.
			$model = hp\create_class_instance( '\HivePress\Models\\' . $this->get_parent_model() );

			if ( $model ) {
				return $model::query()->get_by_id( $id );
			}
		}
	}
}
