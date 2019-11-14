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
		$this->assertSame( null, hivepress()->cache->get_cache( 'key1' ) );

		// Get by key.
		set_transient( 'hp_key1', 'value' );
		$this->assertSame( 'value', hivepress()->cache->get_cache( 'key1' ) );

		// Get by key and group.
		$version = (string) time();

		set_transient( 'hp_group/version', $version );

		set_transient( 'hp_group/' . md5( 'key1' . $version ), 'value' );
		$this->assertSame( 'value', hivepress()->cache->get_cache( 'key1', 'group' ) );

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
			$this->assertSame( null, call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $object_ids[ $type ], 'key2' ] ) );

			// Get by key.
			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_key2', 'value' ] );

			$this->assertSame( 'value', call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $object_ids[ $type ], 'key2' ] ) );

			// Get by key and group.
			$version = (string) time();

			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group/version', $version ] );

			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group/' . md5( 'key2' . $version ), 'value' ] );
			$this->assertSame( 'value', call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $object_ids[ $type ], 'key2', 'group' ] ) );

			call_user_func_array(
				'update_' . $type . '_meta',
				[
					$object_ids[ $type ],
					'_transient_hp_group/' . md5(
						wp_json_encode(
							[
								'a' => 3,
								'b' => 4,
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
							'b' => 4,
							'a' => 3,
						],
						'group',
					]
				)
			);
		}
	}

	/**
	 * Sets transient cache.
	 *
	 * @test
	 */
	public function set_cache() {

		// Set by key.
		hivepress()->cache->set_cache( 'key3', null, 'value' );

		$this->assertSame( 'value', get_transient( 'hp_key3' ) );

		// Set by key and group.
		$version = (string) time();

		set_transient( 'hp_group/version', $version );

		hivepress()->cache->set_cache( 'key3', 'group', 'value' );
		$this->assertSame( 'value', get_transient( 'hp_group/' . md5( 'key3' . $version ) ) );

		hivepress()->cache->set_cache(
			[
				'b' => 5,
				'a' => 6,
			],
			'group',
			'value'
		);

		$this->assertSame(
			'value',
			get_transient(
				'hp_group/' . md5(
					wp_json_encode(
						[
							'a' => 6,
							'b' => 5,
						]
					) . $version
				)
			)
		);
	}

	/**
	 * Sets meta cache.
	 *
	 * @test
	 */
	public function set_meta_cache() {
		$types      = [ 'user', 'post', 'comment', 'term' ];
		$object_ids = $this->get_object_ids();

		foreach ( $types as $type ) {

			// Set by key.
			call_user_func_array( [ hivepress()->cache, 'set_' . $type . '_cache' ], [ $object_ids[ $type ], 'key4', null, 'value' ] );
			$this->assertSame( 'value', call_user_func_array( 'get_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_key4', true ] ) );

			// Set by key and group.
			$version = (string) time();

			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group/version', $version ] );

			call_user_func_array( [ hivepress()->cache, 'set_' . $type . '_cache' ], [ $object_ids[ $type ], 'key4', 'group', 'value' ] );
			$this->assertSame( 'value', call_user_func_array( 'get_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group/' . md5( 'key4' . $version ), true ] ) );

			call_user_func_array(
				[ hivepress()->cache, 'set_' . $type . '_cache' ],
				[
					$object_ids[ $type ],
					[
						'b' => 7,
						'a' => 8,
					],
					'group',
					'value',
				]
			);

			$this->assertSame(
				'value',
				call_user_func_array(
					'get_' . $type . '_meta',
					[
						$object_ids[ $type ],
						'_transient_hp_group/' . md5(
							wp_json_encode(
								[
									'a' => 8,
									'b' => 7,
								]
							) . $version
						),
						true,
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
				'user_login' => 'user' . uniqid(),
				'user_email' => uniqid() . '@example.com',
				'user_pass'  => wp_generate_password(),
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

		$term = wp_insert_term( 'term' . uniqid(), 'hp_taxonomy' );

		$object_ids['term'] = $term['term_id'];

		return $object_ids;
	}
}
