<?php // (C) Copyright Bobbing Wide 2017-2024

/**
 * @package oik-weight-zone-shipping
 * 
 * Test logic in oik-weight-zone-shipping
 */
class Tests_calculate_shipping extends BW_UnitTestCase {

	
	/**
	 * 
	 * Pre-requisites 
	 * - WooCommerce
	 * - WooCommerce test code
	 * - oik-weight-zone-shipping to be active
	 * - Live data in the database must match what we've hard coded.
	 * 
	 * @TODO We're using live data from sb/wordpress here. This is only just about good enough until we can generate it dynamically.
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
		//oik_require( "tests/framework/helpers/class-wc-helper-product.php", "woocommerce-source" );
		//oik_require( "tests/legacy/framework/helpers/class-wc-helper-product.php", "woocommerce-source" );
		
		if ( !did_action( "woocommerce_init" ) ) {	
			gob();
		}
		if ( !did_action( "woocommerce_shipping_init" ) ) {
			do_action( "woocommerce_shipping_init" );
		} 
	}
	
	/**
	 * Creates a package to be shipped.
	 * 
	 * We have to simulate a WooCommerce 2.6/3.0 package
	 * with an item in the cart that weighs something 
	 * so that we can determine a shipping cost.
	 */
	function get_package( $qty=1 ) {  
	
		$package = array();
		
		$this->add_to_cart( $qty );
		$package = WC()->cart->get_shipping_packages(); 
		//print_r( $package );
		//$package['destination']['country'] = 'UK';
		//print_r( $package );
		return( $package );
	
	}
	
	/**
	 * Add products to the cart
	 *
	 * Note use of hardcoded product ID.
	 * wc_get_product() returns the current weight of the product
	 * We need to update to 0.1 and save().
	 */
	function add_to_cart( $qty=1 ) {

		//$product = WC_Helper_Product::create_simple_product();
		$product = wc_get_product( 3065 );
		bw_trace2( $product, "product" );
		// set_weight() is in 2.7.. which will become 3.0.0
		$product->set_weight( 0.1 );
		$product->save();
		$id = $product->get_id();
		//print_r( $product );
		WC()->cart->empty_cart();
		// Add the product to the cart. Methods returns boolean on failure, string on success.
		//WC()->cart->add_to_cart( 31631 /* $product->get_id(), 1 );
		// Note:
		// 31631 weighs 100gms. ie. 0.1 kg
		// 30114 weighs 1 kg
		//$id = 3065; // on s.b/wordpress
		//$id = 31631; // on q.w/wordpress
		WC()->cart->add_to_cart( $id, $qty );
	}

	/**
	 * Unit test calculate_shipping
	 *
	 * This is what I wanted to do...
	 * - Start a new instance. How do we find the instance ID? 
	 * - Confirm we're using "oik_weight_zone_shipping" - for oik-weight-zone-shipping not oik-weightcountry-shipping
	 * - Confirm it's enabled
	 * - Create a package to pass
	 * - Calculate shipping
	 *
	 * This is the pragmatic solution, using live data from qw/wordpress
	 * Not really good enough but it will do until we can generate it dynamically.
	 * 
	 * Notes:
	 * - The shipping methods that are loaded depend on the Zone for the package.
	 * - The destination defaults to UK since that is the value for user 0 ( taken from customer billing/shipping address)
	 * - One product weighs 0.1 kg.
	 * - Two products weigh 0.2 kg.
	 *
	 * Shipping rate table is expected to contain, at a minimum:
	 * 0.1 | 3.65 | UK shipping ( 0.1 )
	 * 0.2 | 0.20 | UK shipping ( 0.2 )
	 *
	 * Total cart cost is:
	 *
	 *
	 *
	 *
	 */
	function test_calculate_shipping() {
	
		$shipping = WC_Shipping::instance();
		bw_trace2( $shipping, "WC_shipping::instance" );
		$package = $this->get_package();     
		$packages = $shipping->calculate_shipping( $package );
		bw_trace2( $shipping, "After?" );
		bw_trace2( $packages, "packages");

		$fee = $this->get_fee_used( $shipping );
		if ( '1.23%' === $fee ) {
			$expected_output = 3.77;  // = 3.65 + .12  where .12 is 1.23 percent of 10.00 to 2 decimal places 0.123
		} elseif ( '1.23' === $fee ) {
			$expected_output=4.88;  // = 3.65 + 1.23
		} elseif ( '' === $fee ){
			$expected_output = 3.65;

		} else {
			$expected_output = "Handling fee should be either 1.23% or 1.23 or blank";
		}
		
		$cost = $this->get_calculated_shipping_cost( $shipping );
		$this->assertEquals( $expected_output, $cost );

		// $oik_shipping = new OIK_Weight_Zone_Shipping( 20 );
	 // $this->assertEquals( "oik_weight_zone_shipping", $oik_shipping->id );
	 // bw_trace2( $oik_shipping, "oik_shipping" );
	 // $this->assertEquals( "yes", $oik_shipping->enabled );
      

		//$oik_shipping->calculate_shipping( $package[0] );

		//$rates = $oik_shipping->rates;
		//$cost = $rates['oik_weight_zone_shipping']->cost;
		//bw_trace2( $rates, "rates" );
		
		 	

	}
	
