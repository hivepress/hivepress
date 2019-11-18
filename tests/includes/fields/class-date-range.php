<?php
/**
 * Date range field test.
 *
 * @package HivePress\Tests\Fields
 */

namespace HivePress\Tests\Fields;

use HivePress\Fields;

/**
 * Date range field test class.
 *
 * @class Date_Range
 */
class Date_Range extends \PHPUnit\Framework\TestCase {

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
		$this->field = new Fields\Date_Range();
	}

	/**
	 * Adds field filters.
	 *
	 * @test
	 */
	public function add_filters() {
		$this->set_value( [ '1000-01-01', '1000-01-02' ] );
		$this->assertSame( 'BETWEEN', $this->field->get_filters()['operator'] );
	}

	/**
	 * Normalizes field value.
	 *
	 * @test
	 */
	public function normalize() {
		$values = [ true, 1, 'value', [], [ '1000-01-01' ], [ '1000-01-01', '1000-01-01', '1000-01-01' ], new \stdClass() ];

		foreach ( $values as $value ) {
			$this->field->set_value( $value );
			$this->assertSame( null, $this->field->get_value() );
		}

		$this->field->set_value( [ '1000-01-02', '1000-01-01' ] );
		$this->assertSame( [ '1000-01-01', '1000-01-02' ], $this->field->get_value() );
	}

	/**
	 * Sanitizes field value.
	 *
	 * @test
	 */
	public function sanitize() {
		$values = [ [ null, null ], [ 1, 2 ], [ '1000-01-01', 2 ], [ 1, '1000-01-01' ] ];

		foreach ( $values as $value ) {
			$this->field->set_value( $value );
			$this->assertSame( null, $this->field->get_value() );
		}

		$this->field->set_value( [ '0', '0' ] );
		$this->assertSame( [ '0', '0' ], $this->field->get_value() );
	}

	/**
	 * Validates field value.
	 *
	 * @test
	 */
	public function validate() {
		$this->field->set_value( null );
		$this->assertTrue( $this->field->validate() );

		$this->field->set_value( [ '1000-01-02', '1000-01-01' ] );
		$this->assertTrue( $this->field->validate() );
		$this->assertSame( [ '1000-01-01', '1000-01-02' ], $this->field->get_value() );
	}
}
