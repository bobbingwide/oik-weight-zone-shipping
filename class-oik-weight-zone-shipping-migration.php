<?php // (C) Copyright Bobbing Wide 2016-2020

/**
 * Implements oik-weight-zone-shipping-pro migration logic
 *
 * Note: We use bw_trace2() and bw_backtrace() but can't use trace levels since we don't know if trace is actually available.  
 */
class OIK_Weight_Zone_Shipping_Migration {

	/**
	 * Stores the migration status
	 */
	public $migration_status; 
	
	/**
	 * 
	 * awd_settings is expected to be a serialised structure containing
	 * 
	 * Fields  | Values
	 * ------- | ----------------------
	 * enabled | yes, no
	 * title   | string Method title
	 * tax_status | none , taxable
	 * fee | numeric
	 * options | Shipping rate table - see $options below
	 * country_group_no | number of country groups 
	 * countries1 | Array of country codes ( sometimes more than 2 digits )
	 * ... | 
	 * countriesn | Array of country codes 
	 *
	 * e.g. 
	 * 
	 a:10:{s:7:"enabled";s:3:"yes";
	 s:5:"title";s:16:"Regular Shipping";
	 s:10:"tax_status";s:4:"none";
	 s:3:"fee";s:0:"";
	 s:7:"options";s:1416:"0|9.99|0| zero weight to elsewhere	...	etcetera"
	 s:16:"country_group_no";s:1:"4";
	 s:10:"countries1";a:2:{i:0;s:2:"PT";i:1;s:2:"GB";}
	 s:10:"countries2";a:1:{i:0;s:2:"FR";}
	 s:10:"countries3";a:2:{i:0;s:2:"CA";i:1;s:2:"US";}
	 s:10:"countries4";a:2:{i:0;s:5:"PT-20";i:1;s:5:"PT-30";}
	 */
	public $awd_settings; 
	
	/** 
	 * Weight/country shipping rate table
	 * 
	 * In the serialized structure this is:
	 * - one per line 
	 * - format: max_weight | rate | country_group | method_title_override																	
	 * - rate may not be non-numeric - indicating maximum weight
	 * 
	 * Here it's stored as an array.
	 */
	public $options;
	
	/**
	 * The defined shipping zones
	 * 
	 */
	public $shipping_zones;
	
	/**
	 * The defined shipping methods
	 */
	public $shipping_methods;
	
	/**
	 * woocommerce_oik_weight_zone_shipping_nn_settings 
	 * 
	 * where nn is the instance ID
	 * 
	 * We need to cater for multiple instances
	 *
	 * We may not need this as we can use WooCommerce APIs to handle the settings and stuff
	 
	 * Fields  | Values
	 * ------- | ----------------------
	 * title   | string Method title
	 * tax_status | none , taxable
	 * fee | numeric
	 * options | Shipping rate table - see below
	 */
	public $oik_settings;
	
	/**
	 * New shipping rate table - for the current instance
	 * 
	 * - one per line
	 * - format: max_weight | rate | method_title_override
	 * 
	 * `
	 * 1,1.00, one pound per pound
		 1, 1.50, faster for a pound
		 2, 2.00, two pounds up to 2 pounds
		 2, 2.50,or another 50p
		 2, 3.00,
	 * `
	 */
	public $oik_shipping_rate_table; 
	
	/**
	 * Array of messages showing what's been going on
	 */  
	public $messages;  

	/**
	 * @var DIY_shortcode_overrides - the true instance
	 */
	private static $instance;

  /**
	 * Return the single instance of this class
	 */
	public static function instance() {
    if ( !isset( self::$instance ) && !( self::$instance instanceof self ) ) {
      self::$instance = new self;
    }
    return self::$instance;
  }
	
	/** 
	 * Constructor for OIK_Weight_Zone_Shipping_Migration
	 * 
	 * First thing we do is to find out if we really need to do anything, or if it's already been done, or didn't need doing in the first place.
	 * 
	 */
  function __construct() {
		$this->messages = array();
		$initial_status = $this->get_migration_status();
		if ( $initial_status == "5" ) {
			$this->query_migration_status();
			$this->update_migration_status();
		}
	}
	
	/**
	 * Display an "update" message
	 * 
	 * @param string $text the message to display
	 * @return string the generated HTML
	 */								 
	function show_update_message( $text ) {
		$message = '<tr class="plugin-update-tr">';
		$message .= '<td colspan="3" class="plugin-update colspanchange">';
		$message .= '<div class="update-message">';
		$message .= $text;
		$message .= "</div>";
		$message .= "</td>";
		$message .= "</tr>";
		echo $message;
	}
	
