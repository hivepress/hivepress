<?php
/**
 * Cache component test.
 *
 * @package HivePress\Tests\Components
 */

namespace HivePress\Tests\Components;

/**
 * Cache component test class.
 *
 * @class Cache
 */
class Cache extends \PHPUnit\Framework\TestCase {

	/**
	 * Gets transient cache.
	 *
	 * @test
	 */
	public function get_cache() {

		// Get non-existent.
		$this->assertSame( null, hivepress()->cache->get_cache( 'key' ) );

		// Get by key.
		set_transient( 'hp_key', 'value' );
		$this->assertSame( 'value', hivepress()->cache->get_cache( 'key' ) );

		// Get by key and group.
		$version = (string) time();

		set_transient( 'hp_group/version', $version );

		set_transient( 'hp_group/' . md5( 'key' . $version ), 'value' );
		$this->assertSame( 'value', hivepress()->cache->get_cache( 'key', 'group' ) );

		set_transient(
			'hp_group/' . md5(
				wp_json_encode(
					[
						'a' => 1,
						'b' => 2,
					]
				) . $version
			),
			'value'
		);

		$this->assertSame(
			'value',
			hivepress()->cache->get_cache(
				[
					'b' => 2,
					'a' => 1,
				],
				'group'
			)
		);
	}

	/**
	 * Gets meta cache.
	 *
	 * @test
	 */
	public function get_meta_cache() {
		$types      = [ 'user', 'post', 'comment', 'term' ];
		$object_ids = $this->get_object_ids();

		foreach ( $types as $type ) {

			// Get non-existent.
			$this->assertSame( null, call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $object_ids[ $type ], 'key' ] ) );

			// Get by key.
			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_key', 'value' ] );

			$this->assertSame( 'value', call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $object_ids[ $type ], 'key' ] ) );

			// Get by key and group.
			$version = (string) time();

			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group/version', $version ] );

			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group/' . md5( 'key' . $version ), 'value' ] );
			$this->assertSame( 'value', call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $object_ids[ $type ], 'key', 'group' ] ) );

			call_user_func_array(
				'update_' . $type . '_meta',
				[
					$object_ids[ $type ],
					'_transient_hp_group/' . md5(
						wp_json_encode(
							[
								'a' => 1,
								'b' => 2,
							]
						) . $version
					),
					'value',
				]
			);

			$this->assertSame(
				'value',
				call_user_func_array(
					[ hivepress()->cache, 'get_' . $type . '_cache' ],
					[
						$object_ids[ $type ],
						[
							'b' => 2,
							'a' => 1,
						],
						'group',
					]
				)
			);
		}
	}

	/**
	 * Gets object IDs.
	 *
	 * @return array
	 */
	protected function get_object_ids() {
		$object_ids = [];

		// User.
		$object_ids['user'] = wp_insert_user(
			[
				'user_login' => 'username',
				'user_email' => uniqid() . '@example.com',
			]
		);

		// Post.
		register_post_type( 'hp_post_type' );

		$object_ids['post'] = wp_insert_post(
			[
				'post_title' => 'title',
				'post_type'  => 'hp_post_type',
			]
		);

		// Comment.
		$object_ids['comment'] = wp_insert_comment(
			[
				'comment_type' => 'hp_type',
			]
		);

		// Term.
		register_taxonomy( 'hp_taxonomy', 'hp_post_type' );

		$object_ids['term'] = wp_insert_term( 'term', 'hp_taxonomy' );

		var_dump( $object_ids );
		return $object_ids;
	}
}
