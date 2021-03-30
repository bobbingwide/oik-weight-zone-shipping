<?php // (C) Copyright Bobbing Wide 2017-2020

/**
 * @package oik-weight-zone-shipping
 * 
 * Tests the simpler methods in oik-weight-zone-shipping
 */
class Tests_class_oik_weight_zone_shipping extends BW_UnitTestCase {

	public $oik_weight_zone_shipping = null;
	
	/**
	 * 
	 * Pre-requisites 
	 * - WooCommerce
	 * - WooCommerce test code
	 * - oik-weight-zone-shipping to be active
	 * - Live data in the database must match what we've hard coded.
	 * 
	 * @TODO We're using live data from qw/wordpress here. This is only just about good enough until we can generate it dynamically.
	 * WooCommerce WC_Helper_product isn't quite enough for our needs.
	 */
	function setUp(): void {
		parent::setUp();
		oik_require( "oik-weight-zone-shipping.php", "oik-weight-zone-shipping" );
		
		if ( !class_exists( 'WC_Shipping_Method' ) ) {
			oik_require( "includes/abstracts/abstract-wc-shipping-method.php", "woocommerce" );
		} else {
			//echo . PHP_EOL .  'good' . PHP_EOL;
		}
		if ( !$this->oik_weight_zone_shipping ) {
			$this->oik_weight_zone_shipping = new OIK_Weight_Zone_Shipping();
		}
	}
	
	/**
	 * 
	 * When there's no instance passed on the new then some values won't be set.
	 * How can we use this to fiddle with the results to ease testing?
	 */ 
	
	function test_setUp_worked() {
		$this->assertInstanceOf( 'OIK_Weight_Zone_Shipping', $this->oik_weight_zone_shipping );
		//$this->assertEquals( $this->oik_weight_zone_shipping->decimal_separator, "." );
		//$this->assertEquals( $this->oik_weight_zone_shipping->thousand_separator, "," );
		$this->assertEquals( "oik_weight_zone_shipping", $this->oik_weight_zone_shipping->id );
		$this->assertEquals( "", $this->oik_weight_zone_shipping->fee );
	}
	
	/**
	 * Tests that the thousand and decimal separators are OK for us.
	 */
	function test_set_acceptable_separators() {
		$acceptable = $this->oik_weight_zone_shipping->acceptable_separators();
		$this->assertTrue( $acceptable );
	}
	
	/**
	 * Performs round trip conversion of numbers
	 * and compares before and after.
	 * 
	 * 
	 */
	function test_decimal_numbers() {
		//$decimal_separator = wc_get_price_decimal_separator();
		//$thousand_separator = wc_get_price_thousand_separator();
		$numbers = array( 1234.56, 0.00, "X" );
		foreach ( $numbers as $number ) {
			$converted = $this->oik_weight_zone_shipping->price( $number );
			$actual_output = $this->oik_weight_zone_shipping->get_value_as_decimal( $converted );
			$this->assertEquals( $number, $actual_output );
		}
	}
	
	function rates_array() {
		$rates = array();
		$rates[] = array( "0.1", "1234.56", "Fred" ); 
		$rates[] = array( "0.2", "2.00", "Derf" );
		return $rates;
	}
	
	 
	/**
	 * Tests that we can convert a rates array to options and back again.
	 * 
	 * We have another test for the original migration from the options string to a rates array.
	 *
	 */
	function test_rate_option_round_trip() {
		$rates = $this->rates_array();
		$options = $this->oik_weight_zone_shipping->rates_array_to_display( $rates );
		bw_trace2( $options, "options", false );
		$actual_output = $this->oik_weight_zone_shipping->convert_rates_display_to_rates_table( $options );
		$this->assertEquals( $rates, $actual_output );
	}
	
	/**
	 * Simulates converting the previously stored options to a rates array
	 * 
	 *
	 * 
	 */
	function test_get_rates_table_from_string() {
		$string_rate = " 0.1 | 1234.56 / Fred\n 0.2 , 2.00 , Derf";
    $this->oik_weight_zone_shipping->instance_settings['rates_table'] = $string_rate;
    $actual = $this->oik_weight_zone_shipping->get_rates();
		$expected = $this->rates_array();
		$this->assertEquals( $expected, $actual );
	}
	
	/**
	 * Note: PHP's number_format function ignores the 
	 * "common convention for formatting thousands after the decimal point
	 */
	
	function test_number_format() {
	
		$result = number_format( 123456.7890123, 8, ".", "," );
		//$this->assertEquals( "123,456.789,012,3", $result );
		$this->assertEquals( "123,456.78901230", $result );
	
	} 
	
	
	/**
	 * Tests the handling fee calculation which can now be a percentage of the total cost
	 * 
	 * Here we rely on contents_cost being public. Who defines fee?
	 */
	function test_handling_fee() {
	
		$this->oik_weight_zone_shipping->fee = "";
		$this->oik_weight_zone_shipping->contents_cost( 100 );
		$fee = $this->oik_weight_zone_shipping->handling_fee();
		$this->assertEquals( 0, $fee );
		
		$this->oik_weight_zone_shipping->fee = "1.23";
		$this->oik_weight_zone_shipping->contents_cost( 100 );
		$fee = $this->oik_weight_zone_shipping->handling_fee();
		$this->assertEquals( 1.23, $fee );
		
		$this->oik_weight_zone_shipping->fee = "10%";
		$this->oik_weight_zone_shipping->contents_cost( 100 );
		$fee = $this->oik_weight_zone_shipping->handling_fee();
		$this->assertEquals( 10.0, $fee );
		
		$this->oik_weight_zone_shipping->fee = "1.23%";
		$this->oik_weight_zone_shipping->contents_cost( 9.87 );
		$fee = $this->oik_weight_zone_shipping->handling_fee();
		$expected = 0.121401;
		$this->assertEquals( $expected, $fee );

		$this->oik_weight_zone_shipping->fee = "1.23%";
		$this->oik_weight_zone_shipping->contents_cost( 10.00 );
		$fee = $this->oik_weight_zone_shipping->handling_fee();
		$expected = 0.123;
		$this->assertEquals( $expected, $fee );

		$this->oik_weight_zone_shipping->fee = "1.23%";
		$this->oik_weight_zone_shipping->contents_cost( 20.00 );
		$fee = $this->oik_weight_zone_shipping->handling_fee();
		$expected = 0.246;
		$this->assertEquals( $expected, $fee );
	}
	
	function test_assert_equals_message() {
		$fee = 0.121041;
		$this->assertEqualsWithDelta( $fee, 0.121041,0.00001 );
	}
}
