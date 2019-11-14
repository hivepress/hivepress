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
		set_transient( 'hp_group/' . md5( wp_json_encode( [ 'key1', 'key2' ] ) . $version ), 'value' );

		$this->assertSame( 'value', hivepress()->cache->get_cache( 'key', 'group' ) );
		$this->assertSame( 'value', hivepress()->cache->get_cache( [ 'key2', 'key1' ], 'group' ) );
	}
}
