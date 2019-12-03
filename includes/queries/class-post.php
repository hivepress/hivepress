<?php
/**
 * Post query.
 *
 * @package HivePress\Queries
 */

namespace HivePress\Queries;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Post query class.
 *
 * @class Post
 */
class Post extends Query {

	/**
	 * Class constructor.
	 *
	 * @param array $args Query arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'aliases' => [
					'filter' => [
						'aliases' => [
							'id__in'     => 'post__in',
							'id__not_in' => 'post__not_in',
						],
					],

					'order'  => [
						'aliases' => [
							'random' => 'rand',
							'id'     => 'ID',
							'id__in' => 'post__in',
						],
					],
				],

				'args'    => [
					'post_status'         => 'any',
					'posts_per_page'      => -1,
					'ignore_sticky_posts' => true,
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps query properties.
	 */
	protected function bootstrap() {

		// Set post type.
		$this->args['post_type'] = hp\prefix( $this->model );

		parent::bootstrap();
	}

	final protected function get_objects( $args ) {
		return get_posts( $this->args );
	}

	/**
	 * Gets object count.
	 *
	 * @return int
	 */
	final public function get_count() {
		return count( $this->get_ids() );
	}

	public function order( $criteria ) {
		parent::order($criteria);

		$aliases = [
			'post_date'   => 'date',
			'post_author' => 'author',
		];

		if ( is_array( $args ) ) {
			$args = array_combine(
				array_map(
					function( $name ) use ( $aliases ) {
						return hp\get_array_value( $aliases, $name, $name );
					},
					array_keys( $args )
				),
				$args
			);
		} else {
			$args = hp\get_array_value( $aliases, $args, $args );
		}

		return $args;
	}

	public function filter( $criteria ) {
		parent::filter($criteria);

		if ( isset( $this->args['post_author'] ) ) {
			$this->args['author'] = $this->args['post_author'];

			unset( $this->args['post_author'] );
		}

		if ( isset( $this->args['post__in'] ) && empty( $this->args['post__in'] ) ) {
			$this->args['post__in'] = [ 0 ];
		}

		return $this;
	}
}