	/**
	 * Implement "after_plugin_row" for this plugin
	 * 
	 * Quick and dirty solution to decide if data migration is required
	 * and if so produce a link to the Migration settings page.
	 */
	function after_plugin_row( $plugin_file, $plugin_data, $status ) {
		bw_trace2();
		$message = $this->map_migration_status();
		$this->show_update_message( "Migrate data? $message" );
	}
	
	/**
	 * Return the stored migration status
	 * 
	 * Initialise to 5 ( "Unknown" ) then update to the actual value if set
	 */
	function get_migration_status() {
		$this->migration_status = 5;
    $oik_settings = get_option( "woocommerce_oik_weight_zone_shipping_settings" );
		if ( $oik_settings ) { 
			if ( isset( $oik_settings['status'] ) && !empty( $oik_settings['status'] ) ) {
				bw_trace2( $oik_settings, "oik_weight_zone_shipping_settings" );
				$this->migration_status = $oik_settings['status']; 
			}
			if ( isset( $oik_settings['information'] ) ) {
				$this->messages = explode( PHP_EOL, $oik_settings['information'] );
			}
		}
		bw_trace2( $this->migration_status, "this migration_status" );
		return( $this->migration_status );
	}
	
	/**
   * Update the migration status
	 *
	 * @TODO What if not $oik_settings is initially null?
	 * 
	 */
	function update_migration_status() {
		$oik_settings = get_option( "woocommerce_oik_weight_zone_shipping_settings" );
		$oik_settings['status'] = $this->migration_status; 
		$this->add_message( sprintf( __( 'Migration status set to: %1$s', 'oik-weight-zone-shipping' ) , $this->migration_status ) );
		update_option( "woocommerce_oik_weight_zone_shipping_settings", $oik_settings );
	}
	
	
	/** 
	 * Return the migration status in text
	 * 
	 * @TODO Work with the same options as in init_migration_form_fields() 
	 *
	 */	
	function map_migration_status() {
		$states = array( __( "Migration not necessary.", 'oik-weight-zone-shipping' )
									 , __( "Migration not started.", 'oik-weight-zone-shipping' )
									 , __( "Migration in progress.", 'oik-weight-zone-shipping' )
									 , __( "Migration complete. ", 'oik-weight-zone-shipping' )
									 , __( "Migration fully complete.", 'oik-weight-zone-shipping' )
									 , __( "Unknown.", 'oik-weight-zone-shipping' )
									 );
		if ( isset( $states[ $this->migration_status ] ) ) {
			$text = $states[ $this->migration_status ];
		} else {
			$text = sprintf( __( 'Truly unknown status: %1$s', 'oik-weight-zone-shipping' ), $this->migration_status );
		}	
		return( $text );
	}	
	
	/**
	 * Query the migration status
	 * 
	 * We want to spend as little time as possible deciding whether or not to perform migration.
	 * How can we easily tell that migration is complete? 
	 * 
	 * Status  | Meaning        | How determined?
	 * ------- | -------------- | ----------------------
	 * 0       | Not necessary  | No awd_settings data, or enabled=no or no country groups defined.
	 * 1       | Not started    | No Shipping zones defined other than "Rest of the World".
	 * 2       | In progress    | Shipping zones defined but not methods.
	 * 3       | Complete       | Shipping zones and shipping methods defined.
	 * 4       | Fully complete |	woocommerce_awd_shipping_settings enabled="no"
	 * 5       | Unknown        | Nothing has been done yet.
	 *  
	 */
	function query_migration_status() { 
		$this->migration_status = 0;
		//$this->
		$shipping_zones_count = $this->load_shipping_zones();
		if ( $shipping_zones_count ) {
			$shipping_methods_count = $this->load_shipping_methods();
			if ( $shipping_methods_count ) {
				$this->migration_status = 3;
				if ( !$this->query_awd_status() ) {
					$this->migration_status = 4;
        } 
			} else {
				$this->migration_status = 2;
			}
		} else {
			$this->migration_status = $this->query_awd_status();
		}	
	 	return( $this->migration_status );
	}
	
