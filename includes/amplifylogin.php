<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//ini_set("display_errors", 0);
if (!class_exists('AmplifyLogin')) {
class AmplifyLogin {

    public $amplifyObj;

    public static function addFiles() {

        wp_localize_script('amplify_magic', 'personaL10n', array(
            'plugin_url' => plugins_url('woocommerce-betaout'),
            'ajax_url' => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')),
        ));
    }

    public static function adminStyle() {
        $src = plugins_url('css/common.css', dirname(__FILE__));
        wp_register_style('custom_wp_admin_css', $src);
        wp_enqueue_style('custom_wp_admin_css');
    }

   public function wp_amplify_footer() {
?>
        <script type="text/javascript">
               <?php $user = wp_get_current_user();
            $userEmail = "";
            $userName='';
            if (is_user_logged_in() && $user) {
                $userEmail = $user->user_email;
                $userName=$user->user_login;
                $customerId=$user->Id;
            }?>
                
             <?php if(is_product()){
                 $brands_list =  wp_get_object_terms(get_the_ID(), 'product_brand');
		
		$brands_list_output = '';
		$brand_image_output = '';
		$brands_list_comma = ', ';
		$i = 0;
		
                $categories=self::betaout_categories(get_the_ID());
                 ?>
                 
                 _bout.push(["identify", {
                    "customer_id": "<?php echo $customerId?>",
                    "email": "<?php echo $userEmail ?>",
                    "phone": ""
                },{productId:"<?php echo get_the_ID()?>",categoryId:"<?php echo $categories[0]['cat_id'];?>",brandName:""}
            ]);
             <?php }else if(is_product_category()){?>
                 _bout.push(["identify", {
                    "customer_id": "<?php echo $customerId?>",
                    "email": "<?php echo $userEmail ?>",
                    "phone": ""
                },{productId:"",categoryId:"<?php str_replace('Product Categories |', '', wp_title('|', false, 'right'));?>",brandName:""}
            ]);
            <?php }else{?>
                 _bout.push(["identify", {
                    "customer_id": "<?php echo $customerId?>",
                    "email": "<?php echo $userEmail ?>",
                    "phone": ""
                },{productId:"",categoryId:"",brandName:""}
            ]);
          <?php }
        ?>
              
        </script>
<?php
    }
    public static function wp_amplify_header() {

        echo "<script type='text/javascript'>
           _boutAKEY= '" . get_option('_AMPLIFY_API_KEY') . "';
           _boutPID= '" . get_option('_AMPLIFY_PROJECT_ID') . "';</script>";
        wp_register_script('amplify_head', plugins_url('woocommerce-betaout/js/amplify.js'), array('jquery'));
        wp_enqueue_script('amplify_head');
    }

      public static function wpLogin() {
        try {
            if (is_user_logged_in() && !isset($_COOKIE['ampEm'])) {
                $current_user = wp_get_current_user();
                $userLogin = $current_user->user_login;
                $userEmail = $current_user->user_email;
                $userFirstName = $current_user->user_firstname;
                $userLastName = $current_user->user_lastname;
                $userId = $current_user->ID;
                $identifier = new Amplify();
                $response = $identifier->identify($userEmail, $userFirstName);
                
            } else if(!isset($_COOKIE['ampUITN'])){
                $identifier = new Amplify();
                $response = $identifier->identify();
                
            }
        } catch (Exception $e) {
            
        }
    }
    public static function amplifyinit() {
     try{
         if(!session_id()) {
             session_start();
         }
        $currentPage=$_SERVER['REQUEST_URI'];
         if(!isset($_SESSION['currentPage'])){
          $_SESSION['currentPage'] =$currentPage;
          $_SESSION['sendviewed'] = true;
         }else if($_SESSION['currentPage'] != $currentPage){
           $_SESSION['currentPage'] = $currentPage;
           $_SESSION['sendviewed'] = true;
         }else{
           $_SESSION['currentPage']=$currentPage;
           $_SESSION['sendviewed'] = false;
         }
          
//        add_action('parse_request', array('AmplifyLogin', 'connect'));
        $amplifyKey=get_option('_AMPLIFY_API_KEY', false);
        add_action('wp_enqueue_scripts', array('AmplifyLogin', 'addFiles'));

        // add_action('admin_enqueue_scripts', array('AmplifyLogin', 'adminStyle'));

        add_action('admin_menu', array('AmplifyLogin', 'amplifyMenu'));

        add_action('wp_footer', array('AmplifyLogin', 'wp_amplify_footer'));
        add_action('wp_head', array('AmplifyLogin', 'wp_amplify_header'), 1);
        add_action('login_enqueue_scripts', array('AmplifyLogin', 'wp_amplify_header'), 1);

        //track woo commerce
        if (is_plugin_active('woocommerce/woocommerce.php')) {
          if(!empty($amplifyKey)){
             
            add_action('wp_login', array('AmplifyLogin', 'amplify_signed_in'), 10, 2);
            // Signed out

            add_action('wp_logout', array('AmplifyLogin', 'amplify_signed_out'));

            // Signed up for new account (on my account page if enabled OR during checkout)
            add_action('user_register', array('AmplifyLogin', 'amplify_signed_up'));


            // Viewed Product (Properties: Name)

            add_action('woocommerce_after_single_product', array('AmplifyLogin', 'amplify_viewed_product'));


            // Added Product to Cart (Properties: Product Name, Quantity)

            add_action('woocommerce_add_to_cart', array('AmplifyLogin', 'amplify_added_to_cart'), 10, 6);
            add_action('woocommerce_after_cart_item_quantity_update',array('AmplifyLogin', 'amplify_updatecart'),10,2);

            // Removed Product from Cart (Properties: Product Name)

            add_action('woocommerce_before_cart_item_quantity_zero', array('AmplifyLogin', 'amplify_removed_from_cart'),10,1);
            add_action('woocommerce_cart_item_removed', array('AmplifyLogin', 'amplify_removed_item_from_cart'),10,2);

             // Started Checkout

            add_action('woocommerce_after_checkout_form', array('AmplifyLogin', 'amplify_started_checkout'));
            add_action('woocommerce_checkout_shipping', array('AmplifyLogin', 'amplify_checkout_shipping'));
           // add_action('woocommerce_checkout_order_review', array('AmplifyLogin', 'amplify_checkout_order_review'));

            // woocommerce_checkout_billing
            //woocommerce_checkout_order_review
            //woocommerce_checkout_shipping
            //woocommerce_proceed_to_checkout
          
            // Started Payment (for gateways that direct post from payment page, eg: Braintree TR, Authorize.net AIM, etc

            add_action('after_woocommerce_pay', array('AmplifyLogin', 'amplify_started_payment'));

            add_action('woocommerce_checkout_order_processed', array('AmplifyLogin', 'amplify_completed_purchase'), 10, 1);
            add_action('custom_order_betaout', array('AmplifyLogin', 'amplify_custom_purchase'), 10, 1);
            add_action('custom_event_betaout', array('AmplifyLogin', 'amplify_custom_event'), 10,1);
            add_action('woocommerce_order_status_changed',array('AmplifyLogin', 'amplify_status_changed'), 10, 1);

            //Track comments
            add_action('comment_post', array('AmplifyLogin', 'amplify_wrote_review_or_commented'));


            // Viewed Order

            add_action('woocommerce_view_order', array('AmplifyLogin', 'amplify_viewed_order'), 10, 1);


            // Updated Address

            add_action('woocommerce_customer_save_address', array('AmplifyLogin', 'amplify_updated_address'), 10, 1);



            add_action('woocommerce_customer_change_password', array('AmplifyLogin', 'amplify_changed_password'), 10, 1);


            // Estimated Shipping, Tracked Order, Used a Coupon
            // Checking $_POST for these, as there are no suitable hooks yet
            add_action('init', array('AmplifyLogin', 'amplify_track_from_post'), 25);

            // Cancelled Order, Reordered
            // Checking $_GET for these, as there are no suitable hooks yet

            // AJAX Applied Coupon

            add_action('wp_ajax_nopriv_woocommerce_apply_coupon', array('AmplifyLogin', 'amplify_ajax_applied_coupon'), 0);
            add_action('wp_ajax_woocommerce_apply_coupon', array('AmplifyLogin', 'amplify_ajax_applied_coupon'), 0);

            add_action('wp_ajax_woocommerce_add_to_cart', array('AmplifyLogin', 'amplify_ajax_added_to_cart'), 0);
            add_action('wp_ajax_nopriv_woocommerce_add_to_cart', array('AmplifyLogin', 'amplify_ajax_added_to_cart'), 0);
            
           
            add_action('woocommerce_payment_complete', 'woocommerce_payment_complete');
           //add_action('woocommerce_order_status_completed','woocommerce_payment_complete');
        }
        }
     }catch(Exception $e){

     }
    }

    public static function amplifyMenu() {
       add_menu_page('Woocommerce Betaout', 'Woocommerce Betaout', 'manage_options', 'woocommerce-betaout', 'AmplifyLogin::amplify', plugins_url('images/icon.png', dirname(__FILE__)));
       add_submenu_page('woocommerce-betaout', 'Woocommerce Order Export', 'Woocommerce Order Expor', 'manage_options', 'betaoutexport',array(new WooCommerce_Betaout_Export_Plugin(), 'panel'));
    }

    /**
     * Track Sign In
     */
    public function amplify_signed_in($user_login, $user) {
        try{
        $amplifyObj = new Amplify();
        $user_email= $user->data->user_email;
        $customer_id= $user->data->ID;
        $data['email']=$user_email;
        $data['customer_id']=$customer_id;
        try {
             $result=$amplifyObj->identify($data);
           } catch (Exception $ex) {

           }
        }catch(Exception $e){
            
        }
         
    }
    public function amplify_custom_event($event){
         try{

         $amplifyObj = new Amplify();

         $result=$amplifyObj->event($event);

        }catch(Exception $e){
      }

    }

    /**
     * Track Sign Out
     */
    public function amplify_signed_out() {
       try{
        $amplifyObj = new Amplify();
        $amplifyObj->event('customer_logout');
       }catch(Exception $e){

       }
       
    }

    /**
     * Track Sign Up
     */
    public function amplify_signed_up($user_id) {
        try{
          $amplifyObj = new Amplify();
          $info = get_userdata($user_id );
          $data['email']=$info->user_email;
          $data['customer_id']=$user_id;
          $propetyArray=array('first_name'=>$info->first_name,'userlogin'=>$info->userlogin);
          $result=$amplifyObj->identify($data);
        }catch(Exception $e){

        }
    }

    
    /**
     * Track Add to Cart
     */
    public function amplify_added_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
       
       try{
        if (isset($_POST['action'])) {
            return;
        }
        $currency = get_woocommerce_currency();
        $product = new WC_Product($product_id);
       
           $imageSrc="";
            try{
            $post_thumbnail_id = get_post_thumbnail_id($product_id );
            $imageSrc = wp_get_attachment_url($post_thumbnail_id);
             }catch(Exception $e){
                  $imageSrc="";
             }
             $price=0;
            if($product->get_sale_price()){
               $price=$product->get_sale_price();
            }else if($product->get_regular_price()){
                 $price=$product->get_regular_price();
            }else{
                $price=$product->get_price();
            }
            $productprice=$price*$quantity;
            $productarray=array();
            $productarray[0]['name']= $product->post->post_title;
            $productarray[0]['sku']=  $product->get_sku();
            $productarray[0]['price']= $price;
            $productarray[0]['id'] =$product_id;
            $productarray[0]['categories'] = self::betaout_categories($product_id);
            $productarray[0]['image_url'] = $imageSrc;
            $productarray[0]['product_url'] =$product->get_permalink();
            $productarray[0]['quantity'] = $quantity;
           
            global  $woocommerce;
            
            $cartInfo["total"] =$woocommerce->cart->subtotal+$productprice;
            $cartInfo["revenue"] = $woocommerce->cart->subtotal+$productprice;
            $cartInfo['abandon_cart_url'] = $woocommerce->cart->get_cart_url();
            $cartInfo['currency']=$currency;
            
            $actionDescription = array(
                'activity_type' => 'add_to_cart',
                'products'=>$productarray,
                'cart_info'=>$cartInfo
                );
        $amplifyObj = new Amplify();
        $result = $amplifyObj->customer_action($actionDescription);
       
       }catch(Exception $e){

       }
    }
    /**
     * update cart item
     */

    public function amplify_updatecart($cart_item_key, $quantity){
       try{
          
         global $woocommerce;
         $cart=$woocommerce->cart->get_cart();
         $itmarrayprice=$cart[$cart_item_key]['line_total'];
         $product_id=$cart[$cart_item_key]['product_id'];

        $product = new WC_Product($product_id);
        $currency = get_woocommerce_currency();
        $sku = $product->get_sku();
        $stock = $product->get_stock_quantity();
        $instock=$product->is_in_stock();
           $imageSrc="";
            try{
            $post_thumbnail_id = get_post_thumbnail_id($product_id );
            $imageSrc = wp_get_attachment_url($post_thumbnail_id);
             }catch(Exception $e){
                  $imageSrc="";
             }
             $productarray=array();
            $price=0;
            if($product->get_sale_price()){
               $price=$product->get_sale_price();
            }else if($product->get_regular_price()){
                 $price=$product->get_regular_price();
            }else{
                $price=$product->get_price();
            }
            $productarray=array();
            $productarray[0]['name']= $product->post->post_title;
            $productarray[0]['sku']=  $product->get_sku();
            $productarray[0]['price']= $price;
            $productarray[0]['id'] =$product_id;
            $productarray[0]['categories'] = self::betaout_categories($product_id);
          
            $productarray[0]['image_url'] = $imageSrc;
            $productarray[0]['product_url'] =$product->get_permalink();
            $productarray[0]['quantity'] = $quantity;
           
            global  $woocommerce;
            $subtotalPrice=($woocommerce->cart->subtotal)-$itmarrayprice+$product->get_price()*$quantity;
            
            $cartInfo["total"] =$subtotalPrice;
            $cartInfo["revenue"] = $subtotalPrice;
            $cartInfo['abandon_cart_url'] = $woocommerce->cart->get_cart_url();
            $cartInfo['currency']=$currency;
            
           
            $actionDescription = array(
                'activity_type' => 'update_cart',
                'products'=>$productarray,
                'cart_info'=>$cartInfo
                );
         
        $amplifyObj = new Amplify();
      
        $result = $amplifyObj->customer_action($actionDescription);

        }catch(Exception $e){

        }
    }
    
    /**
     * Track Remove from Cart
     */
    public function amplify_removed_item_from_cart($cart_item_key,$data){
         global $woocommerce;
         
        $cart=$data;
        $removedContent=$data->removed_cart_contents;
        $product_id=$removedContent[$cart_item_key]['product_id'];
        $quantity=$removedContent[$cart_item_key]['quantity'];
        $lineTotal=$removedContent[$cart_item_key]['line_total'];
        $product = new WC_Product($product_id);
        $currency = get_woocommerce_currency();
        $sku = $product->get_sku();
        $stock = $product->get_stock_quantity();
        $instock=$product->is_in_stock();
          $imageSrc="";
            try{
            $post_thumbnail_id = get_post_thumbnail_id($product_id );
            $imageSrc = wp_get_attachment_url($post_thumbnail_id);
             }catch(Exception $e){
                  $imageSrc="";
             }
            $productarray=array();
            $price=0;
            if($product->get_sale_price()){
               $price=$product->get_sale_price();
            }else if($product->get_regular_price()){
                 $price=$product->get_regular_price();
            }else{
                $price=$product->get_price();
            }
            $productarray[0]['name']= $product->post->post_title;
            $productarray[0]['sku']=  $product->get_sku();
            $productarray[0]['price']= $price;
            $productarray[0]['id'] =$product_id;
            $productarray[0]['categories'] = self::betaout_categories($product_id);
            $productarray[0]['image_url'] = $imageSrc;
            $productarray[0]['product_url'] =$product->get_permalink();
            $productarray[0]['quantity'] =$quantity;
            
            $cartInfo=array("revenue" => $cart->subtotal-$lineTotal ,
			    "currency"=>$currency,
			   "total" => $cart->subtotal-$lineTotal
                        );
        
            $actionDescription = array(
                'activity_type' => 'remove_from_cart',
                'products'=>$productarray,
                'cart_info'=>$cartInfo
                );

        $amplifyObj = new Amplify();
        $result = $amplifyObj->customer_action($actionDescription);
      
    }
    public function amplify_removed_from_cart($cart_item_key) {
        try{
         global $woocommerce;
         
        $cart=$woocommerce->cart->cart_contents;
       
        $product_id=$cart[$cart_item_key]['product_id'];
        $quantity=$cart[$cart_item_key]['quantity'];
        $linetotal=$cart[$cart_item_key]['line_subtotal'];
       
        $product = new WC_Product($product_id);
        $currency = get_woocommerce_currency();
        $sku = $product->get_sku();
        $stock = $product->get_stock_quantity();
        $instock=$product->is_in_stock();
          $imageSrc="";
            try{
            $post_thumbnail_id = get_post_thumbnail_id($product_id );
            $imageSrc = wp_get_attachment_url($post_thumbnail_id);
             }catch(Exception $e){
                  $imageSrc="";
             }
             $price=0;
                        if($product->get_sale_price()){
                           $price=$product->get_sale_price();
                        }else if($product->get_regular_price()){
                             $price=$product->get_regular_price();
                        }else{
                            $price=$product->get_price();
                        }
            $productarray=array();
            $productarray[0]['name']= $product->post->post_title;
            $productarray[0]['sku']=  $product->get_sku();
            $productarray[0]['price']= $price;
            $productarray[0]['id'] =$product_id;
            $productarray[0]['currency'] = $currency;
            $productarray[0]['categories'] = self::betaout_categories($product_id);
           
            $productarray[0]['image_url'] = $imageSrc;
            $productarray[0]['product_url'] =$product->get_permalink();
            $productarray[0]['quantity'] = $quantity;
           
        
             $cartinfo=array("revenue" =>$woocommerce->cart->subtotal-$linetotal ,
			 "total" => $woocommerce->cart->subtotal-$linetotal,
                         "currency"=>$currency
                   );
            $actionDescription = array(
                'activity_type' => 'remove_from_cart',
                'pd'=>$productarray,
                'or'=>$cartinfo
                );
        $amplifyObj = new Amplify();
        $result = $amplifyObj->customer_action($actionDescription);
        }catch(Exception $e){
            
        }
    }


    /**
     * Track Checkout Start
     */
    public function amplify_started_checkout() {
        try{
        $amplifyObj = new Amplify();
         $result=$amplifyObj->event('checkout_process_billing');
        }catch(Exception $e){

        }
         
     }
     /**
     * Track Checkout Shipping
     */
    public function amplify_checkout_shipping() {
        try{
         $amplifyObj = new Amplify();
         $result=$amplifyObj->event('checkout_process_shipping');
        }catch(Exception $e){

        }

     }
     


      public function virtual_order_payment_complete_order_status($order_status, $order_id){
         try{
         $amplifyObj = new Amplify();
         $result=$amplifyObj->event('checkout_process_completed');
         }catch(Exception $e){

         }
        
      }
      public function woocommerce_payment_complete(){
        try{
          $amplifyObj = new Amplify();
         $result=$amplifyObj->event('checkout_process_completed');
        }catch(Exception $e){

        }
      }
    

    /**
     * Track Payment Start
     */
    public function amplify_started_payment() {
        try{
         $amplifyObj = new Amplify();
         $result=$amplifyObj->event('checkout_process_payment');
        }catch(Exception $e){

        }
    }
   
    public function amplify_status_changed($order_id){
        try{
       global $woocommerce;
     
        $order = new WC_Order($order_id);
        $status=$order->status;
        $amplifyObj = new Amplify();
        $result=$amplifyObj->update_order($order_id,$status);
        }catch(Exception $e){

        }
    }

    /**
     * Track Commenting (either Product Review or Blog Comment)
     */
    public function amplify_wrote_review_or_commented() {
        try{
        $type = get_post_type();
        if ($type == 'product') {
            $amplifyObj = new Amplify();
            $result=$amplifyObj->event('product_reviewed');
        } elseif ($type == 'post' || $type == 'page') {
           $amplifyObj = new Amplify();
            $result=$amplifyObj->event('comment');
        }
        }catch(Exception $e){

        }
    }
   //
    public function amplify_custom_purchase($order_id) {
     
        try{
         
         $amplifyObj=new Amplify();
         $order = new WC_Order($order_id);
         $currency = get_woocommerce_currency();
         $productarray=array();
         $items = $order->get_items();
         
         $i=0;
                 foreach ($items as $item ) {
                    
                    $product_name = $item['name'];
                    $product_id = $item['product_id'];
                    $product_variation_id = $item['variation_id'];
                    $quantity= $item['qty'];
                    $lineTotal=$item['line_total'];;
                    $lineSubTotal=$item['line_subtotal'];;
                    $product = new WC_Product($product_id);
                   
                      $imageSrc="";
                        try{
                        $post_thumbnail_id = get_post_thumbnail_id($product_id );
                        $imageSrc = wp_get_attachment_url($post_thumbnail_id);
                         }catch(Exception $e){
                              $imageSrc="";
                         }
                        $price=0;
                        if($product->get_sale_price()){
                           $price=$product->get_sale_price();
                        }else if($product->get_regular_price()){
                             $price=$product->get_regular_price();
                        }else{
                            $price=$product->get_price();
                        }
                        $productarray[$i]['name']= $product->post->post_title;
                        $productarray[$i]['sku']=  $product->get_sku();
                        $productarray[$i]['price']= $price;
                        $productarray[$i]['id'] =$product_id;
                        $productarray[$i]['currency'] = $currency;
                        $productarray[$i]['categories'] =self::betaout_categories($product_id);;
                        
                        $productarray[$i]['image_url'] = $imageSrc;
                        $productarray[$i]['product_url'] =$product->get_permalink();
                        
                        $productarray[$i]['quantity'] = $quantity;
                        
                        $i++;
                       
                 }
            $coupons=$order->get_used_coupons();
             $paymentMethod="custome";
            if($order->payment_method_title!=""){
                 $paymentMethod=$order->payment_method_title;
            }
            $cc=  implode(",", $coupons);
            $orderinfo=array("order_id"=>$order_id,
			"revenue" => $order->get_total() ,
			"shipping" => $order->get_shipping, 
			"tax" => $order->get_total_tax(), 
			"discount" => $order->get_total_discount(), 
			"total" => $order->get_subtotal(),
			"coupon" => $cc,
			"status" => $order->status,
                        "payment_method"=>$paymentMethod,
                        "currency"=>$order->get_order_currency()
                       );
            $user_email=array();
            
            $user_email['email']=$order->billing_email;
            if($order->billing_phone)
            $user_email['phone']=$order->billing_phone;
            if($order->customer_user)
            $user_email['customer_id']=$order->customer_user;
            
            $actionDescription = array(
                'activity_type' => 'purchase',
                'products'=>$productarray,
                'order_info'=>$orderinfo,
                'identifiers'=>$user_email,
                'custom_properties'=>array("color"=>"red","size"=>"Small"),
                );
            
            $amplifyObj->identify($user_email);
            $result = $amplifyObj->customer_action($actionDescription);
        }catch(Exception $e){
           mail("rohit@getamplify.com", "Purchase Exception", "orderID".$order_id);
        }
    }
    
    /**
     * Track Order Completion
     */
    public function amplify_completed_purchase($order_id) {
        try{
            
         $amplifyObj=new Amplify();
         $order = new WC_Order($order_id);
         $currency = get_woocommerce_currency();
         $productarray=array();
        $items = $order->get_items();
        
         $i=0;
                 foreach ($items as $item ) {
                    
                    $product_name = $item['name'];
                    $product_id = $item['product_id'];
                    $product_variation_id = $item['variation_id'];
                    $quantity= $item['qty'];
                    $lineTotal=$item['line_total'];;
                    $lineSubTotal=$item['line_subtotal'];;
                    $product = new WC_Product($product_id);
                   
                      $imageSrc="";
                        try{
                        $post_thumbnail_id = get_post_thumbnail_id($product_id );
                        $imageSrc = wp_get_attachment_url($post_thumbnail_id);
                         }catch(Exception $e){
                              $imageSrc="";
                         }
                          $price=0;
                        if($product->get_sale_price()){
                           $price=$product->get_sale_price();
                        }else if($product->get_regular_price()){
                             $price=$product->get_regular_price();
                        }else{
                            $price=$product->get_price();
                        }
                        $productarray[$i]['name']= $product->post->post_title;
                        $productarray[$i]['sku']=  $product->get_sku();
                        $productarray[$i]['price']= $price;
                        $productarray[$i]['id'] =$product_id;
                        $productarray[$i]['currency'] = $currency;
                        $productarray[$i]['categories'] =self::betaout_categories($product_id);;
                        $productarray[$i]['image_url'] = $imageSrc;
                        $productarray[$i]['product_url'] =$product->get_permalink();
                        $productarray[$i]['quantity'] = $quantity;
                       
                        $i++;
                       
                 }
          global $woocommerce;
          $paymentMethod="custome";
          if($order->payment_method_title!=""){
               $paymentMethod=$order->payment_method_title;
          }
           $couppon=$woocommerce->cart->applied_coupons;
            $orderinfo=array("order_id"=>$order_id,
			"revenue" => $order->get_total() ,
			"shipping" => $order->get_shipping, 
			"tax" => $woocommerce->tax_total, 
			"discount" => $order->get_total_discount(), 
			"total" => $woocommerce->cart->subtotal,
			"coupon" => $couppon['0'],
			"status" => $order->status,
                        "payment_method"=>$paymentMethod,
                        "currency"=>$order->get_order_currency()
                      );
            $user_email=array();
            
            $user_email['email']=$order->billing_email;
            if($order->billing_phone)
            $user_email['phone']=$order->billing_phone;
            if($order->customer_user)
            $user_email['customer_id']=$order->customer_user;
            
            
            $actionDescription = array(
                'activity_type' => 'purchase',
                'products'=>$productarray,
                'order_info'=>$orderinfo,
                'identifiers'=>$user_email,
                'custom_properties'=>array("color"=>"red","size"=>"Small"),
                );
          
          $amplifyObj->identify($user_email);
          $result = $amplifyObj->customer_action($actionDescription);
        
        }catch(Exception $e){
        }
    }

    /**
     * Track Order View
     */
    public function amplify_viewed_order($order_id) {
        try{
         $amplifyObj = new Amplify();
         $result=$amplifyObj->event('viewed_sales_order_history');
        }catch(Exception $e){

        }
    }

    /**
     * Track Address Update
     */
    public function amplify_updated_address($user_id) {
        try{
         $amplifyObj = new Amplify();
         $result=$amplifyObj->event('account_edit');
        }catch(Exception $e){

        }
    }


    /**
     * Track events from $_POST
     */
    public static function amplify_track_from_post() {
        try{
        // Applied Coupon
        if (!empty($_POST['apply_coupon']) AND !empty($_POST['coupon_code'])) {
            $coupon = new WC_Coupon(stripslashes(trim($_POST['coupon_code'])));
            if ($coupon->is_valid()) {
                $amplifyObj = new Amplify();
                $result=$amplifyObj->event('coupon_success');
               // $this->api_record_event($this->event_name['applied_coupon'], array('coupon code' => $coupon->code));
            }else{
                $amplifyObj = new Amplify();
                $result=$amplifyObj->event('coupon_unsuccess');
            }
        }
        if (isset($_GET['cancel_order']) AND isset($_GET['order']) AND isset($_GET['order_id'])) {
             $amplifyObj = new Amplify();
             $result=$amplifyObj->event('cancelled_order');
        }
        

        // Tracked Order //
        if (!empty($_POST['track']) AND !empty($_POST['orderid']) AND !empty($_POST['order_email'])) {
             $amplifyObj = new Amplify();
             $result=$amplifyObj->event('tracked_order');
        }
        }catch(Exception $e){

        }
        
    }

    

    /**
     * Track Applied Coupon from AJAX (on Checkout page)
     */
    public function amplify_ajax_applied_coupon() {
        try{

        if (!empty($_POST['coupon_code'])) {
            $coupon = new WC_Coupon(stripslashes(trim($_POST['coupon_code'])));
            if ($coupon->is_valid()) {
                 $amplifyObj = new Amplify();
                $result=$amplifyObj->event('coupon_success');
            }else{
                 $amplifyObj = new Amplify();
                $result=$amplifyObj->event('coupon_unsuccess');
            }
        }
        }catch(Exception $e){

        }
    }

    /**
     * Track Added to Cart from AJAX
     */
    public function amplify_ajax_added_to_cart() {
 try{
        
        $product_id = (int) apply_filters('woocommerce_add_to_cart_product_id', $_POST['product_id']);

        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', TRUE, $product_id, 1);
        $currency = get_woocommerce_currency();
        $quantity=isset($_POST['quantity'])?$_POST['quantity']:1;
        global  $woocommerce;
         
         $cartItemQty=$woocommerce->cart->get_cart_item_quantities();
       
        if ($passed_validation) {
            $product = new WC_Product($product_id);
           
            $imageSrc="";
            try{
            $post_thumbnail_id = get_post_thumbnail_id($product_id );
            $imageSrc = wp_get_attachment_url($post_thumbnail_id);
             }catch(Exception $e){
                  $imageSrc="";
             }
             $price=0;
            if($product->get_sale_price()){
               $price=$product->get_sale_price();
            }else if($product->get_regular_price()){
                 $price=$product->get_regular_price();
            }else{
                $price=$product->get_price();
            }
            $productprice=$price*$quantity;
            $productarray=array();
            $productarray[0]['name']= $product->post->post_title;
            $productarray[0]['sku']=  $product->get_sku();
            $productarray[0]['price']= $price;
            $productarray[0]['id'] =$product_id;
            $productarray[0]['currency'] = $currency;
            $productarray[0]['categories'] = self::betaout_categories($product_id);
            
            $productarray[0]['image_url'] = $imageSrc;
            $productarray[0]['product_url'] =$product->get_permalink();
            $productarray[0]['quantity'] = $quantity;
            
            $cartinfo=array("revenue"=>($woocommerce->cart->subtotal+$productprice),
                            "abandon_cart_url"=>$woocommerce->cart->get_cart_url(),
                            "total"=>$woocommerce->cart->subtotal+$productprice
                       );
            $actionDescription = array(
                'activity_type' => 'add_to_cart',
                'products'=>$productarray,
                'order_info'=>$cartinfo
                );
          
            $amplifyObj = new Amplify();
            $result = $amplifyObj->customer_action($actionDescription);
          
        }
 }catch(Exception $e){

    }
    }

    public function amplify_viewed_product() {
        try{
         
         $productId = get_the_ID();
         if (!empty($productId)) {
            $currency = get_woocommerce_currency();
            $product = new WC_Product($productId);
            
           $imageSrc="";
             try{
            $post_thumbnail_id = get_post_thumbnail_id($productId );
            $imageSrc = wp_get_attachment_url($post_thumbnail_id);
             }catch(Exception $e){
                  $imageSrc="";
             }
            $productarray=array();
            $price=0;
            if($product->get_sale_price()){
               $price=$product->get_sale_price();
            }else if($product->get_regular_price()){
                 $price=$product->get_regular_price();
            }else{
                $price=$product->get_price();
            }
                    
            $productarray[0]['name']= $product->post->post_title;
            $productarray[0]['sku']=  $product->get_sku();
            $productarray[0]['price']= $price;
            $productarray[0]['id'] =$productId;
            $productarray[0]['currency'] = $currency;
            $productarray[0]['categories'] = self::betaout_categories($productId);
           
            $productarray[0]['image_url'] = $imageSrc;
            $productarray[0]['product_url'] =$product->get_permalink();
            $productarray[0]['quantity'] = 1;
           
            $actionDescription = array(
                'activity_type' => 'view',
                'products'=>$productarray,
                );
             if(isset($_SESSION['sendviewed']) && $_SESSION['sendviewed']==TRUE){
               $amplifyObj = new Amplify();
               $result = $amplifyObj->customer_action($actionDescription);
            
             }else{
                 // echo "reload view product";
             }
       
       
        }
        }catch(Exception $e){
           //print_r($e);
        }
    }
    
    public function betaout_categories($product_id){
         $terms = get_the_terms($product_id, 'product_cat' );
						
     if ( $terms && ! is_wp_error( $terms ) ) : 

	 $cat_links = array();
         $i=0;
         foreach ( $terms as $term ) {
	  $cat_links[] = array("cat_id"=>$term->term_id,"cat_name"=>$term->name,"parent_cat_id"=>$term->parent);
               
	}
        return $cat_links;
    endif;
    }

    private function not_page_reload() {
        return 1;
    }

    public static function amplify() {
        try {

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'changekey') {
                require_once('html/configuration.php');
            } else {
                $amplifyApiKey = get_option("_AMPLIFY_API_KEY");
                $amplifyApiSecret = get_option("_AMPLIFY_API_SECRET");
                $amplifyProjectId = get_option("_AMPLIFY_PROJECT_ID");
                $wordpressVersion = get_bloginfo('version');
                $wordpressBoPluginUrl= plugins_url();
                if (!empty($amplifyApiKey) && !empty($amplifyApiSecret) && !empty($amplifyProjectId)) {
                    $parameters = array('wordpressVersion' => $wordpressVersion, 'wordpressBoPluginUrl' => $wordpressBoPluginUrl);
                    try {

                        $AMPLIFYSDKObj = new Amplify($amplifyApiKey, $amplifyApiSecret, $amplifyProjectId);
                       // $curlResponse = $AMPLIFYSDKObj->verify();
                    } catch (Exception $ex) {
                        $curlResponse = '{ "error": "' . $ex->getMessage() . '", "responseCode": 500 }';
                        $curlResponse = json_decode($result);
                    }
                    $curlResponse = $curlResponse;
                }

                require_once('html/amplify.php');
            }
        } catch (Exception $ex) {

        }
    }

}
}
?>
