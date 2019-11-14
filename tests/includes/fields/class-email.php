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
	 * Sanitizes field value.
	 *
	 * @test
	 */
	public function sanitize() {
		$field = new Fields\Email();

		$email = 'a"b(c)d,e:f;gi[j\k]l@example.com';

		$field->set_value( $email );
		$this->assertSame( sanitize_email( $email ), $field->get_value() );
	}

	/**
	 * Validates field value.
	 *
	 * @test
	 */
	public function validate() {
		$field = new Fields\Email();

		$field->set_value( null );
		$this->assertTrue( $field->validate() );

		$field->set_value( 'example@example.com' );
		$this->assertTrue( $field->validate() );

		$field->set_value( 'example@example' );
		$this->assertFalse( $field->validate() );

		$field->set_value( 'eqbujptxfckdjykzckylzqfgmaalxsnknoukcoajrmwjnbptsuuvumyfvgilpivpoevotkzohqvjgkhdaxqibzxmtapxdnairomksiyqxsnuhlognrcoabkgfncorlgrvufcthrciuvkgpvuztfvjxfpvwxhrozcswnrcotcbnumkvfwqnieefwepsibmvqzvwjbcamrxwfwtfydokcxtcisllaezgvjaaupftsfyvhipocaumf@example.com' );
		$this->assertFalse( $field->validate() );
	}
}
