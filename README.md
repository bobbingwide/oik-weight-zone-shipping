# Weight zone shipping for WooCommerce 
![banner](https://raw.githubusercontent.com/bobbingwide/oik-weight-zone-shipping/master/assets/oik-weight-zone-shipping-banner-772x250.jpg)
* Contributors: bobbingwide, vsgloik
* Donate link: https://www.oik-plugins.com/oik/oik-donate/
* Tags: shipping, weight, zone, woocommerce, multi rate, shipping classes
* Requires at least: 5.0
* Tested up to: 5.6
* Stable tag: 0.2.3
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
Now tested up to WooCommerce 4.8.0.

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
5. Cart totals
6. Checkout shipping rates

## Upgrade Notice 
# 0.2.3 
Update to fix a problem if some of your products use shipping classes.

# 0.2.2 
Upgrade for internationalized strings for migration.

# 0.2.1 
Upgrade to 0.2.1 to get the two missing files.

# 0.2.0 
Supports migration from oik-weightcountry-shipping. Supports Multiple Rates in each Shipping table and Shipping class restrictions.

# 0.1.4 
Tested with WordPress 5.6 and WooCommerce 4.8.0

# 0.1.3 
Tested with WordPress 5.4, WooCommerce 4.0.1 and PHPUnit 8.

# 0.1.2 
Update if you need handling fee as percentage of total cart cost

# 0.1.1 
Update if you're still using WooCommerce 2.6

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
# 0.2.3 
* Fixed: No shipping options were found when restrict shipping classes is 'no' but there are shipping classes in the cart,https://github.com/bobbingwide/oik-weight-zone-shipping/issues/31
* Changed: Add WC requires at least and WC tested up to values,https://github.com/bobbingwide/oik-weight-zone-shipping/issues/30
* Tested: With WooCommerce 4.9.1

# 0.2.2 
* Changed: Internationlised the strings used in Migration,https://github.com/bobbingwide/oik-weight-zone-shipping/issues/22
* Changed: in bw_term_array() cater for unexpected results from get_terms(),https://github.com/bobbingwide/oik-weight-zone-shipping/issues/29

# 0.2.1 
* Fixed: Added missing files that I forgot to commit to SVN,https://github.com/bobbingwide/oik-weight-zone-shipping/issues/27

# 0.2.0 
* Changed: Implements most of the logic previously available in oik-weight-zone-shipping-pro,https://github.com/bobbingwide/oik-weight-zone-shipping/issues/26
* Tested: With WordPress 5.6
* Tested: With WooCommerce 4.8.0
* Tested: With PHPUnit 8
* Tested: With PHP 7.4

# 0.1.4 
* Changed: Update tests for WooCommerce 4.8.0,https://github.com/bobbingwide/oik-weight-zone-shipping/issues/26
* Tested: With WordPress 5.6
* Tested: With WooCommerce 4.8.0
* Tested: With PHPUnit 8
* Tested: With PHP 7.4

# 0.1.3 
* Changed: Updated tests for PHPUnit 8 https://github.com/bobbingwide/oik-weight-zone-shipping/issues/24
* Tested: With WordPress 5.4
* Tested: With WooCommerce 4.0.1
* Tested: With PHPUnit 8
* Tested: With PHP 7.3 and 7.4

# 0.1.2 
* Changed: Handling fee can now be a percentage of cart total https://github.com/bobbingwide/oik-weight-zone-shipping/issues/21
* Changed: Default to US English and add UK English version https://github.com/bobbingwide/oik-weight-zone-shipping/issues/22
* Tested: With WordPress 4.8.1
* Tested: With WooCommerce 2.6, 3.0 and 3.1

# 0.1.1 
* Fixed: Shipping Rates table incorrectly re-displayed in WooCommerce 2.6 non-modal dialog https://github.com/bobbingwide/oik-weight-zone-shipping/issues/20

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


