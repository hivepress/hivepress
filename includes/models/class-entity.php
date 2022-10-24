<?php
/**
 * Entity model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Entity.
 */
class Entity extends Post {

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'slug'        => [
						'type'       => 'text',
						'max_length' => 256,
						'_alias'     => 'post_name',
					],

					'description' => [
						'label'      => hivepress()->translator->get_string( 'description' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'html'       => true,
						'required'   => true,
						'_alias'     => 'post_content',
					],

					'status'      => [
						'type'   => 'text',
						'_alias' => 'post_status',
					],

					'user'        => [
						'type'     => 'id',
						'required' => true,
						'_alias'   => 'post_author',
						'_model'   => 'user',
					],

					'categories'  => [
						'label'      => hivepress()->translator->get_string( 'category' ),
						'type'       => 'select',
						'options'    => 'terms',
						'multiple'   => true,
						'required'   => true,
						'_indexable' => true,
						'_relation'  => 'many_to_many',
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
	public function _get_fields( $area = null ) {
		return array_filter(
			$this->fields,
			function( $field ) use ( $area ) {
				return empty( $area ) || in_array( $area, (array) $field->get_arg( '_display_areas' ), true );
			}
		);
	}
}
