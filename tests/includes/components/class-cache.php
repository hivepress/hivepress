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
	 * Schedules events.
	 *
	 * @test
	 */
	public function schedule_events() {
		$periods = [ 'hourly', 'twicedaily', 'daily' ];

		foreach ( $periods as $period ) {
			$this->assertNotSame( false, wp_next_scheduled( 'hivepress/v1/cron/' . $period ) );
		}
	}

	/**
	 * Unschedules events.
	 *
	 * @test
	 */
	public function unschedule_events() {
		hivepress()->cache->unschedule_events();

		$periods = [ 'hourly', 'twicedaily', 'daily' ];

		foreach ( $periods as $period ) {
			$this->assertSame( false, wp_next_scheduled( 'hivepress/v1/cron/' . $period ) );
		}
	}

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

		set_transient( 'hp_group1/version', $version );

		set_transient( 'hp_group1/' . md5( 'key1' . $version ), 'value' );
		$this->assertSame( 'value', hivepress()->cache->get_cache( 'key1', 'group1' ) );

		set_transient(
			'hp_group1/' . md5(
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
				'group1'
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

			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group2/version', $version ] );

			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group2/' . md5( 'key2' . $version ), 'value' ] );
			$this->assertSame( 'value', call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $object_ids[ $type ], 'key2', 'group2' ] ) );

			call_user_func_array(
				'update_' . $type . '_meta',
				[
					$object_ids[ $type ],
					'_transient_hp_group2/' . md5(
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
						'group2',
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

		set_transient( 'hp_group3/version', $version );

		hivepress()->cache->set_cache( 'key3', 'group3', 'value' );
		$this->assertSame( 'value', get_transient( 'hp_group3/' . md5( 'key3' . $version ) ) );

		hivepress()->cache->set_cache(
			[
				'b' => 5,
				'a' => 6,
			],
			'group3',
			'value'
		);

		$this->assertSame(
			'value',
			get_transient(
				'hp_group3/' . md5(
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

			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group4/version', $version ] );

			call_user_func_array( [ hivepress()->cache, 'set_' . $type . '_cache' ], [ $object_ids[ $type ], 'key4', 'group4', 'value' ] );
			$this->assertSame( 'value', call_user_func_array( 'get_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group4/' . md5( 'key4' . $version ), true ] ) );

			call_user_func_array(
				[ hivepress()->cache, 'set_' . $type . '_cache' ],
				[
					$object_ids[ $type ],
					[
						'b' => 7,
						'a' => 8,
					],
					'group4',
					'value',
				]
			);

			$this->assertSame(
				'value',
				call_user_func_array(
					'get_' . $type . '_meta',
					[
						$object_ids[ $type ],
						'_transient_hp_group4/' . md5(
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
	 * Deletes transient cache.
	 *
	 * @test
	 */
	public function delete_cache() {

		// Delete by key.
		set_transient( 'hp_key', 'value' );
		hivepress()->cache->delete_cache( 'key' );
		$this->assertNotSame( 'value', get_transient( 'hp_key' ) );

		// Delete by key and group.
		$version = (string) time();

		set_transient( 'hp_group5/version', $version );

		set_transient( 'hp_group5/' . md5( 'key5' . $version ), 'value' );
		hivepress()->cache->delete_cache( 'key5', 'group5' );
		$this->assertNotSame( 'value', get_transient( 'hp_group5/' . md5( 'key5' . $version ) ) );

		set_transient(
			'hp_group5/' . md5(
				wp_json_encode(
					[
						'a' => 9,
						'b' => 10,
					]
				) . $version
			),
			'value'
		);

		hivepress()->cache->delete_cache(
			[
				'b' => 10,
				'a' => 9,
			],
			'group5'
		);

		$this->assertNotSame(
			'value',
			get_transient(
				'hp_group5/' . md5(
					wp_json_encode(
						[
							'a' => 9,
							'b' => 10,
						]
					) . $version
				)
			)
		);
	}

	/**
	 * Deletes meta cache.
	 *
	 * @test
	 */
	public function delete_meta_cache() {
		$types      = [ 'user', 'post', 'comment', 'term' ];
		$object_ids = $this->get_object_ids();

		foreach ( $types as $type ) {

			// Delete by key.
			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_key6', 'value' ] );
			call_user_func_array( [ hivepress()->cache, 'delete_' . $type . '_cache' ], [ $object_ids[ $type ], 'key6' ] );
			$this->assertNotSame( 'value', call_user_func_array( 'get_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_key6', true ] ) );

			// Delete by key and group.
			$version = (string) time();

			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group6/version', $version ] );

			call_user_func_array( 'update_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group6/' . md5( 'key6' . $version ), 'value' ] );
			call_user_func_array( [ hivepress()->cache, 'delete_' . $type . '_cache' ], [ $object_ids[ $type ], 'key6', 'group6' ] );
			$this->assertNotSame( 'value', call_user_func_array( 'get_' . $type . '_meta', [ $object_ids[ $type ], '_transient_hp_group6/' . md5( 'key6' . $version ), true ] ) );

			call_user_func_array(
				'update_' . $type . '_meta',
				[
					$object_ids[ $type ],
					'_transient_hp_group6/' . md5(
						wp_json_encode(
							[
								'a' => 11,
								'b' => 12,
							]
						) . $version
					),
					'value',
				]
			);

			call_user_func_array(
				[ hivepress()->cache, 'delete_' . $type . '_cache' ],
				[
					$object_ids[ $type ],
					[
						'b' => 12,
						'a' => 11,
					],
					'group6',
				]
			);

			$this->assertNotSame(
				'value',
				call_user_func_array(
					'get_' . $type . '_meta',
					[
						$object_ids[ $type ],
						'_transient_hp_group6/' . md5(
							wp_json_encode(
								[
									'a' => 11,
									'b' => 12,
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
	 * Gets transient cache version.
	 *
	 * @test
	 */
	public function get_cache_version() {

		// Get non-existent.
		$this->assertNotEquals( null, hivepress()->cache->get_cache_version( 'group8' ) );

		// Get by group.
		$version = (string) time();

		set_transient( 'hp_group9', $version );
		$this->assertSame( $version, hivepress()->cache->get_cache_version( 'group9' ) );
	}

	/**
	 * Clears meta cache.
	 *
	 * @test
	 */
	public function clear_meta_cache() {
		$types      = [ 'user', 'post', 'comment', 'term' ];
		$object_ids = $this->get_object_ids();

		foreach ( $types as $type ) {
			call_user_func_array( [ hivepress()->cache, 'set_' . $type . '_cache' ], [ $object_ids[ $type ], 'key7', null, 'value', time() - 1 ] );

			hivepress()->cache->clear_meta_cache();

			$this->assertSame( null, call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $object_ids[ $type ], 'key7' ] ) );
		}
	}

	/**
	 * Clears post cache.
	 *
	 * @test
	 */
	public function clear_post_cache() {
		$object_ids = $this->get_object_ids();

		wp_update_post(
			[
				'ID'          => $object_ids['post'],
				'post_author' => $object_ids['user'],
			]
		);

		hivepress()->cache->set_cache( 'key8', 'post/post_type', 'value' );
		hivepress()->cache->set_user_cache( $object_ids['user'], 'key8', 'post/post_type', 'value' );

		hivepress()->cache->clear_post_cache( $object_ids['post'] );

		$this->assertSame( null, hivepress()->cache->get_cache( 'key8', 'post/post_type' ) );
		$this->assertSame( null, hivepress()->cache->get_user_cache( $object_ids['user'], 'key8', 'post/post_type' ) );
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
