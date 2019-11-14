<?php
/**
 * URL field test.
 *
 * @package HivePress\Tests\Fields
 */

namespace HivePress\Tests\Fields;

use HivePress\Fields;

/**
 * URL field test class.
 *
 * @class URL
 */
class URL extends \PHPUnit\Framework\TestCase {

	/**
	 * Sanitizes field value.
	 *
	 * @test
	 */
	public function sanitize() {
		$field = new Fields\URL();

		$url = 'a"b(c)d,e:f;gi[j\k]l';

		$field->set_value( $url );
		$this->assertSame( esc_url_raw( $url ), $field->get_value() );
	}

	/**
	 * Validates field value.
	 *
	 * @test
	 */
	public function validate() {
		$field = new Fields\URL();

		$field->set_value( null );
		$this->assertTrue( $field->validate() );

		$field->set_value( 'uncgmsfbczsrmbpnwbcqixuquzgwxvzplrwjdhrrorjiiogbovjspzkkbmjjkhaiwhyeapicfesdjetpjzvesabzpgyrghpvkpsqizjkzkmmhrsxqjozgvskbnqaoigbktghfnpbodvvseozouhvvyjgwicokjkpavpwcibievjcajjkaqbphmkesbtjnhoaixczddlrzkhocwatsucrtvrxhulpkhmldukgibmyneapxcxvxodriojohjcxzmnmjkziidflpnftuhgxyvxrfaudicfznwwxsfbkivshuttehwmkjvzcqujirdkdzyxvhbljarmzcvhfgierlckyklyxmqxzjrizarkadvgwoahbjoffrfeuauptszftlbrrohdeybvzqmxbbqbvqgwkiivctqfvmatmzmriuioyepaexntfwwjarnaauhoepzdyemukyzxueentdhxmcirmuliignmpaegbbeamxlvvxsfghwqmqmdqykzxoijbdooqlisbxgotnfoqumxtczqnprvictdqjufqqosprcwpowjuoodmhkzotiaqnpvxllyzdtapzmluopmawszuozjhvqumifdnxgkqptiivzmjtpoullwmgjjgyobvhhgnwkaxaesxwtsmyhwjevhabrpozvkiqjmnkpnqqexjxsbaljnbjccjnahfmsxzhcxxzkdhsnprrtubmwgtgnrrisddrarqzoshrsxyilengxcgstyjtxgaixrwrqvfuauiriuosjoayefyzwnwygmbmhyhvgujuuabicxyeahefpgfukvndhsvyiwzqcfihpnkktkyqocxjsgzyzfripneqtogqqnjklbsjhqggkpwbsrgpbpkazgkydargunbcxhohzujxppuecfwkaaeatlzyziyaiicognmrgxxcjldhbzvdpsdpdtvidvbglltzwxlpietccofonlybirhnnfdqsflubxnjnlzwmydscoijscmdykgkesworbughesiwfbvmnxkpyvyidyymfifxrmpclgimgfmshaydqhnxymbbhmbedbwjtbrqrnjsyyyxncbustkupjbmzfilzniidwbmdhqrbvovtxqrozzpcmligxxrwpbitfjykpljahwiiuicwrphmnvpzepzehojfltudzdilsyxrnzlghsmtnaapghvsjqgdwsasbjujvycxlyfydzhjkbvtzigvdpvuqlypgnuucjyriswdvgykbwftjqbzibdpvvbdksnjdbmcnwsjhcbmhfzamuxkfoladhxhwpzobcishjuguqfpuhklqqqizenotvwsvleybapjxpduhrhhaimzmxakiztweikrcpaqgtfdfjpxjcmmgejuvhpezlxlcbxjyflkdajgevchdkmejiquttaesonrmhqpwzfzkrjhpvemtuijypppngehlzbcoithgbaklcjzoktqxnphkgleifeidtojwcdcncwdgpvgoltvaccjqjoyewgkhsjamdxjxzhutjajhebvsgbwocmdcgpdpuvxfothwyohcudkqkvdqfsqxkejvkniomgczxfotjncldcqaoyyclbiqdnbngnqqjjaslhvpqpbxqkxgyuotrmhytxrbvbpzaxgjijowuilwnrkbhvzhanorzguprpnqjytttpkqeoklcihwiqwxpzzuujtxeedlzzthzcpipfhrejcxrvdfpxdmuopuiinzuyjykuettyhjnybynkgxvcocuhqerrfxlasvqonjqozyyqjmvmoayqxjnehzcokferearmjzpbsgwpiylpelfixjmuygkeofhhcoifpsnoeuyjsafqvneolajgxfhkhgkfuazxgtskignfcvfuvqbybqalclzekyqnrroranacfjrqxqkrhjewsckanjerweaolaruefitblbrlyqcojejminqpazkpyyunkwksqdvcuuibbilvcroqnyzmapkspqhtpdwvmebqigfsvrdkoyqqrlqxqcnqinvhljeetmxqeikogasswq' );
		$this->assertFalse( $field->validate() );
	}
}