	/**
	 * Return a value for the awd status
	 * 
	 * Value | Means
	 * ----- | ------------
	 * 0     | No awd settings, method not enabled or no country groups defined
	 * 1     | There are settings, the method is enabled and country groups are defined
	 * 
	 * @return integer 
	 */
	function query_awd_status( ) {
		$awd_status = 0;
		$this->awd_settings = get_option( 'woocommerce_awd_shipping_settings' );
		if ( $this->awd_settings ) {
			$enabled = $this->get_enabled();
			if ( $enabled === "yes" ) {
			
				$country_group_no = $this->get_country_group_no();
				if ( $country_group_no ) {
					$awd_status = 1;
				} 
				 
			}
		}
		return( $awd_status );
	}
	
	/**
	 * Map the awd_status code to human understandable
	 *
	 */
	function map_awd_status( $awd_status ) {
		$awd_statuses = array( 0 => __( "Not necessary; no settings, method not enabled, or no country groups defined", 'oik-weight-zone-shipping' )
												 , 1 => __( "Migration required", 'oik-weight-zone-shipping' )
												 );
    $awd_status_text = $awd_statuses[ $awd_status ];
		return( $awd_status_text );
	}
	
	/**
	 * Recommends the next action.
	 *
	 * @return string
	 */
	function recommend_next_action() {
		$next_actions = array( "0" => __( "Complete migration", 'oik-weight-zone-shipping' )
												 , "1" => __( "Perform migration", 'oik-weight-zone-shipping' )
												 , "2" => __( "Perform migration", 'oik-weight-zone-shipping' )
												 , "3" => __( "Complete migration", 'oik-weight-zone-shipping' )
												 , "4" => __( "None", 'oik-weight-zone-shipping' )
												 , "5" => __( "Information", 'oik-weight-zone-shipping' )
												 );
		return( $next_actions[ $this->migration_status ] );
		 												
	}
	
	/**
	 * Return information about migration in a logical form
	 * 
	 * This may not be as efficient as query_migration_status.
	 *
	 * @param object $oikwzsp instance of the OIK_Weight_Zone_Shipping_Multi_Rate class
	 * @return string HTML to display 
	 */
	function information( $oikwzsp ) {
		$this->add_message( sprintf( __( 'Current status is: %1$s', 'oik-weight-zone-shipping' ) , $this->map_migration_status() ) );
		$this->add_message( sprintf( __( 'AWD status is: %1$s', 'oik-weight-zone-shipping' ), $this->map_awd_status( $this->query_awd_status() ) ) );
		$this->add_message( sprintf( __( 'Recommended next action: %1$s', 'oik-weight-zone-shipping' ), $this->recommend_next_action() ) );
		// Update the migration_status
		return( __( "Performed information", 'oik-weight-zone-shipping' ) );
	}
	
	/**
	 * Add a message to the Information field
	 */
	function add_message( $text ) {
		$this->messages[] = $text;
	}
	
	/**
	 * Return all the messages
	 */
	function get_messages() {
		$information = implode( PHP_EOL, $this->messages );
		return( $information );
	}
		
	
	/**
	 * Get count of country groups
	 *	 
	 */ 
	function get_country_group_no() {
		return( $this->awd_setting( 'country_group_no' ) );
	}
	
	/**
	 * Returns the weight/country shipping enabled flag
	 */
	function get_enabled() {
		return( $this->awd_setting( 'enabled' ) );
	}
	
	/**
	 * Returns the method_title
	 *
	 */ 
	function get_method_title() {
		return( $this->awd_setting('title') );
	}
	
	/**
	 * Returns the tax_status
	 *
	 */ 
	function get_tax_status() {
		return( $this->awd_setting('tax_status') );
	}
	
	/**
	 * Returns the fee
	 */
	function get_fee() {
		return( $this->awd_setting( 'fee' ) );
	}
	
	/**
	 * Returns the countries for the country group
	 */
	function get_countries( $cgi ) {
		return( $this->awd_setting( "countries" . $cgi ) );
	}
	
	/**
	 * Return a field value or null
	 * 
	 * @param string $field the option field name
	 * @return string the value of the field, or null
	 */
	function awd_setting( $field ) {
		$value = null;
		if ( isset( $this->awd_settings[ $field ] ) ) {
			$value = $this->awd_settings[ $field ];
		}
		return( $value );
	}
	 
