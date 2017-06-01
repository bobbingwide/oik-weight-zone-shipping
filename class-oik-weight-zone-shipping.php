<?php // (C) Copyright Bobbing Wide 2015-2017

/**
 * Single rate weight zone shipping class WooCommerce extension
 *
 * Implements single rate shipping charges by weight and shipping zone
 * Depends on WooCommerce 2.6, 3.0 or higher
 *  
 */
class OIK_Weight_Zone_Shipping extends WC_Shipping_Method {

	/**
	 * Title for the selected shipping rate.
	 * 
	 * but we may also need it for "No shipping method"
	 *
	 */
	public $shippingrate_title;
	
	/**
	 * We no longer allow a '/' in the localized version.
	 * But it's supported in the original 'options' field.
	 */
	private $allowed_delimiters = array( "|", "," );
	private $dot_rate_delimiters = array( "|", "/", "," );
	private $decimal_separator;
	private $thousand_separator; 
	private $decimals;
	
	private $delimiters = null;
  
	/**
	 * Constructor for OIK_Weight_Zone_Shipping class
	 *
	 * Sets the ID to 'oik_weight_zone_shipping'
	 
	 * Values for supports are:
	 * - shipping-zones Shipping zone functionality + instances
	 * - instance-settings Instance settings screens.
	 * - settings Non-instance settings screens. Enabled by default for BW compatibility with methods before instances existed.
	 * - instance-settings-modal Allows the instance settings to be loaded within a modal in the zones UI.
	 * 
	 * For instance-settings to work we need to set $this->instance_id
	 *
	 */
	function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );
		//bw_trace2( );
		//bw_backtrace();
		$this->supports = array( "shipping-zones", "instance-settings", "instance-settings-modal" );
		$this->id = 'oik_weight_zone_shipping'; 
		$this->method_title = __( 'Weight Zone', 'oik-weight-zone-shipping' );
		$this->method_description    = __( 'Lets you charge based on cart weight.', 'oik-weight-zone-shipping' );
		$this->admin_page_heading     = __( 'Weight and zone based shipping', 'oik-weight-zone-shipping' );
		$this->admin_page_description = __( 'Define rates for shipping by weight and zone', 'oik-weight-zone-shipping' );
		//add_action( 'woocommerce_update_options_shipping_oik_weight_zone_shipping', array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		$this->init();
	}

	/**
	 * Initialise the shipping method
	 * 
	 * We need to find out what's supposed to go in:
	 *  public $instance_form_fields = array();
	 *  public $instance_settings = array();
	 */
	function init() {
		$this->set_allowed_delimiters();
		$this->init_form_fields();
		$this->init_settings();
		$this->init_instance_settings();
		//bw_trace2( $this->instance_settings, "instance_settings" );

		$this->enabled          = $this->get_option('enabled');
		
		$this->type             = 'order';
		$this->tax_status       = $this->get_option('tax_status');
		$this->fee              = $this->get_option('fee');
		
		//bw_trace2( $this->fee, "this->fee" );
		$this->instance_settings['fee'] = $this->price( $this->fee );
		
		
		// @TODO What do we do with regards to availability
		//$this->availability     = 'specific';
		//if ( empty( $this->countries ) ) {
		//	$this->availability = $this->settings['availability'] = 'all';
		//}
		
		$this->title              = $this->get_option( 'title' );
		$this->shippingrate_title = $this->title;
	}

	/**
	 * Set the instance form fields
	 * 
	 * Note: Set desc_tip to true if you want the description to appear as a tip which can be viewed when you hover over the ?
	 * 
	 * 
	 */ 
	function init_form_fields() {
		$six_ninety_five = $this->price( 6.95 ); 
		$three_fifty = $this->price( 3.50 );
		$this->instance_form_fields = array(
				'title'      => array(
					'title'       => __( 'Method Title', 'oik-weight-zone-shipping' ),
					'type'        => 'text',
					'description' => __( 'The title which the user sees during checkout, if not defined in Shipping Rates.', 'oik-weight-zone-shipping' ),
					'default'     => __( 'Weight zone shipping', 'oik-weight-zone-shipping' ),
					'desc_tip'    => true,
				),
				'rates'       => array(
					'title'       => __( 'Shipping Rates', 'oik-weight-zone-shipping' ),
					'type'        => 'textarea',
					'description' => sprintf( __( 'Set your weight based rates in %1$s for this shipping zone (one per line).<br /> Format: Max weight | Cost | Method Title override<br />Example: 10 | %2$s | Standard rate', 'oik-weight-zone-shipping' ),  get_option( 'woocommerce_weight_unit' ), $six_ninety_five ),
					'default'     => '',
					'desc_tip'    => false,
					'placeholder'	=> __( 'Max weight | Cost | Method Title override', 'oik-weight-zone-shipping' ),
				),
				'tax_status' => array(
					'title'       => __( 'Tax Status', 'oik-weight-zone-shipping' ),
					'type'        => 'select',
					'class' 			=> 'wc-enhanced-select',
					'description' => '',
					'default'     => 'taxable',
					'options'     => array(
						'taxable' 	=> __( 'Taxable', 'oik-weight-zone-shipping' ),
						'none' 		=> _x( 'None', 'Tax status', 'oik-weight-zone-shipping' )
						)
				),
				'fee'        => array(
					'title'       => __( 'Handling Fee', 'oik-weight-zone-shipping' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Fee excluding tax, e.g. %1$s. Leave blank to disable.', 'oik-weight-zone-shipping' ), $three_fifty ),
					'default'     => '',
					'desc_tip'		=> true,
				),
		);
	}

	/**
	 * Return if the method is available
	 *
	 * @return bool true 
	 */
	function is_available( $package ) {
		//bw_trace2();
		return true;
	}

	/**
	 * Calculate shipping rates
	 * 
	 * Calculates a single shipping rate for the FREE version
	 *
	 * @param array $package 
	 */
	function calculate_shipping( $package = array() ) {
		$woocommerce = function_exists('WC') ? WC() : $GLOBALS['woocommerce'];
		$rates = $this->get_rates();
		//bw_trace2( $rates, "rates" );
		$weight = $woocommerce->cart->cart_contents_weight;
		//bw_trace2( $weight, "cart contents weight" );
		$final_rate = $this->pick_smallest_rate( $rates, $weight );
		
		if ( $final_rate !== false && is_numeric( $final_rate )) {
			$taxable = ($this->tax_status == 'taxable') ? true : false;
			if ( $this->fee > 0 && $package['destination']['country'] ) {
			 $final_rate += $this->fee;
			}
			$rate = array(
							 'id'        => $this->id . "_" .  $this->instance_id, 
							 'label'     => $this->shippingrate_title,
							 'cost'      => $final_rate,
							 'taxes'     => '',
							 'calc_tax'  => 'per_order'
							 );
			//bw_trace2( $rate, "rate" );
			$this->add_rate( $rate );
		} else {
			add_filter( "woocommerce_cart_no_shipping_available_html", array( $this, 'no_shipping_available') );
		}
	}
	
	/**
	 * Gets the rate array field from a rates display line using user defined delimiters
	 *
	 * @param string $value
	 * @param array $allowed_delimiters for the field separator
	 * @return array rate; 0=> max weight, 1=> rate, 2=> label
	 */
	function get_local_rate( $value, $allowed_delimiters ) {
		$value = trim( $value );
		$value = str_replace( $allowed_delimiters, "|", $value );
		$rate = explode( "|", $value );
		foreach ( $rate as $key => $val ) {
			$rate[$key] = trim( $val );
		}
		if ( !isset( $rate[0] ) ) {
			$rate[0] = null;
		}	else {
			$rate[0] = $this->get_value_as_decimal( $rate[0] );
		}
		if ( !isset( $rate[1] ) ) {
			$rate[1] = null;
		}	else {
			$rate[1] = $this->get_value_as_decimal( $rate[1] );
		}
		if ( !isset( $rate[2] ) ) {
			$rate[2] = null;
		}
		return $rate;
	}

	/**
	 * Returns a decimal price value
	 * 
	 * The number may have been entered using the thousand and decimal separators currently specified.
	 * OR the user could simply have used '.'s
	 * 
	 * - We want to convert the incoming number to a simple decimal value.
	 * - We assume that the separator character are sensible.
	 * - We don't care how many decimal places are used on input
	 * 
	 * e.g.
	 * Thous | Dec  | Value     | Return
	 * ----- | ---  | --------- | -------
	 * ,     | .    | 1,234.56  | 1234.56
	 * .     | ,    | 1.234,56  | 1234.56
	 * blank | ,    | 1 234,56  | 1234.56
	 * xy    | z    | 1xy234z56 | 1234.56 
	 *  
	 * @param string $value
	 * @return string decimal value
	 */
	function get_value_as_decimal( $value ) {
		$value = str_replace( $this->thousand_separator, "", $value );
		$value = str_replace( $this->decimal_separator, ".", $value );
		return $value;
	}

	/**
	 * Get the useable rate
	 *
	 * @param string $value
	 * @return array $rate_array 
	 */
	function get_rate( $value ) {
		$rate_array = $this->get_local_rate( $value, $this->delimiters );
		//bw_trace2( $rate_array, "rate array", true );
		return $rate_array;
	}

	/** 
	 * Sets the allowed field delimiters
	 * 
	 * We remove any delimiters that are defined as WooCommerce currency separators
	 * 
	 * Notes: 
	 * - Default separator for decimal is a dot aka period '.'
	 * - Default separator for thousand is ','
	 * - WooCommerce allows the separators to be the same value. We don't.
	 * - Separators can also be blank, or null.
	 * - We don't allow '|' to be used as a currency separator.
	 * - Do we really expect rates to be in the thousands?
	 * - We'll allow the weight to be entered using the same rules as currency
	 */
	function set_allowed_delimiters( ) {
		//$this->allowed_delimiters = array( "/", "," );
		$this->decimal_separator = wc_get_price_decimal_separator();
		$this->thousand_separator = wc_get_price_thousand_separator();
		$this->decimals = wc_get_price_decimals();
		$acceptable = $this->acceptable_separators();
		
		$allowed_delimiters = array_diff( $this->allowed_delimiters, array( $this->decimal_separator, $this->thousand_separator ) );
		//bw_trace2( $allowed_delimiters, "allowed delimiters" );
		$this->allowed_delimiters = $allowed_delimiters;
		return $acceptable;
	}

	/**
	 * Tests separators are acceptable
	 *
	 * WooCommerce 3.0 doesn't perform any sanity checking on the separators
	 * This could lead to all kinds of problems.
	 * 
	 * @return bool - true if we think they're acceptable.
	 */
	function acceptable_separators() {
		$acceptable = true;
		if ( $this->decimal_separator == $this->thousand_separator ) {
			// wc_error( 
			$acceptable = false;
			bw_trace2( $this->decimal_separator, "Decimal and thousand separators should not be the same", false ); 
		}
		if ( $this->decimal_separator == '|' ) {
			$acceptable = false;
			bw_trace2( $this->decimal_separator, "Non-acceptable value for decimal separator", false );
		}
		if ( $this->thousand_separator == '|' ) {
			$acceptable = false;
			bw_trace2( $this->thousand_separator, "Non-acceptable value for thousand separator", false );
		}
		return $acceptable;
	}

	/**
	 * Returns the rates table
	 *
	 * If it's not an array then we treat it as an unconverted options string.
	 * @TODO What if it's not set?
	 * 
	 * @return array internal representation of the rates table
	 */
	function get_rates() {
		$rates = $this->instance_settings['rates_table'];
		//bw_trace2( $rates, "rates", false );
		if ( !$rates || !is_array( $rates) ) {
			$this->delimiters = $this->dot_rate_delimiters;
			$rates = $this->get_rates_table( $rates );
		}
		return $rates;
	}

	/**
	 * Validates the rates field converting it to a rates array
	 *
	 * @param string $key
	 * @param string $value
	 * @return 
	 */
	function validate_rates_field( $key, $value ) {
		//bw_trace2();
		$this->delimiters = $this->allowed_delimiters;
		$value = $this->get_rates_table( $value ); 
		return $value;
	}

	/**
	 * Validate the fee to be a valid currency value
	 *
	 * Allow for local currency separators
	 *
	 */ 
	function validate_fee_field( $key, $value ) {
		$value = $this->get_value_as_decimal( $value );
		//bw_trace2();
		return $value;
	}

	/**
	 * Convert a rates table array to a rates display string
	 * 
	 * Rates are stored internally as separate fields.
	 * On the front end it's a simple textarea field.
	 *
	 */
	function convert_rates_to_display() {
		//bw_trace2( $this, "this", false );
		if ( !isset( $this->instance_settings['rates'] ) ) {
			$this->instance_settings['rates'] = null;
			$this->delimiters = $this->dot_rate_delimiters;
		}
		$this->instance_settings['rates_table'] = $this->instance_settings['rates'];
		$rates_display = $this->rates_array_to_display( $this->instance_settings['rates'] );
		$this->instance_settings['rates'] = $rates_display;
	}

	/**
	 * Converts rates table to rates display
	 *
	 * @param array $rates
	 */
	function rates_array_to_display( $rates ) {
		//bw_trace2();
		$rates = $this->get_rates_table( $rates );
		$rates_display = array();
		foreach ( $rates as $key => $rate ) {
			$rates_display[] = $this->convert_rate_array_to_rate_display( $rate );
		}
		$rates_display = implode( "\n", $rates_display ); 
		$rates_display = stripslashes( $rates_display );
		return $rates_display;
	}

	/**
	 * Converts a rate array to an option string
	 * 
	 * 
	 * 
	 * @param array $rate 
	 * @return string $rate_display
	 */
	
	function convert_rate_array_to_rate_display( $rate ) {
		$rate_display = array();
		$rate_display[] = $this->local_number( array_shift( $rate ) );
		$rate_display[] = $this->price( array_shift( $rate ) );
		$rate_display[] = stripslashes( implode( " ", $rate ) );
		$rate_display = implode( " | ", $rate_display );
		return( $rate_display );
	}

	/**
	 * Display a localized number
	 * 
	 * Used to Format the Max weight where the number of decimals is not limited.
	 *
	 * https://en.wikipedia.org/wiki/Decimal_mark
	 * 
	 * @param string $value
	 * @return string localized decimal
	 */
	function local_number( $value ) {
		if ( is_numeric( $value ) ) { 
			//$local_number = number_format( $value, 10 , $this->decimal_separator, $this->thousand_separator );
			//$local_number = rtrim( $local_number, "0" );
			//$local_number = rtrim( $local_number, "." );
			$local_number = wc_format_localized_decimal( $value );
		} else {
			$local_number = $value;
		}
		//bw_trace2( $local_number, "local_number" );
		return( $local_number );
	} 
	
	/**
	 * Returns a localized price
	 * 
	 * Note: Does not support negative values.
	 * 
	 * @param string $value
	 * @return string localized version
	 */
	function price( $value ) {
		if ( is_numeric( $value ) ) { 
			$price = number_format( $value, $this->decimals, $this->decimal_separator, $this->thousand_separator );
		} else {
			$price = $value;
		}
		return $price;
	}
	
	/**
	 * Retrieves the original options string
	 * 
	 * We can't use `$this->get_option("options")` since the "options" field is no longer part of the form.
	 * So we have to check for the existence of the field and return that if set.
	 * 
	 * @return string|null value of 'options', if set
	 */
	function get_options() {
		if ( isset( $this->instance_settings['options'] ) ) {
			$options = $this->instance_settings['options'];
		} else {
			$options = null;
		}
		return $options;
	}

	/**
	 * Retrieves all rates available
	 *
	 * - If rates have not yet been converted then we load the values from "options"
	 * - Supports user defined currency separators 
	 * - Supports field separators of forward slash, vertical bar and comma; if not used as a currency separator
	 * - Also trims off blanks.
	 *
	 * @param string $value
	 * @return array $rates_table
	 */
	function get_rates_table( $value=null ) {
		if ( $value ) {
			$rates = $value;
		} else {
			$rates = $this->get_options();
			//bw_backtrace();
			//bw_trace2( $this, "thus", false );
		}
		//bw_trace2( $rates, "rates" ); 
		if ( !is_array( $rates ) ) { 			
			$rates_table = $this->convert_rates_display_to_rates_table( $rates );
		} else {
			$rates_table = $rates;
		}
		return $rates_table;
	}

	/**
	 * Convert rates display to rates table
	 * 
	 * @param string $rates_display
	 * @return array $rates - table
	 */
	function convert_rates_display_to_rates_table( $rates_display ) {
		$rates = array();
		//bw_trace2( $rates_display, "rates display", false );
		$rates_display = trim( $rates_display );
		if ( $rates_display ) {
			$rates_display = (array) explode( "\n", $rates_display );
			//bw_trace2( $rates_display, "rates display array", false );
			if ( sizeof( $rates_display ) > 0) {
				foreach ( $rates_display as $key => $value ) {
					$rate = $this->get_rate( $value );
					$this->set_shippingrate_title( $rate );
					$rates[] = $rate;
				}
			}
		}
		return $rates;
	}
    
	/**
	 * Set the title for this shipping rate
	 * 
	 * Note: This includes the shipping rate for zero weight carts;
	 * 
	 * @param array $rate - the current rate that we're going to use
	 */
	function set_shippingrate_title( $rate ) {
		//bw_trace2();
		if ( isset( $rate[2] ) && $rate[2] != "" ) {
			$title = $rate[2];
		} else {
			$title = $this->title;
		}
		$title = stripslashes( $title );
		$this->shippingrate_title = $title;
		return $title ;
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
		foreach ( $rates_array as $key => $value ) {
			$weights[ $key ] = $value[0];
			$rates[ $key ] = $value[1];
			$labels[ $key ] = $value[2];
		}
		//bw_trace2();
		array_multisort( $weights, SORT_ASC, SORT_NUMERIC, $rates, $labels );
		//bw_trace2( $weights, "weights", false );
		//bw_trace2( $rates, "weights", false );
		//bw_trace2( $labels, "labels", false );
		foreach ( $weights as $key => $value ) {
			$new_array[] = array( $value, $rates[ $key ], $labels[ $key ] ); 
		} 
		return( $new_array );
	}
	
	/**
	 * Picks the right rate from available rates based on cart weight
	 * 
	 * If you want to set a weight at which shipping is free
	 * then set a rate for the weight at the limit, and another way above the limit to 0
	 *
	 * e.g.
	 * `
	 * 50|100.00| Not free up to and including 50
	 * 999|0.00| Free above 50, up to 999
	 * 1000| X | Maximum weight supported is 999
	 * `
	 * 
	 * If the weight is above this highest value then the most expensive rate is chosen.
	 * This is rather silly logic... but it'll do for the moment.
	 * 
	 * We also set the shipping rate title for the selected rate.  
	 * 
	 * @param array $rates 
	 * @param string $weight - the cart weight 
	 * @return - rate - may be false if no rate can be determined
	 */
	function pick_smallest_rate( $rates_array, $weight) {
		$rate = null;
		$max_rate = false;
		$found_weight = -1;
		$found = false;
		if ( sizeof( $rates_array ) > 0) {
			$rates = $this->sort_ascending( $rates_array );
			//bw_trace2( $rates, "rates" );
			foreach ( $rates as $key => $value) {
				if ( $weight <= $value[0] && ( $found_weight < $weight ) ) {
					if ( true || null === $rate || $value[1] < $rate ) {
						$rate = $value[1];
						//bw_trace2( $rate, "rate is now", false );
						$found_weight = $value[0];
						$found = true;
						$this->set_shippingrate_title( $value );
					}   
				}
				if ( !$found  ) {
					if ( !$max_rate || $value[1] > $max_rate ) {
						$max_rate = $value[1];
						$this->set_shippingrate_title( $value );
					}
				}   
			}
		}
		if ( null === $rate ) {
			$rate = $max_rate;
			//$rate = false;
		}  
		return $rate;
	}

	/**
	 * Implement "woocommerce_cart_no_shipping_available_html" 
	 *
	 * @param string $html message to be displayed when there are no shipping methods available
	 * @return string Updated with our own version taken from the rates if the default has been overridden
	 */
	function no_shipping_available( $html ) {
		if ( $this->shippingrate_title && $this->shippingrate_title != $this->title ) {
			$html = $this->shippingrate_title;
		}
		return( $html );
	}
	
	/**
	 * Generate OIK Weight Zone Shipping Textarea HTML.
	 *
	 * This is a copy of WC_Settings_API::generate_textarea_html with
	 * - conversion of the rates array to a textarea field
	 * - overrides for the rows and cols values 
	 * - and defaulting the width to 100%
	 *
	 * @param  mixed $key
	 * @param  mixed $data
	 * @return string
	 */
	public function generate_textarea_html( $key, $data ) {
		$this->convert_rates_to_display();
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
			'css' => "width: 100%;",
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo $this->get_tooltip_html( $data ); ?>
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<textarea rows="10" cols="30" class="input-text wide-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo esc_textarea( $this->get_option( $key ) ); ?></textarea>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}
    
} // end OIK_Weight_Zone_Shipping


if ( !function_exists( "bw_trace2" ) ) {
	function bw_trace2( $p=null ) { return $p; }
	function bw_backtrace() {}
}

