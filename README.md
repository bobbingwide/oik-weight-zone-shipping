# Weight zone shipping for WooCommerce 
![banner](https://raw.githubusercontent.com/bobbingwide/oik-weight-zone-shipping/master/assets/oik-weight-zone-shipping-banner-772x250.jpg)
* Contributors: bobbingwide, vsgloik
* Donate link: https://www.oik-plugins.com/oik/oik-donate/
* Tags: shipping, weight, zone, woocommerce
* Requires at least: 4.5.2
* Tested up to: 4.7.5
* Stable tag: 0.1.0
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds shipping zone weight based shipping cost calculations to your WooCommerce store.

## Description 

If your WooCommerce store needs to calculate shipping charges based on cart weight and delivery region then this plugin is for you.

# Documentation 

This plugin replaces the oik-weightcountry-shipping plugin. Designed to work with WooCommerce 2.6 and 3.0, it is integrated with shipping zones.

Each shipping zone can contain multiple shipping methods with rates that apply to all regions in the zone.

# Features 

* Shipping rates based on cart weight and delivery region
* Unlimited weight ranges
* Carts with zero weight
* FREE shipping in selected weight ranges
* Default rates using the Rest of the World shipping zone
* Maximum cart weight
* WooCommerce 2.6, 3.0 and above
* Available in English, French and Swedish


## Frequently Asked Questions 

# Installation 
1. Upload 'oik-weight-zone-shipping' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Set your delivery rates for each Shipping zone in WooCommerce->Settings->Shipping

# Which version of WooCommerce does this work on? 

Tested with WooCommerce 2.6, WooCommerce 3.0 and higher.

# What is the separator for the shipping rate table? 

Vertical bars. Blanks around values will be ignored.

```
0 | 9.99| Zero weight
1 | 1.24 |
2 | 3.45 |
3 | 4.56
```

* Use of slash characters are no longer supported as these are now allowed in the Method Title override.
* Whether or not you can use a comma depends on your Currency options.
* If your Thousand or Decimal separator is a comma then it can't be used as a field delimiter.
* Enter one rate per line.


# How do I set the Method Title? 

Set the default Method Title and add overrides in the Shipping Rates table

```
0 | 9.99| Zero weight
1 | 1.24 |
2 | 3.45 |
3 | 4.56 | Another method title override
10 | 0 | Free shipping between 3 and 10 kgs
999 | ZZZ | Maximum cart weight is 10 kgs
```

# Does this support multiple rates per weight/shipping zone combination? 

Yes - from version 0.0.2

Because this plugin is integrated with WooCommerce Shipping Zones you can achieve it by
creating multiple shipping methods using Weight zone shipping.

For each shipping method you add to a zone set the Method Title to reflect the rates.
e.g. UK second class, UK first class.

If you want to define multiple rates in the Shipping Rate table then you will need to use the
[Multi rate weight zone shipping for WooCommerce plugin](https://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping-pro/),
which is the premium version of the plugin.


# Are there any other FAQs? 

Yes. See [oik weight zone shipping for WooCommerce FAQS](https://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping/?oik-tab=faq)
and [Multi rate weight shipping for WooCommerce FAQ's](https://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping-pro/?oik-tab=faq)


## Screenshots 
1. Add Shipping Method for Weight Zone
2. Weight Zone Settings - initial display
3. Weight Zone Settings - shipping rates defined
5. Cart totals
6. Checkout shipping rates

## Upgrade Notice 
# 0.1.0 
Now supports local currency format and HTML in the shipping method title override.

# 0.0.3 
Tested with WooCommerce 3.0, WordPress 4.7.3 and PHP 7.1

# 0.0.2 
Contains a fix so that multiple weight zone shipping methods can be implemented.

# 0.0.1 
Checks for WooCommerce 2.6 or higher

# 0.0.0 
Based on oik-weightcountry-shipping.
Tested with WooCommerce 2.6.0 and above and WordPress 4.5.3 and above.


## Changelog 
# 0.1.0 
* Changed: Add logic to format weight; using WooCommerce functions
* Changed: Added Swedish language. Props @jyourstone
* Changed: Improve styling of the Shipping rates textarea field https://github.com/bobbingwide/oik-weight-zone-shipping/issues/19
* Changed: Reconcile with oik-weight-zone-shipping-pro https://github.com/bobbingwide/oik-weight-zone-shipping/issues/3
* Changed: Support HTML in the method title override https://github.com/bobbingwide/oik-weight-zone-shipping/issues/18
* Changed: Supports local currency separators in Cost and Fee fields https://github.com/bobbingwide/oik-weight-zone-shipping/issues/11
* Tested: with WooCommerce 3.0, WordPress 4.7.4 and PHP 7.1 https://github.com/bobbingwide/oik-weight-zone-shipping/issues/14

# 0.0.3 
* Tested: With WooCommerce 3.0, [Issue 14](https://github.com/bobbingwide/oik-weight-zone-shipping/issues/14)
* Tested: With WordPress 4.7.3 and WordPress Multisite
* Tested: With PHP 7.1

# 0.0.2 
* Fixed: Support multiple rates using multiple methods [Issue 6](https://github.com/bobbingwide/oik-weight-zone-shipping/issues/6)
* Tested: With WordPress 4.6.1 and WooCommerce 2.6.7
* Updated: readme links

# 0.0.1 
* Changed: Move shipping rates before tax status [Issue 1](https://github.com/bobbingwide/oik-weight-zone-shipping/issues/1)
* Changed: Share OIK_Weight_Shipping class with oik-weight-zone-shipping-pro [Issue 3](https://github.com/bobbingwide/oik-weight-zone-shipping/issues/3)
* Changed: Implement checks for WooCommerce 2.6 or higher [Issue 4](https://github.com/bobbingwide/oik-weight-zone-shipping/issues/4)
* Changed: Update language files [Issue 5](https://github.com/bobbingwide/oik-weight-zone-shipping/issues/5)
* Changed: Updated readme: description, screenshots and captions

# 0.0.0 
* Added: New plugin cloned from oik-weightcountry-shipping v1.3.2
* Changed: Synchronized with code from oik-weight-zone-shipping-pro v0.0.0
* Changed: readme updated to reflect the move to a new plugin.
* Changed: Renamed language files