	/** 
	 * extract options for the selected country group number
	 * 
	 * @param integer $country_group_number
	 * @return string $options
	 * 
	 	 
1 | 92.99 | 0 | elsewhere
2 | X | 0 | 
1 | 92.98 | 2 | France
100 | 93.97 | 2 | France
100 | 93.98 | 2 | France alternative rate
150|94.99 |2 | France
150|95.00| 2 | France alternative rate
151 | Z | 2 | Too heavy for France 
0.1 / 3.65 , 1, UK shipping ( 0.1 ) 
0.2 / 0.20 / 1, UK shipping ( 0.2 )
1|10.00| 1 | UK 1 10.00
3| 8.00 | 1 | UK 3 8.00
3| 12.00 | 1 | UK 3 12.00 - expedited
5|42.90|1 | UK 5 42.90
6 | 41.00 | 1 | UK 6 41.00
4 | 13. | 1 | UK 4 13.00
2|15.90|1 | UK 2 15.90
10|59.99|1 | 
20|92.99|1 | UK 20 92.99
30|120.00|1 | UK 30 120.00
40|110.00|1 | UK 40 110.00
50 | 109 | 1 | UK 50 109.00
70 | 0 | 1 | Over 50 &lt;= 70 FREE
80 | 999 | 1 | Over 70 - expensive
100 | 0 | 1 | Over 80 - FREE again
150 | 1.50 | 1 | Cheap shipping for over 100 kgs
151 | X | 1 | Over 150 - too heavy
0| 1.23 | 3
0.8125| 14.95 | 3 | Steven Baker Canada
2.8125 | 24.95 | 3 | Steven Baker Canada
3 | 4.56 , 3
4 | X | 3 | Cart weight exceeded. USA Canada limited to 3 kg
0 | 0 | 1 | Nothing for FREE in UK
1,2.78,1,myHermes ParcelShop
2,3.78,1,myHermes ParcelShop
5,5.79,1,myHermes ParcelShop
10,7.49,1,myHermes ParcelShop
15,9.78,1,myHermes Parcelshop
0,1.23,UK, United Kingdom
.1,6.45,1,Guaranteed by 1pm
.5,7.25,1,Guaranteed by 1pm
1,8.55,1,Guaranteed by 1pm
2,11.00,1,Guaranteed by 1pm
10,26.60,1,Guaranteed by 1pm
20,41.20,1,Guaranteed by 1pm";

	 */
	function extract_options( $country_group ) {
		$this->options = $this->awd_setting( "options" );
		$this->options = (array) explode( "\n", $this->options );
		$options = $this->get_rates_by_countrygroup( $country_group );
		return( $options );
	}
	
	/**
	 * Process the migration
	 * 
	 * @param object $oikwzsp Instance of OIK_Weight_Zone_Shipping_Multi_Rate class
	 * @return string $information
	 */
	function process_migration( $oikwzsp ) {
		$information = null;
		
	  switch ( $oikwzsp->settings['action'] ) {
			case "none": 
			case "info":
				$information = $this->information( $oikwzsp );
				break;
			
			case "migrate":
				$information = $this->perform_migration( $oikwzsp );
				break;
				
			case "complete":
				$information = $this->complete_migration( $oikwzsp );
				break;
				
			default:
				bw_trace2( $oikwzsp->settings, "Invalid action" );
		}
		$information = $this->get_messages();
		return( $information );
	}
	
	
	/**
	 * Perform migration
	 *
	 * We only perform migration if migration has not been started.
	 * 
	 * for each country group
	 * - create a zone named "Country group n"
	 * - copy the countries to the zone
	 * - add our method to the zone - returning the instance_id	- add_shipping_method() then fetch the method instance
	 * - extract rates
	 * - update the options (  woocommerce_oik_weight_zone_shipping_nn_settings - where nn is the instance_id  
	 * - save 
	 * 
	 * for country group 0
	 * - We use "Rest of the World" - zone_id 0
	 * - No need to add countries
	 * - But we do need to add the method
	 * - copy the rates
	 * - BUT don't save the zone - see https://github.com/woothemes/woocommerce/issues/11688
	 *
	 * @param object $oikwzsp Instance of OIK_Weight_Zone_Shipping_Multi_Rate
	 * 
	 */
	function perform_migration( $oikwzsp ) {
		$country_group_no = $this->get_country_group_no();
		$this->add_message( sprintf( __( 'Number of country groups: %d', "oik-weight-zone-shipping" ), $country_group_no ) );
		for ( $cgi = 1; $cgi <= $country_group_no; $cgi++ ) {
			$countries = $this->get_countries( $cgi );
			if ( is_array( $countries) && count( $countries ) ) {
				$zone_id = $this->create_shipping_zone( $cgi );
				$zone = new WC_Shipping_Zone( $zone_id );
				$this->create_locations( $zone, $countries );
				$method = $this->add_shipping_method( $zone );
				//$method->method_title = 
				$this->migrate_awd_settings( $method, $cgi );
				$zone->save();
			} else {
				$this->add_message( sprintf( __( 'No countries in Country Group %d.', 'oik-weight-zone-shipping' ), $cgi ) );
			}	
		}
		
		// Migrate data for Country Group 0 to the "Rest of the World"
		$zone = new WC_Shipping_Zone( 0 );
		//bw_trace2( $zone, "Rest of the World?", false );
		$method = $this->add_shipping_method( $zone );
		$this->migrate_awd_settings( $method, 0 );
			
	}
	
