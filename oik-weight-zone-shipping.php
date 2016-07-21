<?php
/**
 * Plugin Name: oik weight zone shipping
 * Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping
 * Description: Weight zone shipping for WooCommerce 2.6
 * Version: 0.0.0
 * Author: bobbingwide
 * Author URI: http://www.oik-plugins.com/author/bobbingwide
 * License: GPL2
 * Text Domain: oik-weight-zone-shipping
 * Domain Path: /languages/
 
    Copyright Bobbing Wide 2014-2016 ( email : herb@bobbingwide.com ) 

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Implement 'woocommerce_shipping_methods' filter for oik-weight-zone-shipping
 *
 * @param array $methods array of shipping method classes
 * @return array array with "OIK_Weight_Zone_Shipping" included
 */  
function oik_weight_zone_woocommerce_shipping_methods( $methods ) {
	$methods['oik_weight_zone_shipping'] = 'OIK_Weight_Zone_Shipping';
	return $methods;
}

/**
 * Implement 'woocommerce_shipping_init' to load l10n versions and then initialise weight zone shipping
 * 
 */
function oik_weight_zone_woocommerce_shipping_init() {
	if ( class_exists( 'WC_Shipping_Method' ) ) {
		load_plugin_textdomain( "oik-weight-zone-shipping", false, 'oik-weight-zone-shipping/languages' );
		require_once( dirname( __FILE__ ) . "/class-oik-weight-zone-shipping.php" );
  }
}
	
add_filter( 'woocommerce_shipping_methods', 'oik_weight_zone_woocommerce_shipping_methods' );
add_action( 'woocommerce_shipping_init', 'oik_weight_zone_woocommerce_shipping_init' );

if ( !function_exists( "bw_trace2" ) ) {
  function bw_trace2( $p=null ) { return $p; }
	function bw_backtrace() {}
}