	/**
	 * Test calculate shipping for qty=2
	 */
	function test_calculate_shipping_2() {
		$shipping = WC_Shipping::instance();
		//bw_trace2( $shipping, "WC_shipping::instance" );
		$package = $this->get_package( 2 );     
		$shipping->calculate_shipping( $package );
		bw_trace2( $shipping, "After?" );

		$fee = $this->get_fee_used( $shipping );
		if ( '1.23%' === $fee ) {
			$expected_output = 0.45;  // = 0.20 + .25 where .25 is 1.23% of 10.00 ( .246 ) to two decimal places
		} elseif ( '1.23' === $fee ) {
			$expected_output = 1.43;  // = 0.20 + 1.23
		} elseif ( '' === $fee ){
			$expected_output = 0.20;
		} else {
			$expected_output = "Handling fee should be either 1.23% or 1.23 or blank";
		}
		$cost = $this->get_calculated_shipping_cost( $shipping );
		$this->assertEquals( $expected_output, $cost );
	}

		/*
	  `
    [shipping_methods] => Array
        (
            [20] => OIK_Weight_Zone_Shipping Object
                (
                    [shippingrate_title] => UK shipping ( 0.1 ) wzs
                    [allowed_delimiters:OIK_Weight_Zone_Shipping:private] => Array
                        (
                            [0] => |
                            [1] => /
                        )

                    [dot_rate_delimiters:OIK_Weight_Zone_Shipping:private] => Array
                        (
                            [0] => |
                            [1] => /
                            [2] => ,
                        )

                    [decimal_separator:OIK_Weight_Zone_Shipping:private] => ,
                    [thousand_separator:OIK_Weight_Zone_Shipping:private] => ,
                    [decimals:OIK_Weight_Zone_Shipping:private] => 2
                    [delimiters:OIK_Weight_Zone_Shipping:private] => 
                    [supports] => Array
                        (
                            [0] => shipping-zones
                            [1] => instance-settings
                            [2] => instance-settings-modal
                        )

                    [id] => oik_weight_zone_shipping
                    [method_title] => Weight Zone
                    [method_description] => <p>Lets you charge based on cart weight.</p>

                    [enabled] => yes
                    [title] => Regular Shipping
                    [rates] => Array
                        (
                            [oik_weight_zone_shipping_20] => WC_Shipping_Rate Object
                                (
                                    [id] => oik_weight_zone_shipping_20
                                    [label] => UK shipping ( 0.1 ) wzs
                                    [cost] => 4.88
                                    [taxes] => Array
                                        (
                                        )

                                    [method_id] => oik_weight_zone_shipping
                                    [meta_data:WC_Shipping_Rate:private] => Array
                                        (
                                        )

                                )

                        )
		`
		*/
		
	
	function get_calculated_shipping_cost( $shipping ) {
		$oik_weight_zone_shipping = reset( $shipping->shipping_methods );
		$rates = $oik_weight_zone_shipping->rates;
		$shipping_rate = reset( $rates );
		$cost = $shipping_rate->cost;
		return $cost;
	}

	function get_fee_used( $shipping ) {
		$oik_weight_zone_shipping = reset( $shipping->shipping_methods );
		$fee = $oik_weight_zone_shipping->fee;
		return $fee;
	}
	
}
