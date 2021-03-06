<?php
ini_set("display_errors",1);
if ( ! class_exists( 'WooCommerce_Betaout_Export_Plugin' ) ) {

class WooCommerce_Betaout_Export_Plugin
{

	protected $errors;
	protected $exported_data;
	public $export_settings;

	protected $included_order_keys = array();
	protected $included_order_default_product_keys = array();
	protected $included_order_product_keys = array();

	protected $included_user_identity_keys = array();
	protected $included_billing_information_keys = array();
	protected $included_shipping_information_keys = array();
	protected $status_for_user_activity = array();
	
	protected $woocommerce_version;
	protected $products_in_columns;
	

	function __construct()
	{	
		global $woocommerce;
    	$this->woocommerce_version = $woocommerce->version;
    	
		if( class_exists( 'Woocommerce' ) ){
			$this->hooks();
		}
		else{
			add_action( 'woocommerce_loaded', array( &$this, 'hooks' ) );
		}
		
		$this->products_in_columns = true;
	} //__construct
	
	public function hooks()
	{
		add_action( 'admin_enqueue_scripts', array($this, 'exportjavascript' ) );
		add_action( 'init', array($this, 'init_class_vars') );
		add_action( 'init', array($this, 'export') );
	} //hooks
	
	public function init_class_vars()
	{
		$this->included_order_keys = $this->wcse_included_order_keys();
		$this->included_user_identity_keys = $this->wcse_included_user_identity_keys();
		$this->included_billing_information_keys = $this->wcse_included_billing_information_keys();
		$this->included_shipping_information_keys = $this->wcse_included_shipping_information_keys();
		$this->included_order_default_product_keys = $this->wcse_included_order_default_product_keys();
		$this->included_order_product_keys = $this->wcse_included_order_product_keys();
		$this->status_for_user_activity = $this->wcse_status_for_user_activity();
		$this->errors = array();
             
	}
        
      public function exportjavascript($hook)
	{
         wp_enqueue_script( 'betaout_custom_script', plugin_dir_url( __FILE__ ) . '/js/betaout-export.js' , array('jquery','jquery-ui-datepicker') );
         wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	
	} 

	public function panel()
	{    
		if( count($this->errors) > 0 ) {
			echo '<div class="error"><ul>'; 
			foreach( $this->errors as $error) {
				echo '<li>'.$error->get_error_message().'</li>';
			}
			echo '</ul></div>'; 
		}
		?>
           <h2><span><?php _e('Betaout Order Export', 'betaout');?></span></h2>
		<div id="poststuff">
		<form id="wcse-form" method="post" action="">
			<div class="postbox">
			<h3><span><?php _e('Data Type', 'wcse');?></span></h3>
			<div class="inside">
			<table class="form-table" id="wcse-form-table-entity">
				<tr valign="top">
					<th scope="row">
						<label for="wcse-entity"><?php _e('Would you like to export customers or orders ?','wcse');?></label>
					</th>

					<td>
						<select name="wcse_type" id="wcse-entity">
							<option value="customers"><?php _e('Customers', 'woocommerce'); ?></option>
							<option value="orders" selected="selected"><?php _e('Orders', 'woocommerce'); ?></option>
						</select>
					</td>
				</tr>

				<tr valign="top" id="wcse-td-user-infos">

					<th scope="row"><?php _e('User data', 'wcse');?></th>
					
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php _e('User data', 'wcse');?></span></legend>

							<!-- USER IDENTITY -->
							<label for="wcse-userdata-identity">
								<input name="wcse_userdata[]" type="checkbox" id="wcse-userdata-identity" value="identity" checked="checked">
									<?php _e('User identity', 'wcse'); ?>
							</label><br>

							<!-- USER BILLING INFO -->
							<label for="wcse-userdata-billing">
								<input name="wcse_userdata[]" type="checkbox" id="wcse-userdata-billing" value="billing" checked="checked">
									<?php _e('Billing informations', 'wcse'); ?>
							</label><br>

							<!-- USER SHIPPING INFO -->
							<label for="wcse-userdata-shipping">
								<input name="wcse_userdata[]" type="checkbox" id="wcse-userdata-shipping" value="shipping" checked="checked">
									<?php _e('Shipping informations', 'wcse'); ?>
							</label><br>

							<!-- USER SALES INFO -->
							<label for="wcse-userdata-sales">
								<input name="wcse_userdata[]" type="checkbox" id="wcse-userdata-sales" value="sales" >
									<?php _e('Sales statistics', 'wcse'); ?>
							</label><br>

						</fieldset>
					</td>
				</tr>

				<tr valign="top" id="wcse-td-command-status">

					<th scope="row"><?php _e('Order status', 'wcse');?></th>
					
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php _e('Order status', 'wcse');?></span></legend>

							<?php 
							if(function_exists('wc_get_order_statuses'))
							{
								$shop_order_status = wc_get_order_statuses();
								global $wpdb;
								foreach ($shop_order_status as $i=>$status): 
								
									
								$count_query = ' SELECT COUNT(*) as nb from '.$wpdb->prefix.'posts where post_status="'.$i.'" and post_type="shop_order" ';
		
    								$count = $wpdb->get_var($count_query);
								
								if($count>0):
								?>
								<label for="wcse-status-<?php echo $i;?>">
								<input name="wcse_status[]" type="checkbox" id="wcse-status-<?php echo $i;?>" value="<?php echo $i; ?>" checked="checked">
								<?php echo $status; ?> (<?php echo $count; ?>)
								
							</label><br><?php endif; ?>
							<?php endforeach;
							}
							else
							{	
								$shop_order_status = get_terms( 'shop_order_status', 'orderby=id&hide_empty=1' ); ?>
							<?php foreach ($shop_order_status as $status): ?>
								<label for="wcse-status-<?php echo $status->slug;?>">
								<input name="wcse_status[]" type="checkbox" id="wcse-status-<?php echo $status->slug;?>" value="<?php echo $status->term_id; ?>" checked="checked">
								<?php _e($status->name, 'woocommerce'); ?> (<?php echo $status->count;?>)
							</label><br>
							<?php endforeach; 
							
							}
							?>
					</fieldset>
					</td>
				</tr>
				
				<tr valign="top" id="wcse-td-coupon-description">

					<th scope="row"><?php _e('Coupon description', 'wcse');?></th>
					
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php _e('Coupon description', 'wcse');?></span></legend>

							
								<label for="wcse-coupon-description">
								<input name="wcse_coupon_description" type="checkbox" id="wcse-coupon-description" value="coupon_description">
								<?php _e('Include coupon description', 'wcse');?>
							</label><br>
							
					</fieldset>
					</td>
				</tr>
				
<!--				<tr valign="top" id="wcse-td-command-product-display">

					<th scope="row"><--?php _e('Product display', 'wcse');?></th>
					
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><-?php _e('Product display', 'wcse');?></span></legend>

							
								<label for="wcse-status-product-display">
								<input name="wcse_product_display" type="checkbox" id="wcse-status-product-display" value="columns">
								<-?php _e('Display products in column rather than in line', 'wcse');?>
							</label><br>
							
					</fieldset>
					</td>
				</tr>-->
				
				
			</table>
			</div>
			</div>
			<div class="postbox">
			<h3><span><?php _e('Optionnals date range', 'wcse');?></span></h3>
			<div class="inside">
			<table class="form-table" id="wcse-form-table-date-range">
				<tr valign="top">
					<th scope="row"><?php _e('Start Date', 'wcse');?></th>
					<td><input readonly="readonly" type="text" class="text custom_date" name="wcse_start_date" value="" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('End Date', 'wcse');?></th>
					<td><input readonly="readonly" type="text" class="text custom_date" name="wcse_end_date" value="" /></td>
				</tr>
			</table>
			</div>
			</div>
			<div class="postbox">
			<h3><span><?php _e('CSV file options', 'wcse');?></span></h3>
			<div class="inside">
			<table class="form-table" id="wcse-form-table-file-options" style="display:none">
				<tr valign="top">
					<th scope="row"><?php _e('Field Separator', 'wcse');?></th>
					<td><input type="hidden" class="small-text" name="wcse_separator" value="," /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Line Breaks', 'wcse');?></th>
					<td>
						<input type="hidden" class="small-text" name="wcse_linebreak" value="\r\n" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Export Format', 'wcse');?></th>
					<td>
					<select name="wcse_exportformat" id="wcse_exportformat">
							<option value="utf8" <?php if(WPLANG != 'zh_CN') echo 'selected="selected"'; ?> ><?php _e('Default (utf-8)', 'wcse'); ?></option>
							<option value="utf16" ><?php _e('Better Excel Support (utf-16)', 'wcse'); ?></option>
					</select>
					</td>
				</tr>
			</table>


			<input type="hidden" name="wcse_action" value="tocsv" />
			<input type="hidden" name="wcse_action_type" value="manual" />
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Export') ?>" />
			</p>
			</div>
			</div>
		</form>
		</div><!-- poststuff -->
              
		<?php 
               
	} //panel


	public function wcse_status_for_user_activity()
	{
		$keys = array( 'completed' );

		return apply_filters('wcse_status_for_user_activity_filter', $keys);
	}
	
	/*
	 * Define the keys for orders informations to export
	 */
	public function wcse_included_order_keys()
	{
		$keys = array(
		    //general
			'id', 'status', 'order_date',
			
			//billing infos
			'billing_first_name', 'billing_last_name', 
			'billing_company', 'billing_address_1', 'billing_address_2','billing_city',  
			'billing_postcode', 'billing_country', 'billing_state', 'billing_email', 
			'billing_phone',
			
			//shipping infos
			'shipping_first_name', 'shipping_last_name', 
			'shipping_company', 'shipping_address_1', 'shipping_address_2', 
			'shipping_city', 'shipping_postcode', 'shipping_state', 'shipping_country',
			
			//note
			'customer_note', 
			
			//payment, shipping and total
			'shipping_method_title', 'payment_method_title', 'order_discount', 
			'cart_discount', 'order_tax', 'order_shipping', 'order_shipping_tax', 
			'order_total', 'order_tax_detail', 'completed_date',
			
			//others
			'number_of_different_items',
			'total_number_of_items',
			'used_coupons',
			'coupon_name',
			'coupon_discount'
		);
			
		return apply_filters('wcse_included_order_keys_filter', $keys);
	}
	
	/*
	 * Define the keys for general product informations to export
	 */
	public function wcse_included_order_default_product_keys()
	{
		$keys = array('sku', 'name', 'quantity', 'line_price_without_taxes', 'line_price_with_taxes');
		return apply_filters('wcse_included_order_default_product_keys_filter', $keys);
	}

	/*
	 * Define the keys for additionnal product informations to export
	 */
	public function wcse_included_order_product_keys()
	{
		$keys = array();
		return apply_filters('wcse_included_order_product_keys_filter', $keys);
	}

    
	public function wcse_included_user_identity_keys()
	{
		
		$keys = array(
			'user_registered',
			'user_login',
			'user_email'
		);

		return apply_filters('wcse_included_user_identity_keys_filter', $keys);
	
	}

	public function wcse_included_billing_information_keys()
	{
		
		$keys = array(
			'billing_first_name',  'billing_last_name', 'billing_company',
			'billing_address_1', 'billing_address_2', 'billing_city',
			'billing_postcode', 'billing_country', 'billing_state', 
			'billing_email', 'billing_phone'
		);

		return apply_filters('wcse_included_billing_information_keys_filter', $keys);
	
	}

	public function wcse_included_shipping_information_keys()
	{
		
		$keys = array(
			'shipping_first_name', 'shipping_last_name', 'shipping_company', 
			'shipping_address_1', 'shipping_address_2', 'shipping_city', 
			'shipping_postcode', 'shipping_country', 'shipping_state'
		);

		return apply_filters('wcse_included_shipping_information_keys_filter', $keys);
	
	}


	public function get_data()
	{
		$csv = '';
		$export_csv = true;

		$sep = (empty($this->export_settings['wcse_separator'])) ? ',' : $this->export_settings['wcse_separator'];
		$lb = (empty($this->export_settings['wcse_linebreak'])) ? "\r\n" : stripslashes_deep( $this->export_settings['wcse_linebreak'] );
		
		if($lb == 'rn')
		   $lb = "\r\n";

		$start = (empty($this->export_settings['wcse_start_date'])) ? '1970-01-01' : $this->export_settings['wcse_start_date'];
		$end = (empty($this->export_settings['wcse_end_date'])) ? '2020-01-01' : $this->export_settings['wcse_end_date'];
		$end = (strlen($end)==10) ? $end.' 23:59:59' : $end;

		$sep = $this->expand_escape($sep);
		$lb = $this->expand_escape($lb);

		switch ($this->export_settings['wcse_type'])
		{
            case 'customers':
                $export_csv = $this->exportCustomers($csv, $export_csv, $sep, $lb, $start, $end);
		     break;
			
	    case 'orders':
               
                    $export_csv = $this->exportOrders($csv, $export_csv, $sep, $lb, $start, $end);
                    break;
				
        } //switch ($this->export_settings['wcse_type'])

        return $export_csv;
			
	} // get_data
	
	
	/*
     * Order export
     */
	protected function exportOrders($csv, $export_csv, $sep, $lb, $start, $end)
	{
        if(isset($this->export_settings['wcse_product_display']) and $this->export_settings['wcse_product_display']=='columns')
        {
        	$this->products_in_columns = true;
        }
        
        if(!function_exists('wc_get_order_statuses'))
        {
			$customer_orders = new WP_Query( array(
					'posts_per_page' => -1,
					'post_type'   => 'shop_order',
					'post_status' => 'publish',
					'orderby' => 'post_date',
					'order' => 'ASC',
					'date_query' => array(
							array(
										'after' => $start,
										'before' =>  $end,
										'inclusive' => true,
									),
							),
					
					'tax_query'=>array(
							array(
		
								 'taxonomy' =>'shop_order_status',
								 'field' => 'id',
								 'terms' => $this->export_settings['wcse_status']
							)
					)
				)
			);
        }
        else
        {
        	$customer_orders = new WP_Query( array(
					'posts_per_page' => -1,
					'post_type'   => 'shop_order',
					'post_status' => $this->export_settings['wcse_status'],
					'orderby' => 'post_date',
					'order' => 'ASC',
					'date_query' => array(
							array(
										'after' => $start,
										'before' =>  $end,
										'inclusive' => true,
									),
							),
				)
			);
        }
        
        
        $customer_orders = $customer_orders->get_posts();
        
        
        $total_orders = (int) sizeof($customer_orders);
        
        //get max items from a commande
       
        $i=0;
      
          $defaultrow='"orderId","subtotalPrice","totalShippingPrice","totalPrice","totalDiscount","totalTaxes","email","paymentType","currency","createdTime","promoCode","productId","productTitle","productQty","productPrice","categoryId","categoryArray"';
        if( count( $customer_orders ) == 0) {
        	$export_csv = false;
        	$this->errors[] = new WP_Error('error', __('No order to export in that period.', 'wcse'));
        }
        else {
        	$different_taxes = $this->getDifferentTaxes($customer_orders);
        	foreach ( $customer_orders as $customer_order ) {
                $dcsv='';
                $this->set_time_limit(20);
                
                       if(function_exists('wc_get_order_statuses'))
        			$order = new WC_Order($customer_order);
        		else
        		{
        			$order = new WC_Order();
        			$order->populate( $customer_order );
        		}
        		// adding custom fields.
        
        		// WooCommerce is not loading order meta data anymore
        		if(!isset($order->order_custom_fields))
        		$order->order_custom_fields = get_post_meta( $order->id );
        
        		foreach ($order->order_custom_fields as $key => $value) {
        			$order->$key = $value[0];
        		}
        
        		unset( $order->order_custom_fields );
        
            
        		if($i==0) {
        		$csv.=$defaultrow;
                        $csv.=$lb;
        
        		}	
                        $dcsv.='"'.$this->escape($order->get_order_number()).'"'.$sep;
                        $dcsv.='"'.$this->escape($order->get_total()).'"'.$sep;
                        $dcsv.='"'.$this->escape($order->get_shipping).'"'.$sep;
                        $dcsv.='"'.$this->escape($order->get_total()).'"'.$sep;
                        $dcsv.='"'.$this->escape($order->get_total_discount()).'"'.$sep;
                        $dcsv.='"'.$this->escape($order->get_total_tax()).'"'.$sep;
                        $dcsv.='"'.$this->escape($order->billing_email).'"'.$sep;
                        $dcsv.='"'.$this->escape($order->payment_method_title).'"'.$sep;
                        $dcsv.='"'.$this->escape($order->get_order_currency()).'"'.$sep;
                        $dcsv.='"'.$this->escape($order->order_date ).'"'.$sep;
                        $coupon="";
                       foreach($order->get_used_coupons() as $coupon)
            		{
            			if(in_array('coupon_name', $this->included_order_keys))
            				$coupon=$this->escape($coupon);
            		 }
        	        $dcsv.='"'.$coupon.'"'.$sep;
        		$items = $order->get_items();
        		
        		$items = array_values($items);
        		//items infos
        		foreach ($items as $item ){
                                 $pscv="";
        		         $pscv.='"'.$this->escape($item['product_id']).'"'.$sep;
                                 $pscv.='"'.$this->escape($item['name']).'"'.$sep;
                                 $pscv.='"'.$this->escape($item['qty']).'"'.$sep;
                                 $pscv.='"'.$this->escape($item['line_subtotal']).'"'.$sep;
                                 $terms=  get_the_terms($item['product_id'], 'product_cat' );
                                 $catids="";
                                 if ( $terms && ! is_wp_error( $terms ) ) : 
                                        $cat_links = array();
                                         $i=0;
                                         foreach ( $terms as $term ) {
                                          $catids.=$term->term_id.",";   

                                        }
                                    endif;
                                 $catarray=  self::export_categories($item['product_id']);
                                 $pscv.='"'.$catids.'"'.$sep;
                                 $pscv.='"'.serialize($catarray).'"'.$sep;
                             $csv.=$dcsv.$pscv.$lb;
                        }
        		
        	
        		$i++;
        	}
        }
     
        $this->exported_data = $csv;
		
		return $export_csv;
	}
	
public function export_categories($product_id){
         $terms = get_the_terms($product_id, 'product_cat' );
						
     if ( $terms && ! is_wp_error( $terms ) ) : 

	$cat_links = array();
         $i=0;
         foreach ( $terms as $term ) {
		$cat_links[$term->term_id] = array("n"=>$term->name,"p"=>$term->parent);
               
	}
        return $cat_links;
    endif;
    }
	
	/**
     * Customer export
     */
    protected function exportCustomers($csv, $export_csv, $sep, $lb, $start, $end)
    {
        $args = array(
			'fields' => 'all_with_meta',
			'role' => apply_filters('wcse_user_role', 'customer'),
			'orderby' => 'user_registered',
			'order' => 'ASC',
		);

		$customers = get_users($args);
		
		foreach($customers as $k => $customer) {
            $this->set_time_limit(20);
			if($customer->user_registered<$start or $customer->user_registered>$end){
				unset($customers[$k]);
			}
		}

		// calculate order activity if asked
		if(array_search('sales', $this->export_settings['wcse_userdata'])!==false) {

				$customers = $this->addUserActivity($customers);
		}				

		if(array_search('identity', $this->export_settings['wcse_userdata'])!==false){


				foreach ($this->included_user_identity_keys as $key) {

					$key = ucwords(str_replace('_', ' ', $key));
							$key2 = '';
							if(strpos($key, 'Billing')!== false)
							{
								$key = str_replace('Billing', '', $key);
								$key2 = ' ('.__('Billing', 'woocommerce').')';
							}
							if(strpos($key, 'Shipping')!== false)
							{
								$key = str_replace('Shipping', '', $key);
								$key2 = ' ('.__('Shipping', 'woocommerce').')';
							}
							
					$csv.='"'.$this->escape(__(trim($key), 'woocommerce').$key2).'"'.$sep;
				}

				
		}
		
		if(array_search('billing', $this->export_settings['wcse_userdata'])!==false){
				
				foreach ($this->included_billing_information_keys as $key) {
					
				
					$key = ucwords(str_replace('_', ' ', $key));
							$key2 = '';

							if(strpos($key, 'Billing')!== false)
							{
								$key = str_replace('Billing', '', $key);
								$key2 = ' ('.__('Billing', 'woocommerce').')';
							}
						
							
					$csv.='"'.$this->escape(__(trim($key), 'woocommerce').$key2).'"'.$sep;

				}
		}

		if(array_search('shipping', $this->export_settings['wcse_userdata'])!==false){
				
				foreach ($this->included_shipping_information_keys as $key) {
					
				
					$key = ucwords(str_replace('_', ' ', $key));
							$key2 = '';
						
							if(strpos($key, 'Shipping')!== false)
							{
								$key = str_replace('Shipping', '', $key);
								$key2 = ' ('.__('Shipping', 'woocommerce').')';
							}
							
					$csv.='"'.$this->escape(__(trim($key), 'woocommerce').$key2).'"'.$sep;

				}

		}


		if(array_search('sales', $this->export_settings['wcse_userdata'])!==false){

				$csv.='"'.$this->escape(__('Orders count','wcse')).'"'.$sep;
				$csv.='"'.$this->escape(__('Orders total','wcse')).'"'.$sep;

		}
		
		$csv = rtrim($csv, $sep);
		$csv.=$lb;


		if( count( $customers) == 0) {
			$export_csv = false;
			$this->errors[] = new WP_Error('error', __('No customer to export in that period.', 'wcse'));
		}
		else {

			foreach ($customers as $customer) {
                $this->set_time_limit(20);
                
				if(array_search('identity', $this->export_settings['wcse_userdata'])!==false) {

					foreach ($this->included_user_identity_keys as $key) {

						$csv.='"'.$this->escape($customer->{$key}).'"'.$sep;
					
					}
					

				}

				if(array_search('billing', $this->export_settings['wcse_userdata'])!==false) {

					foreach ($this->included_billing_information_keys as $key) {

						$csv.='"'.$this->escape($customer->{$key}).'"'.$sep;
					
					}

				}

				if(array_search('shipping', $this->export_settings['wcse_userdata'])!==false) {

					foreach ($this->included_shipping_information_keys as $key) {

						$csv.='"'.$this->escape($customer->{$key}).'"'.$sep;
					
					}

				}
			

				if(array_search('sales', $this->export_settings['wcse_userdata'])!==false) {

					$csv.='"'.$this->escape($customer->wcse_nb_order).'"'.$sep;
					$csv.='"'.$this->escape($customer->wcse_total_orderered).'"'.$sep;

				}

				$csv = rtrim($csv, $sep);
				$csv.=$lb;
			}

		}
		
		$this->exported_data = $csv;
		
		return $export_csv;
    
    } // exportCustomers
	
	/*
     * Product informations
     */
	private function infosProduit($order, $item, $sep)
	{
		$csv = '';
		
		if(isset($item)) //product exists
		{
			$product = $order->get_product_from_item($item);
			if($product instanceof WC_Product) //product is a product
			{
			    // default product informations
                         foreach ($this->included_order_default_product_keys as $pdt_key)
				{
				    if($pdt_key == 'sku')
				        $value = $product->get_sku();
				    if($pdt_key == 'name')
				        $value = $product->get_title();
				    if($pdt_key == 'quantity')
				        $value = $item['qty'];
				    if($pdt_key == 'line_price_without_taxes')
				        $value = $order->get_line_total($item, false);
				    if($pdt_key == 'line_price_with_taxes')
				        $value = $order->get_line_total($item, true);
				    
				    $csv.='"'.$this->escape($value).'"'.$sep;
				}
				
				// custom product informations		
				foreach ($this->included_order_product_keys as $pdt_key)
				{
					$pm = '';
					if($product instanceof WC_Product_Variation)
					{
						$pm = get_post_meta($product->variation_id, $pdt_key, true);
					}
					if(!$pm)
						$pm = get_post_meta($product->id, $pdt_key, true);
					
					if(!$pm)
						$pm = $this->getItemMeta(str_replace('attribute_', '', $pdt_key), $item);
		
					$csv.='"'.$this->escape($pm).'"'.$sep;
				}
			}
			else //product does not exists anymore
			{
                //default product key
                foreach ($this->included_order_default_product_keys as $pdt_key)
                {
                    if($pdt_key == 'sku')
				        $value = __('Deleted Item', 'wcse');
				    if($pdt_key == 'name')
				        $value = $item['name'];
				    if($pdt_key == 'quantity')
				        $value = $item['qty'];
				    if($pdt_key == 'line_price_without_taxes')
				        $value = $order->get_line_total($item, false);
				    if($pdt_key == 'line_price_with_taxes')
				        $value = $order->get_line_total($item, true);
				        
                    $csv.='"'.$this->escape($value).'"'.$sep;
                }

				// custom product informations => empty
				foreach ($this->included_order_product_keys as $pdt_key)
					$csv.='""'.$sep;
			}
		}
		else //empty line
		{
			// default product informations => empty
			foreach ($this->included_order_default_product_keys as $pdt_key)
				$csv.='""'.$sep;
			
			// custom product informations => empty
			foreach ($this->included_order_product_keys as $pdt_key)
				$csv.='""'.$sep;
		}
		
		return $csv;
	} //infosProduit


	public function export()
	{
		if( $_SERVER['REQUEST_METHOD']!=='POST' or !isset($_POST['wcse_action']) or ($_POST['wcse_action']!=='tocsv') or !current_user_can( apply_filters('wcse_caps','administrator')))
	  		return;

		$this->export_settings = $_POST;

		if($this->get_data()) {
			$filename = $this->export_settings['wcse_type'] .'-'. date( 'Y-m-d_H-i-s' ) . '.csv';
	  
			header( 'Content-Encoding: '. get_option( 'blog_charset' ));
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			
			if($this->export_settings['wcse_exportformat']=='utf16')
			{
				header ( 'Content-Type: application/vnd.ms-excel');
				print chr(255) . chr(254) . mb_convert_encoding(html_entity_decode($this->exported_data, ENT_QUOTES, get_option( 'blog_charset' )), 'UTF-16LE', get_option( 'blog_charset' ));
				die;
			}
			elseif($this->export_settings['wcse_exportformat']=='gbk')
			{
				header ( 'Content-Type: application/vnd.ms-excel');
				die(iconv("UTF-8","gbk//TRANSLIT",$this->exported_data));
			}
			else
			{
				header( 'Content-Type: text/csv; charset=' . get_option( 'blog_charset' ), true );
				die($this->exported_data);
			}
		
		}
		
	} //export
	
	
	/**
	 * Add wcse_total_orderered and wcse_nb_order to each user
	 *
	 * @param array $orders : orders to consider to get the max items
	 * @return int number of maximium items
	 */
	private function getMaxItems($post_orders)
	{	
		global $wpdb;
		$ids = array();
		foreach($post_orders as $post)
		{
			$ids[] = $post->ID;
		}
		
		if(count($post_orders)>0)
		{
    		$query = '
    			SELECT MAX(total) as total FROM
    			
    			(SELECT count(*) as total
    				FROM '.$wpdb->prefix.'woocommerce_order_items
    			WHERE 
    				order_item_type = "line_item"
    				AND order_id in ('.implode(', ', $ids).')
    			GROUP BY 
    				order_id
    			ORDER BY total desc) as tmp
    		';
    		
    		$results = $wpdb->get_results($query);
    
    		if(isset($results[0]))
    			return $results[0]->total;
    		else
    			return 0;
        }
		else
            return 0;
	}
	
	
	public function getItemMeta($key, $item)
	{
		if(isset($item[$key]))
			return $item[$key];
		else
			return '';
	}
	
	
	private function getMaxCoupons($post_orders)
	{	
		global $wpdb;
		$ids = array();
		foreach($post_orders as $post)
		{
			$ids[] = $post->ID;
		}
		
		if(count($post_orders)>0)
		{
    		$query = '
    			SELECT MAX(total) as total FROM
    			
    			(SELECT count(*) as total
    				FROM '.$wpdb->prefix.'woocommerce_order_items
    			WHERE 
    				order_item_type = "coupon"
    				AND order_id in ('.implode(', ', $ids).')
    			GROUP BY 
    				order_id
    			ORDER BY total desc) as tmp
    		';
    		
    		$results = $wpdb->get_results($query);
    
    		if(isset($results[0]))
    			return $results[0]->total;
    		else
    			return 0;
		}
		else
		  return 0;
	}
	
	
	private function set_time_limit($int)
	{
		$safe_mode = ini_get('safe_mode');
		if(!$safe_mode or $safe_mode == 'Off' or $safe_mode == 'off' or $safe_mode == 'OFF')
		{
			@set_time_limit($int);
		}
	}
	
	private function getCouponAmount($order_id, $coupon)
	{
		global $wpdb;
		
		$query = '
			SELECT meta_value
				FROM '.$wpdb->prefix.'woocommerce_order_items oi
				LEFT JOIN '.$wpdb->prefix.'woocommerce_order_itemmeta oim
					ON oi.order_item_id = oim.order_item_id
			WHERE 
				order_item_type = "coupon"
				AND order_id ='.$order_id.'
				AND order_item_name="%s"
				AND meta_key="discount_amount"
		';
		
		$results = $wpdb->get_results($wpdb->prepare($query, $coupon));

		if(isset($results[0]))
			return round($results[0]->meta_value, 2);
		else
			return 0;
	}
	
	/*
	*	Return an array width the slugs of the taxes used by the set of orders
	*/
	protected function getDifferentTaxes($post_orders)
	{
		global $wpdb;
		$ids = array();
		foreach($post_orders as $post)
		{
			$ids[] = $post->ID;
		}
		
		$query = '
			SELECT order_item_name, meta_value
				FROM '.$wpdb->prefix.'woocommerce_order_items oi, '.$wpdb->prefix.'woocommerce_order_itemmeta oim
			WHERE 
			    oi.order_item_id=oim.order_item_id
				AND oi.order_item_type = "tax"
				AND oi.order_item_name != ""
				AND oi.order_id in ('.implode(', ', $ids).')
				AND meta_key="label"
			GROUP BY 
				oi.order_id, oi.order_item_name
			
		';
		
		$results = $wpdb->get_results($query);

		$tab = array();
		foreach($results as $res)
		{
		   $tab[$res->order_item_name] = $res->meta_value;
		}
		
		return $tab;
	}
	
	/*
	*	Return an the amount of a specific taxe and a specific order
	*/
	protected function getSumTaxes($order_id, $taxslug)
	{
	   global $wpdb;
	   $query = '
			SELECT sum(meta_value) as meta_value
				FROM '.$wpdb->prefix.'woocommerce_order_items oi, '.$wpdb->prefix.'woocommerce_order_itemmeta oim
			WHERE 
			    oi.order_item_id=oim.order_item_id
				AND oi.order_item_type = "tax"
				AND oi.order_id = '.$order_id.'
				AND (meta_key="tax_amount" or meta_key="shipping_tax_amount")
				AND order_item_name= "'.$taxslug.'"
			
			
		';
		//echo $query;exit;
		$results = $wpdb->get_results($query);

		if(isset($results[0]))
			return round($results[0]->meta_value, 2);
		else
			return 0;
	}
	
	/**
	 * Add wcse_total_orderered and wcse_nb_order to each user
	 *
	 * @param array $customers array of customers to calculate activity
	 * @return array $customers with wcse_total_orderered and wcse_nb_order added
	 */
	private function addUserActivity($customers)
	{
		global $wpdb;
		
		$ids = array();
		$tmp = array();
		
		if(count($customers))
		{
            foreach($customers as $customer) {
			    $ids[] = $customer->data->ID;
		    }
		
    		$status = apply_filters('wcse_status_for_user_activity_filter', $this->status_for_user_activity);
    		
    		if(count($status)==0)
    			$status = array('completed');
    		
    		
    		
    		if(function_exists('wc_get_order_statuses'))
    		{
    			foreach($status as $i=>$s)
    			{
    				$status[$i] = 'wc-'.$s;
    			}
    			$status = implode('", "', $status);
    			
				$query = '
					SELECT 
						user.meta_value AS user_id, 
						sum(montant.meta_value) AS wcse_total_orderered, 
						count(*) AS wcse_nb_order 
					FROM '.$wpdb->prefix.'posts 
	
					LEFT JOIN '.$wpdb->prefix.'postmeta montant 
						ON id=montant.post_ID 
						AND montant.meta_key="_order_total"
					LEFT JOIN '.$wpdb->prefix.'postmeta user
						ON id=user.post_ID 
						AND user.meta_key="_customer_user" 
		
					
					WHERE post_type="shop_order" 
						AND user.meta_value in ('.implode(',', $ids).') 
						AND post_status in ("'.$status.'")
					GROUP BY user.meta_value
				';
				
				
    		}
    		else
    		{
    			$status = implode('", "', $status);
    			
					$query = '
					SELECT 
						user.meta_value AS user_id, 
						sum(montant.meta_value) AS wcse_total_orderered, 
						count(*) AS wcse_nb_order 
					FROM '.$wpdb->prefix.'posts 
					LEFT JOIN '.$wpdb->prefix.'postmeta montant 
						ON id=montant.post_ID 
						AND montant.meta_key="_order_total"
					LEFT JOIN '.$wpdb->prefix.'postmeta user
						ON id=user.post_ID 
						AND user.meta_key="_customer_user" 
					
					LEFT JOIN
						'.$wpdb->prefix.'term_relationships wtr
						ON user.post_ID = wtr.object_id
					LEFT JOIN '.$wpdb->prefix.'term_taxonomy wtt
						ON wtt.term_taxonomy_id = wtr.term_taxonomy_id
						AND wtt.taxonomy = "shop_order_status"
					INNER JOIN '.$wpdb->prefix.'terms wt
						ON wt.term_id = wtt.term_id
						AND wt.slug in ("'.$status.'")	
					
					WHERE post_type="shop_order" 
						AND user.meta_value in ('.implode(',', $ids).') 
					GROUP BY user.meta_value
				';
    		
    		}
    		
    		$results = $wpdb->get_results($query);
    		foreach($results as $res) {
    			$tmp[$res->user_id] = $res;
    		}
    		
    		foreach($customers as $i => $customer) {
    			if(isset($tmp[$customer->data->ID]))
    			{
    				$customers[$i]->wcse_total_orderered = $tmp[$customer->data->ID]->wcse_total_orderered;
    				$customers[$i]->wcse_nb_order = $tmp[$customer->data->ID]->wcse_nb_order;
    			}
    		}
        }
		
		return $customers;
	} //addUserActivity

	public function escape($str)
	{
		$str = str_replace('"', '""',$str);
		return $str;
	}

	public function expand_escape($string)
	{
		return preg_replace_callback(
			'/\\\([nrtvf]|[0-7]{1,3}|[0-9A-Fa-f]{1,2})?/',
			create_function(
				'$matches',
				'return ($matches[0] == "\\\\") ? "" : eval( sprintf(\'return "%s";\', $matches[0]) );'
			),
			$string
		);
	}

    /**
     * Desactivition plugin
     *
     * 
     * @since 2.0
     * 
     * @access public
     * @return void
     */
    

} //WooCommerce_Smart_Export_Plugin


} //if
