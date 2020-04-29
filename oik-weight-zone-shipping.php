<?php
/**
 * Plugin Name: oik weight zone shipping
 * Plugin URI: https://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping
 * Description: Weight zone shipping for WooCommerce
 * Version: 0.1.3
 * Author: bobbingwide
 * Author URI: https://bobbingwide.com/about-bobbing-wide
 * License: GPL2
 * Text Domain: oik-weight-zone-shipping
 * Domain Path: /languages
 
    Copyright Bobbing Wide 2014-2020 ( email : herb@bobbingwide.com )

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
oik_weight_zone_shipping_loaded();

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
 * @TODO Confirm that the class checking for WC_Shipping_Method is just belt and braces. 
 */
function oik_weight_zone_woocommerce_shipping_init() {
	if ( class_exists( 'WC_Shipping_Method' ) ) {
		load_plugin_textdomain( "oik-weight-zone-shipping", false, 'oik-weight-zone-shipping/languages' );
		if ( !class_exists( "OIK_Weight_Zone_Shipping" ) ) {
			require_once( dirname( __FILE__ ) . "/class-oik-weight-zone-shipping.php" );
		}
  }
}

/**
 * Function to invoke when loaded
 *
 * Only supports WooCommerce 2.6 and higher
 * We need to check the WooCommerce version
 * if WooCommerce is active.
 */
function oik_weight_zone_shipping_loaded() { 
	add_action( "woocommerce_init", "oik_weight_zone_shipping_woocommerce_init" );
}

/** 
 * Implement "woocommerce_init"
 *
 * Only enable the logic if the minimum required version of WooCommerce is active  
 */
function oik_weight_zone_shipping_woocommerce_init() {
	if ( oik_weight_zone_shipping_check_woo_version() ) {
		add_filter( 'woocommerce_shipping_methods', 'oik_weight_zone_woocommerce_shipping_methods' );
		add_action( 'woocommerce_shipping_init', 'oik_weight_zone_woocommerce_shipping_init' );
		add_filter( 'woocommerce_shipping_rate_label', 'oik_weight_zone_shipping_shipping_rate_label', 9 );
	}
}


/**
 * Check the WooCommerce version against the minimum required level
 *
 * Note: The code has been tested against WooCommerce 2.6 and 3.0
 * 
 * @param string $minimum_required Minimum required level
 * @return bool true if the minimum level is active
 */
function oik_weight_zone_shipping_check_woo_version( $minimum_required = "2.6" ) {
	$woocommerce = WC();
	$version = $woocommerce->version;	
	$active = version_compare( $version, $minimum_required, "ge" );
	return( $active );
}

/**
 * Disables the sanitize_text_field filter to allow HTML in the label
 * 
 * 
 * Out of the box, WooCommerce doesn't allow HTML in the label. 
 * We intercept this filter simply to disable the filter that WooCommerce added.
 * The user is responsible for ensuring tags are paired.
 * There's no real need to check if the $label contains any HTML.
 * Removing the filter multiple times is not a problem.
 * 
 * @param string $label which may contain HTML
 * @return string the same
 */
function oik_weight_zone_shipping_shipping_rate_label( $label ) { 
	remove_filter( "woocommerce_shipping_rate_label", "sanitize_text_field" );
	return $label;
}			

if ( !function_exists( "bw_trace2" ) ) {
  function bw_trace2( $p=null ) { return $p; }
	function bw_backtrace() {}
}


