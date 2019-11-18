<?php
/**
 * Number range field test.
 *
 * @package HivePress\Tests\Fields
 */

namespace HivePress\Tests\Fields;

use HivePress\Fields;

/**
 * Number range field test class.
 *
 * @class Number_Range
 */
class Number_Range extends \PHPUnit\Framework\TestCase {

	/**
	 * Field object.
	 *
	 * @var object
	 */
	protected $field;

	/**
	 * Setups test.
	 */
	protected function setUp() {
		$this->field = new Fields\Number_Range();
	}

	/**
	 * Adds field filters.
	 *
	 * @test
	 */
	public function add_filters() {
		$this->field->set_value( [ 1, 2 ] );
		$this->assertSame( 'BETWEEN', $this->field->get_filters()['operator'] );
	}

	/**
	 * Normalizes field value.
	 *
	 * @test
	 */
	public function normalize() {
		$values = [ true, 1, 'value', [], [ 1 ], [ 1, 2, 3 ], new \stdClass() ];

		foreach ( $values as $value ) {
			$this->field->set_value( $value );
			$this->assertSame( null, $this->field->get_value() );
		}

		$this->field->set_value( [ 2, 1 ] );
		$this->assertSame( [ 1, 2 ], $this->field->get_value() );
	}

	/**
	 * Sanitizes field value.
	 *
	 * @test
	 */
	public function sanitize() {
		$values = [ [ null, null ], [ 'a', 'b' ], [ 1, 'b' ], [ 'a', 2 ] ];

		foreach ( $values as $value ) {
			$this->field->set_value( $value );
			$this->assertSame( null, $this->field->get_value() );
		}

		$this->field->set_value( [ 0, 0 ] );
		$this->assertSame( [ 0, 0 ], $this->field->get_value() );
	}

	/**
	 * Validates field value.
	 *
	 * @test
	 */
	public function validate() {
		$this->field->set_value( null );
		$this->assertTrue( $this->field->validate() );

		$this->field->set_value( [ 2, 1 ] );
		$this->assertTrue( $this->field->validate() );
		$this->assertSame( [ 1, 2 ], $this->field->get_value() );
	}
}
