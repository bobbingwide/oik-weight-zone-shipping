# Weight zone shipping for WooCommerce 
![banner](assets/oik-weight-zone-shipping-banner-772x250.jpg)
* Contributors: bobbingwide, vsgloik
* Donate link: https://www.oik-plugins.com/oik/oik-donate/
* Tags: shipping, weight, zone, woocommerce, multi rate, shipping classes
* Requires at least: 5.6
* Tested up to: 6.4-RC3
* Stable tag: 0.2.10
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds shipping zone weight based shipping cost calculations to your WooCommerce store.

## Description 

A WooCommerce extension to calculate shipping charges based on cart weight and delivery zone.

* Supports multiple rates per shipping rate table
* Supports shipping class restriction

If your WooCommerce store needs to calculate shipping charges based on cart weight and delivery region then this plugin is for you.

# Documentation 

Each shipping zone can contain multiple shipping methods with rates that apply to all regions in the zone.

# Features 

* Shipping rates based on cart weight and delivery region
* Unlimited weight ranges
* Carts with zero weight
* FREE shipping in selected weight ranges
* Default rates using the Rest of the World shipping zone
* Maximum cart weight
* WooCommerce 2.6 and above
* Internationalised
* Handling fee as fixed rate or percentage of total cart cost
* Supports migration from oik weight/country shipping

## Frequently Asked Questions 

# Installation 
1. Upload 'oik-weight-zone-shipping' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit WooCommerce->Settings->Shipping
1. If migrating from oik weight/country shipping use the Multi Rate Weight Zone tab to perform the migration.
1. Set your delivery rates for each Shipping zone in WooCommerce->Settings->Shipping

# Which version of WooCommerce does this work on? 
Now tested up to WooCommerce 7.9.0

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

Yes. You can either define multiple rates per shipping rate table or
create multiple shipping methods.

For each shipping method you add to a zone set the Method Title to reflect the rates.
e.g. UK second class, UK first class.

# Are there any other FAQs? 

Yes. See [oik weight zone shipping for WooCommerce FAQS](https://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping/?oik-tab=faq)


## Screenshots 
1. Add Shipping Method for Weight Zone
2. Weight Zone Settings - initial display
3. Weight Zone Settings - shipping rates defined
4. Cart totals
5. Checkout shipping rates

## Upgrade Notice 
# 0.2.10 
Supports PHP 8.1 and PHP 8.2, tested with WordPress 6.4-RC1 and WooCommerce 8.2.1

## Changelog 
# 0.2.10 
* Changed: Support PHP 8.1 and PHP 8.2 #34
* Tested: With WordPress 6.4-RC1 and WordPress Multisite
* Tested: With WooCommerce 8.2.1
* Tested: With PHPUnit 9.6
* Tested: With PHP 8.0, PHP 8.1 and PHP 8.2