	/**
	 * Add the shipping method
	 *
	 * If not already defined with the method title for AWD shipping
	 */
	function add_shipping_method( $zone ) {
		$method = $this->query_shipping_method( $zone );
		if ( !$method ) {
			$instance_id = $zone->add_shipping_method( "oik_weight_zone_shipping" );
			$this->add_message( sprintf( __( 'Added shipping method instance: %1$s', 'oik-weight-zone-shipping' ) , $instance_id ) );
			$methods = $zone->get_shipping_methods();
			$method = $methods[ $instance_id ];
		}
		return( $method );	 
	}
	
	/**
	 * Returns the matching shipping method
	 *
	 * @param object $zone
	 * @return object WC_Shipping_Method object
	 */
	function query_shipping_method( $zone ) { 
		$method = null;
		//$method_title = $this->get_method_title(); 
		  
		$shipping_methods = $zone->get_shipping_methods();
		bw_trace2( $shipping_methods, "shipping_methods" );
		foreach ( $shipping_methods as $shipping_method ) {
			if ( $shipping_method->id == "oik_weight_zone_shipping" ) {
				$this->add_message( sprintf( __( 'Shipping method instance: %1$s', 'oik-weight-zone-shipping' ),  $shipping_method->instance_id	) );
				$method = $shipping_method;
			}
		}
		return( $method );
	}
	
	/**
	 * Update the instance settings for the method
	 *
	         [title] => (string) "Weight zone shipping"
        [rates] => Array
        [tax_status] => (string) "taxable"
        [fee] => (string) ""
  
	 * 
	 */
	function migrate_awd_settings( $method, $cgi ) {
		$method->instance_settings['title'] = $this->get_method_title();
		$method->instance_settings['tax_status'] = $this->get_tax_status();
		$method->instance_settings['fee'] = $this->get_fee();
		$method->instance_settings['rates'] = $this->get_rates( $cgi );
		update_option( $method->get_instance_option_key(), $method->instance_settings );
	}
	
	/**
	 * Get the new rates table
	 * 
	 * 
	 * @param integer $cgi Country Group Index
	 * @return array $rates_table
	 * 
	 */
	function get_rates( $cgi ) {
		$rates = $this->extract_options( $cgi );
		$rates_table = array();
		foreach ( $rates as $rate ) {
			unset( $rate[2] );
			$rates_table[] = $rate;
		}
		return $rates_table;
	}
	
	/**
	 * Retrieves all rates available for the selected country group
	 *
	 * Now supports separators of '/' forward slash and ',' comma as well as vertical bar
	 * Also trims off blanks.
	 *
	 * @param integer $country_group 
	 * @return array $countrygroup_rate - the subset of options for the given country group returned in array form
	 */
	function get_rates_by_countrygroup( $country_group = null ) {
		$countrygroup_rate = array();
		if ( sizeof( $this->options ) > 0) {
			foreach ( $this->options as $option => $value ) {
				$value = trim( $value );
				$value = str_replace( array( "/", "," ), "|", $value );
				$rate = explode( "|", $value );
				foreach ( $rate as $key => $val ) {
					$rate[$key] = trim( $val );
				}
				if ( isset( $rate[2] ) && $rate[2] == $country_group ) {
					if ( !isset( $rate[3] ) ) {
						$rate[3] = null;
					}
					$countrygroup_rate[] = $rate;
					$this->set_countrygroup_title( $rate );
				}
			}
		}  
		return( $countrygroup_rate );
	}
	
