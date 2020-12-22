<?php // (C) Copyright Bobbing Wide 2015-2017

/**
 * Multi rate weight/zone shipping class WooCommerce Extension
 *
 * Implements multiple rate shipping charges by weight and shipping zone
 * 
 * Extends OIK_Weight_Zone_Shipping for the following reasons:
 * - Different method ID 
 * - Different i18n text
 * - Implements multiple rates per weight
 * - Implement Shipping class restrictions
 * 
 * 
 *  
 */
class OIK_Weight_Zone_Shipping_Multi_Rate extends OIK_Weight_Zone_Shipping {

  
	/**
	 * Constructor for OIK_Weight_Zone_Shipping_Multi_Rate class
	 *
	 * Uses the same ID as for the FREE version: 'oik_weight_zone_shipping' 
	 *
	 * Values for supports are:
	 * - shipping-zones Shipping zone functionality + instances
	 * - instance-settings Instance settings screens.
	 * - settings Non-instance settings screens. Enabled by default for BW compatibility with methods before instances existed.
	 * - instance-settings-modal Allows the instance settings to be loaded within a modal in the zones UI.
	 * 
	 * For instance-settings to work we need to set $this->instance_id.
	 * We use "settings" to support the migration logic.
	 *
	 */
	function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );
		bw_trace2();

		
		$this->do_we_need_settings_for_migration();
		$this->method_title = __( 'Multi Rate Weight Zone', 'oik-weight-zone-shipping-pro' );
		$this->method_description = __( 'Lets you charge based on cart weight, supporting multiple rates per weight range in each table.', 'oik-weight-zone-shipping-pro' );
		
		if ( $instance_id === 0 ) {
			//$this->method_title = "Migration";
			//$this->method_description = "Automatic migration from weight/country shipping."; 
			bw_backtrace();
			$this->init_migration_form_fields();
			add_action( 'woocommerce_update_options_shipping_oik_weight_zone_shipping', array( $this, 'process_migration' ) );
			
		}	else {
			$this->admin_page_heading     = __( 'Multi rate weight and zone based shipping', 'oik-weight-zone-shipping-pro' );
			$this->admin_page_description = __( 'Define multiple rates for shipping by weight and zone', 'oik-weight-zone-shipping-pro' );
			add_filter( 'woocommerce_shipping_' . $this->id . '_instance_settings_values', array( $this, "instance_settings_values"), 10, 2 );
		}
		//add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		//$this->init();
		
	}
		
	/**
	 * Determine if we need the "Migration" tab
	 * 
	 * We try to avoid loading the migration class
	 * so we use get_option() to load our non-instance settings
	 * If the 'status' is NOT '4' (fully complete) then we'll need to use the class.
	 *
	 * Note: If the status is '4' (fully complete) then in order to re-enable the logic
	 * we currently have to manually update the settings.
	 *
	 * It could be possible to check if a Weight Country shipping plugin is still active,
	 * but is it really necessary?
	 */
	function do_we_need_settings_for_migration() {
		$migration_class_needed = true;
		$oik_settings = get_option( "woocommerce_oik_weight_zone_shipping_settings" );
		if ( $oik_settings ) { 
			if ( isset( $oik_settings['status'] ) ) {
				bw_trace2( $oik_settings, "oik_weight_zone_shipping_settings" );
				if ( "4" == $oik_settings['status'] ) {
					$migration_class_needed = $this->redo_migration();
				}
			}
		}
		if ( $migration_class_needed ) {
			$this->supports[] = "settings";
		}
	}

	/**
	 * Determines if we should redo the migration.
	 *
	 * We can't call `WC_Shipping_Zones::get_zones()` since it goes recursive.
	 */
	function redo_migration() {
		$data_store = WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();
		bw_trace2( $raw_zones, 'shipping_zones', false);
		$redo = 0 === count( $raw_zones );
		return $redo;
	}

	/**
	 * Return the instance form fields
	 * 
	 * Set desc_tip to true if you want the description to appear as a tip which can be viewed when you hover over the ?
	 * 
	 * 
	 */ 
	function init_form_fields() {
		$six_ninety_five = $this->price( 6.95 ); 
		$three_fifty = $this->price( 3.50 );
		$product_shipping_classes = $this->get_product_shipping_classes();
		$this->instance_form_fields = array(
				'title'      => array(
					'title'       => __( 'Method Title', 'oik-weight-zone-shipping-pro' ),
					'type'        => 'text',
					'description' => __( 'The title which the user sees during checkout, if not defined in Shipping Rates.', 'oik-weight-zone-shipping-pro' ),
					'default'     => __( 'Weight zone shipping', 'oik-weight-zone-shipping-pro' ),
					'desc_tip'    => true,

				),
				
				'rates'       => array(
					'title'       => __( 'Shipping Rates', 'oik-weight-zone-shipping-pro' ),
					'type'        => 'textarea',
					'description' => sprintf( __( 'Set your weight based rates in %1$s for this shipping zone (one per line).<br /> Format: Max weight | Cost | Method Title override<br />Example: 10 | %2$s | Standard rate', 'oik-weight-zone-shipping-pro' ),  get_option( 'woocommerce_weight_unit' ), $six_ninety_five ),
					'default'     => '',
					'desc_tip'    => false,
					'placeholder'	=> __( 'Max weight | Cost | Method Title override', 'oik-weight-zone-shipping-pro' ),
				),
				
				'tax_status' => array(
					'title'       => __( 'Tax Status', 'oik-weight-zone-shipping-pro' ),
					'type'        => 'select',
					'class' 			=> 'wc-enhanced-select',
					'description' => '',
					'default'     => 'taxable',
					'options'     => array(
						'taxable' 	=> __( 'Taxable', 'oik-weight-zone-shipping-pro' ),
						'none' 		=> _x( 'None', 'Tax status', 'oik-weight-zone-shipping-pro' )
						)
				),
				
				
				'fee'        => array(
					'title'       => __( 'Handling Fee (fixed or %)', 'oik-weight-zone-shipping-pro' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Fee excluding tax, e.g. %1$s. Leave blank to disable.', 'oik-weight-zone-shipping-pro' ), $three_fifty ),
					'default'     => '',
					'desc_tip'		=> true,
				),
				
				
				'restrict_shipping_classes' => array( 
					'title' => __( 'Restrict shipping classes', 'oik-weight-zone-shipping-pro' ),
					'type' => 'checkbox',
					'description' => __( 'Enable allowed shipping class checking', 'oik-weight-zone-shipping-pro' ),
					'default' => false
				),
				
				'allowed_shipping_classes' => array(
					'title' => __( 'Allowed shipping classes', 'oik-weight-zone-shipping-pro' ),
					'type' => 'multiselect',
					
					'class' 			=> 'wc-enhanced-select',
					'description' => __( 'Choose the allowed shipping classes when Restrict shipping classes is checked.', 'oik-weight-zone-shipping-pro' ),
					'default' => '',
					'options' => $product_shipping_classes
				),
		);
	}
	
	/** 
	 * Get all the currently registered shipping classes
	 *
	 * Note: If a shipping class has been Deleted then the product_shipping_class
	 * field will not list it any more on the admin page, but it may still be stored in the options table
	 * for the shipping method instance. This isn't a problem.
	 * 
	 * @return array of term ID to term name
	 */
	function get_product_shipping_classes() {
		$args = array( "get" => 'all', "hide_empty" => false );
		$tags = get_terms( 'product_shipping_class', $args );
		bw_trace2( $tags, "tags" );
		$term_array = $this->bw_term_array( $tags );
		return( $term_array );
	}
	
	/**
	 * Build a simple ID, title array from an array of $term objects
	 * 
	 * @param array $terms Array of term objects
	 * @return array mapping ID to name
	 */
	function bw_term_array( $terms ) {
		$options = array();
		//$options = array( 0 => "Any" );
		if ( count( $terms ) ) {
			foreach ($terms as $term ) {
				$options[$term->term_id] = $term->name; 
			}
		}
		return $options;
	}
	
	/**
	 * Return if the method is available
	 * 
	 * The method is not available if 'Restrict shipping classes' is selected 
	 * and one or more of the Shipping classes of the Products in the cart is not in the product_shipping_classes array.
	 * 
	 * @param array $package
	 * @return bool true if the method is available
	 */
	function is_available( $package ) {
		$available = true;
		$restrict_shipping_classes = $this->get_option( "restrict_shipping_classes" );
		if ( $restrict_shipping_classes ) {
			$allowed_shipping_classes = $this->get_option( "allowed_shipping_classes" );
			bw_trace2( $allowed_shipping_classes, "allowed_shipping_classes" );	
			$shipping_classes = $this->get_all_shipping_classes_in_cart( $package['contents'] );
			bw_trace2( $shipping_classes, "shipping_classes", false );
			$available = $this->check_all_shipping_classes_allowed( $shipping_classes, $allowed_shipping_classes );
		}	
		return( $available );
	}

	/**
	 * Retrieve all the shipping classes for the cart
	 *
	 * We ignore shipping class ID of 0, since this means the shipping class is undefined
	 * and we assume that it will always match. 
	 * 
	 * @param array $cart_items
	 * @return array array of shipping class IDs - we don't need the shipping class names
	 */
	function get_all_shipping_classes_in_cart( $cart_items ) {
		$shipping_classes = array();
		foreach ( $cart_items as $cart_item ) {
			//$shipping_class = $cart_item['data']->get_shipping_class();
			//bw_trace2( $shipping_class, "shipping_class", false ); 
			$shipping_class_id = $cart_item['data']->get_shipping_class_id();
			bw_trace2( $shipping_class_id, "shipping_class_id", false ); 
			if ( $shipping_class_id ) {
				$shipping_classes[ $shipping_class_id ] = (string) $shipping_class_id;
			}
		}
		return( $shipping_classes );
	}
	
	/**
	 * Check all shipping classes are allowed
	 *
	 * Note: in array_diff the values are compared as strings. The result set consists of those values from the first array that are not in the second array.
	 * If this array is not empty, then these are the shipping classes that are not allowed.
	 * @TODO Add logic to improve the "No shipping method found" message, if it comes to that.
	 *
	 * @param array $shipping_classes
	 * @param array $allowed_shipping_classes
	 * @return bool true if all shipping classes in the cart are allowed
	 */
	function check_all_shipping_classes_allowed( $shipping_classes, $allowed_shipping_classes ) {
		$all_allowed = true;
		$diff = array_diff( $shipping_classes, $allowed_shipping_classes );
		bw_trace2( $diff, "diff" );
		if ( count( $diff ) )  {
			$all_allowed = false;
		}	
		return( $all_allowed );
	}

	/**
	 * Calculate shipping rates
	 *
	 * Multi-rate shipping allows you to define multiple rates for a selected weight.
	 * 
	 * @param array $package 
	 */
	function calculate_shipping( $package = array() ) {
		bw_trace2( $this );
		$woocommerce = function_exists('WC') ? WC() : $GLOBALS['woocommerce'];
		$rates = $this->get_rates();
		bw_trace2( $rates, "rates", false );
		$weight = $woocommerce->cart->cart_contents_weight;
		bw_trace2( $weight, "cart contents weight" );
		$picked_rates = $this->pick_rates_for_weight( $rates, $weight );
		bw_trace2( $picked_rates, "Picked: " . count( $picked_rates ), false );
		if ( count( $picked_rates ) ) {
			foreach ( $picked_rates as $key => $data ) {
				$final_rate = $data['rate'];
				if ( is_numeric( $final_rate ) ) {
					if ( $this->fee > 0 && $package['destination']['country'] ) {
						 $final_rate += $this->fee;
					}
					$rate = array( 'id' => $this->id . "_" .  $this->instance_id . "_" . $key
											, 'label' => $data['title']
											, 'cost' => $final_rate
											, 'taxes' => ''
											, 'calc_tax' => 'per_order'
											);
					$this->add_rate( $rate );
				} else {
					add_filter( "woocommerce_cart_no_shipping_available_html", array( $this, 'no_shipping_available') );
				}
			} 
		}
	}
 
	/**
	 * Picks a selection of rates from available rates based on cart weight
	 * 
	 * If you want to set a weight at which shipping is free
	 * then set a rate for the weight at the limit, and another way above the limit to 0
	 *
	 * e.g.
	 * `
	 * 50|100.00|  Not free up to and including 50
	 * 999|0.00| Free above 50, up to 999
	 * `
	 * 
	 * Supports multiple rates for the same weight.
	 * This allows you to offer a choice of shipping methods.
	 * 
	 *
	 * e.g.
	 * `
	 * 50 | 1.70 | Second class
	 * 50 | 2.00 | First class
	 * 50 | 100.00 | Knight in shining armour
	 * ` 
	 * 
	 * The weight ranges do not need to match
	 * as the logic now calculates the minimum weight for each range.
	 * 
	 * To set a maximum weight for any shipping method, use a non numeric rate.
	 * `
	 * 77 | X | Too heavy for this shipping method.
	 * `
	 * If the weight is above this highest value then the most expensive rate is chosen.
	 * This is rather silly logic... but it'll do for the moment.
	 * 
	 * We also set the shipping rate title for the selected rates.
	 *  
	 * @TODO Confirm that sorting is no longer required.
	 * 
	 * @param array $rates - array of rates
	 * @param string $weight - the cart weight 
	 * @return - rates - array may be empty if no rates are determined
	 */
	function pick_rates_for_weight( $rates_array, $weight ) {
		bw_trace2();
		$picked_rates = array();
		$max_rate = false;
		$found_weight = -1;
		$found = false;
		if ( sizeof( $rates_array ) > 0) {
			$rates = $this->set_min_weights( $rates_array );
			//$rates = $this->sort_ascending( $new_rates );
			//bw_trace2( $rates, "rates" );
			foreach ( $rates as $key => $value) {
				$max_weight = $value[0];
				$min_weight = $value[3]; 
				if ( ( ( $weight > $min_weight ) || ( 0 == $min_weight ) ) && ( $max_weight >= $weight ) ) {
					$picked_rates[] = array( "rate" => $value[1]
														, "title" => $this->set_shippingrate_title( $value )
														); 
					$found = true;
				}
				if ( !$found  ) {
					if ( !$max_rate || ( is_numeric( $value[1] ) && $value[1] > $max_rate ) ) {
						$max_rate = $value[1];
						$this->set_shippingrate_title( $value );
					}
				}   
			}
		}
		//bw_trace2( $picked_rates, "rates" );  
		if ( count( $picked_rates ) == 0 && $max_rate ) {
			$picked_rates[] =  array( "rate" => $max_rate
												, "title" => $this->shippingrate_title
												);
		}
		return $picked_rates ;
	}
	
  /**
	 * Sets minimum weight for each rate
	 *
	 * Prior to sorting we set the minimum weight from the previous row with a different max weight
	 * resetting the min weight to 0 when the rate is less than the previous. 
	 *
	 *
	 * @param array $rates_array
	 * @return array $new_rates
	 */
	function set_min_weights( $rates_array ) {
		$new_rates = array();
		$previous_min = 0;
		$previous_rate = 0;
		foreach ( $rates_array as $rate ) {
			if ( $rate[0] < $previous_rate ) {
				$previous_min = 0;	
			}
			if ( $rate[0] > $previous_rate ) {
				$previous_min = $previous_rate;  
			}
			$rate[] = $previous_min;
			$new_rates[] = $rate;  
			$previous_rate = $rate[0];
		}
		return $new_rates;
	}
 
	/**
	 * Sort the rates array by ascending weight
	 *
	 * @param array $rates_array array of rates
	 * @return array sorted by ascending weight. 
	 */
	function sort_ascending( $rates_array ) {
		//bw_trace2();
		$weights = array();
		$rates = array();
		//$group = array();
		$labels = array();
		$min_weights = array();
		foreach ( $rates_array as $key => $value ) {
			$weights[ $key ] = $value[0];
			$rates[ $key ] = $value[1];
			$labels[ $key ] = $value[2];
			$min_weights[ $key ] = $value[3];
		}
		//bw_trace2();
		array_multisort( $weights, SORT_ASC, SORT_NUMERIC, $rates, $labels, $min_weights );
		//bw_trace2( $weights, "weights", false );
		//bw_trace2( $rates, "weights", false );
		//bw_trace2( $labels, "labels", false );
		//bw_trace2( $min_weights, "min_weights", false );
		$new_array = array();
		foreach ( $weights as $key => $value ) {
			$new_array[] = array( $value, $rates[ $key ], $labels[ $key ], $min_weights[ $key ] ); 
		} 
		return( $new_array );
	}
	
	/**
	 * Initialize the migration form fields
	 * 
	 * 
	 */
	function init_migration_form_fields() {
		$options =  array( //"none" => "None"
										  "info" => "Refresh information"
										 , "migrate" => "Perform migration" 
										 , "complete" => "Complete migration"
										 );
										 
										 
		$status = array( 0 => "0 - Migration not necessary."
									 , 1 => "1 - Migration not started."
									 , 2 => "2 - Migration in progress."
									 , 3 => "3 - Migration complete. "
									 , 4 => "4 - Migration fully complete."
									 , 5 => "5 - Unknown."
									 );
		$this->form_fields = array( 
			'information' => array( 'title' => 'Information '
													, 'type' => "textarea"
													, 'description' => "Displays information regarding the migration status. Choose an Action to perform."
													, 'desc_tip' => true
													, 'disabled' => true
													),
			'status' => array( 'title' => 'Status'
											 , 'type' => 'select'
											 , 'disabled' => true
											 , 'options' => $status
											 ),													
			'action' => array( 'title' => 'Action'
											 , 'type' => 'select'
											 , 'options' => $options
											 , 'description' => 'Choose an action to perform'
											 ),
		);
	}
	
	/**
	 * Generate information area html
	 * 
	 * Rather than use textarea to display a field we create an information area.
	 * which is populated with @TODO
	 * Can we access $this->form_fields[ $key ]['description'] to change the information fields description? 
	 * If so, can we get away with only one field?
	 */	
	function generate_info_html( $key, $data ) {
		bw_trace2( $this->form_fields, "this form_fields", true );
		$info = $data['description'];
		$info .= $this->get_option( $key );
		return( $info );
	}
	
	/**
	 * Logic for Migration processing
	 * 
	 * Processing will depend on the 'action' selected.
	 * Each action updates the information field and the status.
	 * 
	 * Most of the work is done by the OIK_Weight_Zone_Shipping_Migration class.
	 * All we're doing here is getting and setting the values for the information field.
	 */
	function process_migration() {
		bw_trace2( $this->settings, "this settings" );
		bw_trace2( $this->data, "this data" );
		$oikwzsm = oik_weight_zone_shipping_multi_rate_migration();
		$information = $oikwzsm->process_migration( $this );
		$_POST['woocommerce_oik_weight_zone_shipping_information'] = $information;
		$_POST['woocommerce_oik_weight_zone_shipping_status'] = $oikwzsm->migration_status;	// get the value not the text.
		$this->form_fields['information']['description'] = $information;
		parent::process_admin_options(); 
	}

	/**
	 * Return the Method title for Migration
	 *
	 * How do we know that it really is Migration?
	 * cf=load-woocommerce_page_wc-settings
	 * 
	 * @return string method title for migration
	 */
	function get_method_title() {
		bw_trace2( $this, "this?m" );
		if ( !$this->instance_id ) {
			if ( 'oik_weight_zone_shipping' == $this->get_section() ) {
				$this->method_title = "Migration";
			}
		}
		return( $this->method_title );
	}
	
	/**
	 * Gets the section 
	 *
	 * If the instance_id is 0 and the section is 'oik_weight_zone_shipping' then we'll be changing the title and description.
	 *
	 * @return string|null selected section
	 */
	function get_section() {
		if ( isset( $_REQUEST['section'] ) ) {
			$section = $_REQUEST['section'];
		} else {
			$section = null;
		}
		return $section;
	}

	/**
	 * Return the Method description for Migration
	 * 
	 * @return string method description for migration
	 */
	function get_method_description() {
		if ( !$this->instance_id ) {
			if ( 'oik_weight_zone_shipping' == $this->get_section() ) {
				$this->method_description = __( "Automatic migration from weight/country shipping.", 'oik-weight-zone-shipping' );
			}
		}
		return( $this->method_description );
	}

	/**
	 * Implement woocommerce_shipping_' . $this->id . '_instance_settings_values filter
	 * 
	 * For WPML and WooCommerce MultiLingual we need to register the translatable string values for the shipping method title
	 * 
	 * @param array $instance_settings - instance settings, which we leave unchanged
	 * @param object $shipping_method_object
	 * @return array $instance_settings
	 */
	function instance_settings_values( $instance_settings, $shipping_method_object ) {
		$rates = $this->get_rates();
		bw_trace2( $rates, "rates" );
		foreach ( $rates as $key => $rate ) {
			$name = $shipping_method_object->id;
			$name .= "_";
			$name .= $shipping_method_object->instance_id;
			$name .= "_";
			$name .= $key;
			$name .= "_shipping_method_title";
			//do_action( 
			bw_trace2( $rate, "key $key", false );
			do_action( "wpml_register_single_string", 'woocommerce', $name, $rate[2] );
		}
		return( $instance_settings );
	}

} // end OIK_Weight_Zone_Shipping_Multi_Rate


if ( !function_exists( "bw_trace2" ) ) {
	function bw_trace2( $p=null ) { return $p; }
	function bw_backtrace() {}
}

