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
	 * Cron periods.
	 *
	 * @var array
	 */
	protected $cron_periods = [ 'hourly', 'twicedaily', 'daily' ];

	/**
	 * Cache types.
	 *
	 * @var array
	 */
	protected $cache_types = [ 'user', 'term', 'post', 'comment' ];

	/**
	 * Object IDs.
	 *
	 * @var array
	 */
	protected $object_ids = [];

	/**
	 * Setups test.
	 */
	protected function setUp() {
		$this->object_ids = [];

		// User.
		$this->object_ids['user'] = wp_insert_user(
			[
				'user_login' => 'user' . uniqid(),
				'user_email' => uniqid() . '@example.com',
				'user_pass'  => wp_generate_password(),
			]
		);

		// Post.
		register_post_type( 'hp_post_type' );

		$this->object_ids['post'] = wp_insert_post(
			[
				'post_title' => 'title',
				'post_type'  => 'hp_post_type',
			]
		);

		// Comment.
		$this->object_ids['comment'] = wp_insert_comment(
			[
				'comment_type' => 'hp_type',
			]
		);

		// Term.
		register_taxonomy( 'hp_taxonomy', 'hp_post_type' );

		$term = wp_insert_term( 'term' . uniqid(), 'hp_taxonomy' );

		$this->object_ids['term'] = $term['term_id'];
	}

	/**
	 * Schedules events.
	 *
	 * @test
	 */
	public function schedule_events() {
		foreach ( $this->cron_periods as $period ) {
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

		foreach ( $this->cron_periods as $period ) {
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
		$this->assertNull( hivepress()->cache->get_cache( 'key1' ) );

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
		foreach ( $this->cache_types as $type ) {

			// Get non-existent.
			$this->assertNull( call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $this->object_ids[ $type ], 'key2' ] ) );

			// Get by key.
			call_user_func_array( 'update_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_key2', 'value' ] );

			$this->assertSame( 'value', call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $this->object_ids[ $type ], 'key2' ] ) );

			// Get by key and group.
			$version = (string) time();

			call_user_func_array( 'update_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_group2/version', $version ] );

			call_user_func_array( 'update_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_group2/' . md5( 'key2' . $version ), 'value' ] );
			$this->assertSame( 'value', call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $this->object_ids[ $type ], 'key2', 'group2' ] ) );

			call_user_func_array(
				'update_' . $type . '_meta',
				[
					$this->object_ids[ $type ],
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
						$this->object_ids[ $type ],
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
		foreach ( $this->cache_types as $type ) {

			// Set by key.
			call_user_func_array( [ hivepress()->cache, 'set_' . $type . '_cache' ], [ $this->object_ids[ $type ], 'key4', null, 'value' ] );
			$this->assertSame( 'value', call_user_func_array( 'get_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_key4', true ] ) );

			// Set by key and group.
			$version = (string) time();

			call_user_func_array( 'update_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_group4/version', $version ] );

			call_user_func_array( [ hivepress()->cache, 'set_' . $type . '_cache' ], [ $this->object_ids[ $type ], 'key4', 'group4', 'value' ] );
			$this->assertSame( 'value', call_user_func_array( 'get_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_group4/' . md5( 'key4' . $version ), true ] ) );

			call_user_func_array(
				[ hivepress()->cache, 'set_' . $type . '_cache' ],
				[
					$this->object_ids[ $type ],
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
						$this->object_ids[ $type ],
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
		foreach ( $this->cache_types as $type ) {

			// Delete by key.
			call_user_func_array( 'update_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_key6', 'value' ] );
			call_user_func_array( [ hivepress()->cache, 'delete_' . $type . '_cache' ], [ $this->object_ids[ $type ], 'key6' ] );
			$this->assertNotSame( 'value', call_user_func_array( 'get_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_key6', true ] ) );

			// Delete by key and group.
			$version = (string) time();

			call_user_func_array( 'update_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_group6/version', $version ] );

			call_user_func_array( 'update_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_group6/' . md5( 'key6' . $version ), 'value' ] );
			call_user_func_array( [ hivepress()->cache, 'delete_' . $type . '_cache' ], [ $this->object_ids[ $type ], 'key6', 'group6' ] );
			$this->assertNotSame( 'value', call_user_func_array( 'get_' . $type . '_meta', [ $this->object_ids[ $type ], '_transient_hp_group6/' . md5( 'key6' . $version ), true ] ) );

			call_user_func_array(
				'update_' . $type . '_meta',
				[
					$this->object_ids[ $type ],
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
					$this->object_ids[ $type ],
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
						$this->object_ids[ $type ],
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
		foreach ( $this->cache_types as $type ) {
			call_user_func_array( [ hivepress()->cache, 'set_' . $type . '_cache' ], [ $this->object_ids[ $type ], 'key7', null, 'value', -1000 ] );

			do_action( 'hivepress/v1/cron/daily' );

			$this->assertNull( call_user_func_array( [ hivepress()->cache, 'get_' . $type . '_cache' ], [ $this->object_ids[ $type ], 'key7' ] ) );
		}
	}

	/**
	 * Clears post cache.
	 *
	 * @test
	 */
	public function clear_post_cache() {

		// Create post.
		hivepress()->cache->set_cache( 'key8', 'post/post_type', 'value' );
		hivepress()->cache->set_user_cache( $this->object_ids['user'], 'key8', 'post/post_type', 'value' );

		wp_insert_post(
			[
				'post_title'  => 'title',
				'post_type'   => 'hp_post_type',
				'post_author' => $this->object_ids['user'],
			]
		);

		$this->assertNull( hivepress()->cache->get_cache( 'key8', 'post/post_type' ) );
		$this->assertNull( hivepress()->cache->get_user_cache( $this->object_ids['user'], 'key8', 'post/post_type' ) );

		// Update post.
		hivepress()->cache->set_cache( 'key9', 'post/post_type', 'value' );
		hivepress()->cache->set_user_cache( $this->object_ids['user'], 'key9', 'post/post_type', 'value' );

		wp_update_post(
			[
				'ID'          => $this->object_ids['post'],
				'post_author' => $this->object_ids['user'],
			]
		);

		$this->assertNull( hivepress()->cache->get_cache( 'key9', 'post/post_type' ) );
		$this->assertNull( hivepress()->cache->get_user_cache( $this->object_ids['user'], 'key9', 'post/post_type' ) );

		// Delete post.
		hivepress()->cache->set_cache( 'key9', 'post/post_type', 'value' );
		hivepress()->cache->set_user_cache( $this->object_ids['user'], 'key9', 'post/post_type', 'value' );

		wp_delete_post( $this->object_ids['post'], true );

		$this->assertNull( hivepress()->cache->get_cache( 'key9', 'post/post_type' ) );
		$this->assertNull( hivepress()->cache->get_user_cache( $this->object_ids['user'], 'key9', 'post/post_type' ) );
	}

	/**
	 * Clears post term cache.
	 *
	 * @test
	 */
	public function clear_post_term_cache() {
		hivepress()->cache->set_term_cache( $this->object_ids['term'], 'key11', 'post/post_type', 'value' );
		hivepress()->cache->set_post_cache( $this->object_ids['post'], 'key11', 'term/taxonomy', 'value' );

		wp_set_post_terms( $this->object_ids['post'], [ $this->object_ids['term'] ], 'hp_taxonomy' );

		$this->assertNull( hivepress()->cache->get_term_cache( $this->object_ids['term'], 'key11', 'post/post_type' ) );
		$this->assertNull( hivepress()->cache->get_post_cache( $this->object_ids['post'], 'key11', 'term/taxonomy' ) );
	}

	/**
	 * Clears term cache.
	 *
	 * @test
	 */
	public function clear_term_cache() {

		// Create term.
		hivepress()->cache->set_cache( 'key12', 'term/taxonomy', 'value' );

		wp_insert_term( 'term' . uniqid(), 'hp_taxonomy' );

		$this->assertNull( hivepress()->cache->get_cache( 'key12', 'term/taxonomy' ) );

		// Update term.
		hivepress()->cache->set_cache( 'key12', 'term/taxonomy', 'value' );

		wp_update_term( $this->object_ids['term'], 'hp_taxonomy' );

		$this->assertNull( hivepress()->cache->get_cache( 'key12', 'term/taxonomy' ) );

		// Delete term.
		hivepress()->cache->set_cache( 'key12', 'term/taxonomy', 'value' );

		wp_delete_term( $this->object_ids['term'], 'hp_taxonomy' );

		$this->assertNull( hivepress()->cache->get_cache( 'key12', 'term/taxonomy' ) );
	}

	/**
	 * Clears comment cache.
	 *
	 * @test
	 */
	public function clear_comment_cache() {

		// Create comment.
		hivepress()->cache->set_cache( 'key13', 'comment/hp_comment', 'value' );
		hivepress()->cache->set_post_cache( $this->object_ids['post'], 'key13', 'comment/hp_comment', 'value' );
		hivepress()->cache->set_user_cache( $this->object_ids['user'], 'key13', 'comment/hp_comment', 'value' );

		wp_insert_comment(
			[
				'comment_type'    => 'hp_comment',
				'user_id'         => $this->object_ids['user'],
				'comment_post_ID' => $this->object_ids['post'],
			]
		);

		$this->assertNull( hivepress()->cache->get_cache( 'key13', 'comment/hp_comment' ) );
		$this->assertNull( hivepress()->cache->get_post_cache( $this->object_ids['post'], 'key13', 'comment/hp_comment' ) );
		$this->assertNull( hivepress()->cache->get_user_cache( $this->object_ids['user'], 'key13', 'comment/hp_comment' ) );

		// Update comment.
		hivepress()->cache->set_cache( 'key13', 'comment/hp_comment', 'value' );
		hivepress()->cache->set_post_cache( $this->object_ids['post'], 'key13', 'comment/hp_comment', 'value' );
		hivepress()->cache->set_user_cache( $this->object_ids['user'], 'key13', 'comment/hp_comment', 'value' );

		wp_update_comment(
			[
				'comment_type'    => 'hp_comment',
				'user_id'         => $this->object_ids['user'],
				'comment_post_ID' => $this->object_ids['post'],
			]
		);

		$this->assertNull( hivepress()->cache->get_cache( 'key13', 'comment/hp_comment' ) );
		$this->assertNull( hivepress()->cache->get_post_cache( $this->object_ids['post'], 'key13', 'comment/hp_comment' ) );
		$this->assertNull( hivepress()->cache->get_user_cache( $this->object_ids['user'], 'key13', 'comment/hp_comment' ) );

		// Delete comment.
		hivepress()->cache->set_cache( 'key13', 'comment/hp_comment', 'value' );
		hivepress()->cache->set_post_cache( $this->object_ids['post'], 'key13', 'comment/hp_comment', 'value' );
		hivepress()->cache->set_user_cache( $this->object_ids['user'], 'key13', 'comment/hp_comment', 'value' );

		wp_delete_comment( $this->object_ids['comment'], true );

		$this->assertNull( hivepress()->cache->get_cache( 'key13', 'comment/hp_comment' ) );
		$this->assertNull( hivepress()->cache->get_post_cache( $this->object_ids['post'], 'key13', 'comment/hp_comment' ) );
		$this->assertNull( hivepress()->cache->get_user_cache( $this->object_ids['user'], 'key13', 'comment/hp_comment' ) );
	}
}
