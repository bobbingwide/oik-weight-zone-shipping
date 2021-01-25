<?php
/**
 * Plugin Name: oik weight zone shipping
 * Plugin URI: https://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping
 * Description: Weight zone shipping for WooCommerce
 * Version: 0.2.2
 * Author: bobbingwide
 * Author URI: https://bobbingwide.com/about-bobbing-wide
 * License: GPL2
 * Text Domain: oik-weight-zone-shipping
 * Domain Path: /languages
 * WC requires at least: 2.6
 * WC tested up to: 4.9.1
 
    Copyright Bobbing Wide 2014-2021 ( email : herb@bobbingwide.com )

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
 * @return array array with "OIK_Weight_Zone_Shipping_Multi_rate" included.
 */  
function oik_weight_zone_woocommerce_shipping_methods( $methods ) {
	$methods['oik_weight_zone_shipping'] = 'OIK_Weight_Zone_Shipping_Multi_Rate';

	return $methods;
}

/**
 * Implement 'woocommerce_shipping_init' to load l10n versions and then initialise weight zone shipping
 * 
 * The class checking for WC_Shipping_Method is just belt and braces.
 */
function oik_weight_zone_woocommerce_shipping_init() {
	if ( class_exists( 'WC_Shipping_Method' ) ) {
		load_plugin_textdomain( "oik-weight-zone-shipping", false, 'oik-weight-zone-shipping/languages' );
		if ( !class_exists( "OIK_Weight_Zone_Shipping" ) ) {
			require_once( dirname( __FILE__ ) . "/class-oik-weight-zone-shipping.php" );
		}
		if ( !class_exists( "OIK_Weight_Zone_Shipping_Multi_Rate" ) ) {
			require_once( dirname( __FILE__ ) . "/class-oik-weight-zone-shipping-multi-rate.php" );
		}
	}
}
  
/**
 * Implement 'woocommerce_shipping_methods' filter for oik-weight-zone-shipping-pro
 *
 * Note: This filter is implemented with a higher priority value than oik-weight-zone-shipping so that it overrides the free version.
 * The user doesn't need the free ( single rate ) version when the multi-rate version is active. 
 * It would probably only confuse if both methods were available.
 *
 * @param array $methods array of shipping method classes
 * @return array array with "OIK_shipping" included
 */  
function oik_weight_zone_shipping_multi_rate_woocommerce_shipping_methods( $methods ) {
	$methods['oik_weight_zone_shipping'] = 'OIK_Weight_Zone_Shipping_Multi_Rate';
	return $methods;
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
 * Implements "woocommerce_init".
 *
 * Only enable the logic if the minimum required version of WooCommerce is active.
 */
function oik_weight_zone_shipping_woocommerce_init() {
	if ( oik_weight_zone_shipping_check_woo_version() ) {
		add_filter( 'woocommerce_shipping_methods', 'oik_weight_zone_woocommerce_shipping_methods' );
		add_action( 'woocommerce_shipping_init', 'oik_weight_zone_woocommerce_shipping_init' );
		add_filter( 'woocommerce_shipping_rate_label', 'oik_weight_zone_shipping_shipping_rate_label', 9 );
	}
}

/**
 * Checks the WooCommerce version against the minimum required level.
 *
 * Note: The code has been tested against WooCommerce 2.6 and 3.0 up to 4.8.0
 * 
 * @param string $minimum_required Minimum required level.
 * @return bool true if the minimum level is active.
 */
function oik_weight_zone_shipping_check_woo_version( $minimum_required = "2.6" ) {
	$woocommerce = WC();
	$version = $woocommerce->version;	
	$active = version_compare( $version, $minimum_required, "ge" );
	//bw_trace2( $active, 'active?' . $version, false );
	return $active;
}

/**
 * Return the migration instance
 * 
 * Fetch the class and get the single instance
 */
function oik_weight_zone_shipping_multi_rate_migration() {
	require_once( dirname( __FILE__ ) . "/class-oik-weight-zone-shipping-migration.php" );
	$oikwzsm = OIK_Weight_Zone_Shipping_Migration::instance();
	return( $oikwzsm );
}

/**
 * Disables the sanitize_text_field filter to allow HTML in the label.
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


