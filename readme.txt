=== Weight zone shipping for WooCommerce 2.6 ===
Contributors: bobbingwide, vsgloik
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: shipping, weight, zone, woocommerce, commerce, ecommerce, shop
Requires at least: 4.5.2
Tested up to: 4.6-beta3
Stable tag: 0.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds shipping zone weight based shipping cost calculations to your WooCommerce store.  

== Description ==

If your WooCommerce store needs to calculate shipping charges based on cart weight and delivery region then this plugin is for you.

= Documentation =
tbc



== Installation ==
1. Upload 'oik-weight-zone-shipping' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Set your delivery rates for each Shipping zone in WooCommerce->Settings->Shipping


== Frequently Asked Questions ==
= Which version of WooCommerce does this work on? =

Tested with WooCommerce 2.6 and higher

= What is the separator for the shipping rate table? = 

You can use vertical bars, forward slashes or commas
Blanks around values will be ignored
`
0| 9.99| Zero weight
1 / 1.24 / 
2 , 3.45 ,
3 | 4.56 
`

Enter one rate per line.


= How do I set the Method Title? =

Set the default Method Title and add overrides in the Shipping Rates table 

`
0| 9.99| Zero weight
1 / 1.24 /  
2 , 3.45 , 
3 | 4.56 , Another method title override
`

= Does this support multiple rates per weight/shipping zone combination? =

Yes. 

Because this plugin is integrated with WooCommerce Shipping Zones you can achieve it by 
creating multiple shipping methods using Weight zone shipping.

For each shipping method you add to a zone set the Method Title to reflect the rates.
e.g. UK second class, UK first class.

If you want to define multiple rates in the Shipping Rate table then you will need to use the
[Multi rate weight zone shipping for WooCommerce plugin](http://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping-pro/),
which is the premium version of the plugin. 


= Are there any other FAQs? =

Yes. See [oik weight zone shipping for WooCommerce 2.6 FAQS](http://www.oik-plugins.com/wordpress-plugins-from-oik-plugins/oik-weight-zone-shipping-faqs)
and [Multi rate weight shipping for WooCommerce 2.6 FAQ's](http://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping-pro/?oik-tab=faq)

== Screenshots ==
1. Add Shipping Method for Weight zone shipping
2. tbc
x. Weight and Country shipping settings part two
y. WooCommerce Checkout shipping rate
z. Enable Shipping Debug Mode when modifying rates

== Upgrade Notice ==

= 0.0.0 = 
Based on oik-weightcountry-shipping.
Tested with WooCommerce 2.6.0 and above and WordPress 4.5.3 and above.


== Changelog ==
= 0.0.0 =
* Added: New plugin cloned from oik-weightcountry-shipping v1.3.2
* Changed: Synchronized with code from oik-weight-zone-shipping-pro v0.0.0
* Changed: readme updated to reflect the move to a new plugin.
* Changed: Renamed language files