	/**
	 * Dummy method 
	 * 
	 * Using this dummmy method means we can use an unchanged version of get_rates_by_countrygroup() from oik-weightcountry-shipping.
	 * Note: There was a bug in oik-weightcountry-shipping where non-numeric country group numbers were treated as though they were in country group 0.
	 * As we're using the same code the situation is unchanged.  
	 * 
	 * @param array $rate 
	 */ 
	function set_countrygroup_title( $rate ) {
	}
	
	/**
	 * Complete the migration
	 * 
	 * Complete the migration by
	 * - setting 
	 */
	function complete_migration( $oikwzsp ) {
		$this->migration_status = 4;
		$this->update_migration_status();
		
		$this->awd_settings['enabled'] = "no";
		update_option( 'woocommerce_awd_shipping_settings', $this->awd_settings );
	}
		 
	 
	
	/**
	 * Load the shipping zones
	 * 
	 * @return integer count of shipping zones - does not include rest of the world
	 */
	function load_shipping_zones() {
		$this->shipping_zones = WC_Shipping_Zones::get_zones();
		bw_trace2( $this->shipping_zones, "Shipping Zones", false );
		return( count( $this->shipping_zones ) );
	}
	
	/**
	 * Load our shipping methods
	 * 
	 * @return integer count of shipping methods
	 */
	function load_shipping_methods() {
		$count = 0;
		$this->shipping_methods = array();
		if ( $this->shipping_zones ) {
			foreach ( $this->shipping_zones as $shipping_zone ) {
				$zone_id = $shipping_zone['zone_id'];
				$zone = new WC_Shipping_Zone( $zone_id );
				$methods = $zone->get_shipping_methods();
				bw_trace2( $methods, "methods", false );
				foreach ( $methods as $method ) {
					if ( is_object( $method ) && ( $method instanceof OIK_Weight_Zone_Shipping_Multi_Rate ) ) {
						$this->shipping_methods[] = $method;
						$count++;
					}
				}
			}
		}
		return( $count );
	}
	
	/**
	 * Return a shipping zone title
	 * 
	 * @param integer $cgi Country Group Index - n 
	 * @return string "Country Group n"
	 */
	function shipping_zone_title( $cgi ) {
		return( sprintf( __( 'Country Group %d', 'oik-weight-zone-shipping' ) , $cgi ) );
	}
	
	/** 
	 * Create a shipping zone
	 *
	 * Find the shipping zone
	 * If not found, add it 
	 * 
	 */ 
	function create_shipping_zone( $cgi ) {
		$zone_id = null;
		$shipping_zone_title = $this->shipping_zone_title( $cgi );
		$this->add_message( sprintf( __( 'Checking for matching shipping zone: %1$s', 'oik-weight-zone-shipping' ), $shipping_zone_title ) );
		bw_trace2( $this->shipping_zones, "this shipping_zones" );
		foreach ( $this->shipping_zones as $zone ) {
			if ( $zone['zone_name'] == $shipping_zone_title ) {
				$zone_id = $zone['zone_id'];
			}
		}
		if ( null === $zone_id ) {
			$zone = new WC_Shipping_Zone();
			$this->add_message( sprintf( __( 'Creating new shipping zone: %1$s', 'oik-weight-zone-shipping' ) , $shipping_zone_title ) );
			$zone->set_zone_name( $shipping_zone_title );
			$zone->save();
			$zone_id = $zone->get_id();
			$this->add_message( "Created zone_id: $zone_id" );
		}
		$this->add_message( "zone_Id: $zone_id" );
		return( $zone_id );
	}
	
	/**
	 * Create the locations that were in the Country Group
	 *
	 * Here we assume that add_location() doesn't create duplicates
	 * ... well, it does, but it then sort of resolves itself later.
	 * 
	 *
	 
	 * @param object $zone a WC_Shipping_Zone object
	 * @param array $locations countries in the country group
	 */
	function create_locations( $zone, $countries ) {
		bw_trace2();
		$locations = array();
		if ( is_array( $countries) && count( $countries ) ) {
			foreach ( $countries as $country ) {
				$locations[] = array( 'code' => $country, 'type' => 'country' );
				$this->add_message( sprintf( __( 'Adding country: %1$s', 'oik-weight-zone-shipping' ) , $country ) );
			}
		}
		$zone->set_locations( $locations, "country" );
	}
	
	 
}

