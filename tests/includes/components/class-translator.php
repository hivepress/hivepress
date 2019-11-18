<?php
/**
 * Translator component test.
 *
 * @package HivePress\Tests\Components
 */

namespace HivePress\Tests\Components;

/**
 * Translator component test class.
 *
 * @class Translator
 */
class Translator extends \PHPUnit\Framework\TestCase {

	/**
	 * Gets language code.
	 *
	 * @test
	 */
	public function get_language() {
		$this->assertSame( 'en', hivepress()->translator->get_language() );
	}

	/**
	 * Gets region code.
	 *
	 * @test
	 */
	public function get_region() {
		$this->assertSame( 'US', hivepress()->translator->get_region() );
	}

	/**
	 * Gets translation string.
	 *
	 * @test
	 */
	public function get_string() {
		add_filter(
			'hivepress/v1/strings',
			function( $strings ) {
				return array_merge(
					$strings,
					[
						'key' => 'value',
					]
				);
			}
		);

		$this->assertSame( 'value', hivepress()->translator->get_string( 'key' ) );
	}
}
