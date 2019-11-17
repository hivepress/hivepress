<?php
/**
 * Email field test.
 *
 * @package HivePress\Tests\Fields
 */

namespace HivePress\Tests\Fields;

use HivePress\Fields;

/**
 * Email field test class.
 *
 * @class Email
 */
class Email extends \PHPUnit\Framework\TestCase {

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
		$this->field = new Fields\Email();
	}

	/**
	 * Sanitizes field value.
	 *
	 * @test
	 */
	public function sanitize() {
		$value = 'a"b(c)d,e:f;gi[j\k]l@example.com';

		$this->field->set_value( $value );
		$this->assertSame( sanitize_email( $value ), $this->field->get_value() );
	}

	/**
	 * Validates field value.
	 *
	 * @test
	 */
	public function validate() {
		$this->field->set_value( null );
		$this->assertTrue( $this->field->validate() );

		$this->field->set_value( 'example@example.com' );
		$this->assertTrue( $this->field->validate() );

		$this->field->set_value( 'example@example' );
		$this->assertFalse( $this->field->validate() );

		$this->field->set_value( 'eqbujptxfckdjykzckylzqfgmaalxsnknoukcoajrmwjnbptsuuvumyfvgilpivpoevotkzohqvjgkhdaxqibzxmtapxdnairomksiyqxsnuhlognrcoabkgfncorlgrvufcthrciuvkgpvuztfvjxfpvwxhrozcswnrcotcbnumkvfwqnieefwepsibmvqzvwjbcamrxwfwtfydokcxtcisllaezgvjaaupftsfyvhipocaumf@example.com' );
		$this->assertFalse( $this->field->validate() );
	}
}
