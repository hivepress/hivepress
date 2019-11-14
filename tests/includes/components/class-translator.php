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
	 * Gets string.
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
