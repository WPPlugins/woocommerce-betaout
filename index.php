<?php
/*
  Plugin Name: WooCommerce Betaout
  Plugin URI: http://www.betaout.com
  Description: Manage all your Wordpress sites and Editorial team from a single interface
  Version: 2.1
  Author: BetaOut (support@betaout.com)
  Author URI: http://www.betaout.com
  License: GPLv2 or later
 */
try{
function betaout_admin_notice() {
    echo "Please deactivate Wpecommerce betaout plugin";
    return;
}
defined('AMPLIFY_HOST')
        || define('AMPLIFY_HOST', 'betaout.in');

defined('AMPLIFY_VERSION')
        || define('AMPLIFY_VERSION', 'v2');
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
include_once 'includes/amplify.php';
include_once 'includes/amplifylogin.php';
include_once 'includes/amplifylogin.php';
include_once 'betaoutexport.php';


//------------------------------------------------------------------------------
//the plugin will work function if cURL and add_function exist and the appropriate version of PHP is available.
$adminErrorMessage = "";

if (version_compare(PHP_VERSION, '5.2.0', '<')) {
    $adminErrorMessage .= "PHP 5.2 or newer not found!<br/>";
}

if (!function_exists("curl_init")) {
    $adminErrorMessage .= "cURL library was not found!<br/>";
}

if (!function_exists("session_start")) {
    $adminErrorMessage .= "Sessions are not enabled!<br/>";
}

if (!function_exists("json_decode")) {
    $adminErrorMessage .= "JSON was not enabled!<br/>";
}

if(!empty($adminErrorMessage)){
    add_action( 'admin_notices', '$adminErrorMessage' );
    exit;
}





add_action('init', array('AmplifyLogin','amplifyinit'));

add_action('wp_ajax_verify_key', 'verify_key_callback');
if(!function_exists('verify_key_callback')){
function verify_key_callback() {
    try{
          $amplifyApiKey = $_POST['amplifyApiKey'];
          $amplifyProjectId=$_POST['amplifyProjectId'];
               update_option("_AMPLIFY_API_KEY",$amplifyApiKey);
               update_option("_AMPLIFY_PROJECT_ID",$amplifyProjectId);
               $curlResponse['responseCode']=200;
              echo json_encode($curlResponse);
	die(); // this is required to return a proper result
    }catch(Exception $e){

    }
}
}

}catch(Exception $e){

}

function activation_check() {
	if(is_plugin_active('wpecommerce-betaout/index.php')){
               deactivate_plugins( plugin_basename( __FILE__ ) );
               add_action( 'admin_notices', 'betaout_admin_notice');
               wp_die( 'Please deactive Wpecommerce betaout Plugin' );
   
}
}

function WooCommerce_Betaout_Quick_Export_Plugin_Loader() {
	if( class_exists('Woocommerce') ){
		if(is_admin()){
			$WooCommerce_Betaout_Export_Plugin = new WooCommerce_Betaout_Export_Plugin();
		}
	}
}

add_action( 'plugins_loaded' , 'WooCommerce_Betaout_Quick_Export_Plugin_Loader');

register_activation_hook( __FILE__,'activation_check');



