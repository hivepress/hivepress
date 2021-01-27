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
						'max_length' => 256,
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
	 * @return mixed
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
	 * @return mixed
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
}
