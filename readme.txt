=== Weight Zone Shipping for WooCommerce 2.6 ===
Contributors: bobbingwide, vsgloik
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: woocommerce, commerce, ecommerce, shipping, weight, country, shop
Requires at least: 4.5.2
Tested up to: 4.6-beta
Stable tag: 0.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds shipping zone weight based shipping cost calculations to your WooCommerce store.  

== Description ==

If your WooCommerce store needs to calculate shipping charges based on cart weight and delivery region then this plugin is for you.

= Documentation =


== Installation ==
1. Upload 'oik-weight-zone-shipping' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Set your delivery rates in WooCommerce->Settings->Shipping using the Weight/Country tab


== Frequently Asked Questions ==
= Which version of WooCommerce does this work on? =

Tested with WooCommerce 2.0 up to WooCommerce 2.4.11

= What is the separator for the shipping rate table? = 

You can use vertical bars, forward slashes or commas.
Blanks around values will be ignored.
`
0|9.99|0
1 | 92.99 | 0
1 | 92.98 | 2
100 | 93.97 | 2
30|120.00|1
0| 1.23 | 3
1 / 1.24 / 3
2 , 3.45 , 3

`

= How do I set the Method Title? = 
If you want to use a different title per rate then add this for each rate where the Method Title should be different from the default. 
`
0|9.99|0 | Unknown destination - zero weight
1 | 92.99 | 0 | Country group 0
1 | 92.98 | 2
100 | 93.97 | 2
30|120.00|1
0| 1.23 | 3
1 / 1.24 / 3
2 , 3.45 , 3 / CG3 

`

= Does this support multiple rates per weight/shipping zone combination? =

Yes.

There are two ways that this can be achieved.

Using the [Multi rate weight zone shipping for WooCommerce plugin](http://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping-pro/),
a premium plugin, you can define multiple rates per weight range in the Shipping Rates options field.

e.g.
`
100 | 1.23 | UK standard
100 | 2.34 | UK premium
`

But because this plugin is integrated with WooCommerce Shipping Zones you can also achieve it by 
creating multiple shipping methods using Weight zone shipping.

So for this example you'd call the first method "UK standard" and the second "UK premium". 

It's your choice.



= Are there any other FAQs? =

Yes. See [oik weight/country shipping FAQS](http://www.oik-plugins.com/wordpress-plugins-from-oik-plugins/oik-weight-zone-shipping-faqs)
and [Multi rate weight/country shipping for WooCommerce FAQ's](http://www.oik-plugins.com/oik-plugins/oik-weight-zone-shipping-pro/?oik-tab=faq)

== Screenshots ==
1. Add Shipping Method for Weight zone shipping
2. 

2. Weight and Country shipping settings part two
3. WooCommerce Checkout shipping rate
4. Enable Shipping Debug Mode when modifying rates

== Upgrade Notice ==

= 0.0.0 = 
Based on oik-weightcountry-shipping.
Tested with WooCommerce 2.6.0 and above and WordPress 4.5.3 and above.


== Changelog ==
= 0.0.0 =
* Added: New plugin cloned from oik-weightcountry-shipping v1.3.2
* Changed: readme updated to reflect move to a new plugin.


