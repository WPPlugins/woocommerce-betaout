<?php

/**
 * betaout.com is a marketing automation software and enagegment platform
 *
 * This library provides connectivity with the Amplify API
 *
 * Basic usage:
 *
 * 1. Configure Amplify with your access credentials
 * <code>
 * <?php
 *
 * $amplify = new Amplify('dummy_api_key','dummy_api_secret','dummy_project_id');
 * ?>
 * </code>
 *
 * 2. Make requests to the API
 * <code>
 * <?php
 * $amplify = new Amplify('dummy_app_key','dummy_project_id');
 * amplify->identify('rohit@socialaxishq.com','rohit');
 *
 * ?>
 * </code>
 *
 * @author Rohit Kumar Tyagi <rohit@socialaxishq.com>
 * @copyright Copyright 2013 Betaout Pvt Ltd All rights reserved.
 * @link http://www.betaout.com/
 * @license http://opensource.org/licenses/MIT
 * */
// Check for the required json and curl extensions, the Google API PHP Client won't function without them.

/**
 * amplify.to API
 */
//ini_set("display_errors",1);
if (!class_exists('Amplify')) {
class Amplify {
    /*
     * the amplify ApiKey
     */

    protected $showError = array();
    protected $apiKey;

    /*
     * the amplify ApiSecret
     */
    protected $apiSecret;
    public $hitcount = 0;

    /*
     * the amplify ProjectId
     */
    protected $projectId;

    /*
     * the amplify requesturl
     *
     */
    protected $requestUrl;
    /*
     * the amplify custom URL
     *
     */
    protected $publicationUrl;

    /**
     * amplify host
     */
    private $host = 'api.betaout.com';

    /**
     * amplify version
     */
    private $version = 'v2';

    /*
     * param to be send on amplify
     */
    protected $params;

    /*
     * Computes a Hash-based Message Authentication Code (HMAC) using the SHA1 hash function.
     */
    protected $signatureMethod = 'HMAC-SHA1';

    /*
     * signature based string
     */
    protected $hash;
    /*
     * current time stamp used to create hash
     */
    protected $timeStamp;
    /*
     * ott refer one time token that use to handshake
     */
    protected $ott;

    /**
     * Whether we are in debug mode. This is set by the constructor
     */
    private $debug = true;

    /**
     * If the spider text is found in the current user agent, then return true
     */

    /**
     * gettting device info
     */
    private $botDetect = false;

    /**
     * gettting device info
     */
    private $deviceDetect = 1;

    /**
     * function end point mapping
     */
    protected $functionUrlMap = array(
        'identify' => 'user/identify/',
        'user_events' => 'user/events',
        'user_properties' => 'user/properties',
        'ecommerce_products' => 'ecommerce/products',
        'ecommerce_categories'=>'ecommerce/categories',
        'ecommerce_activities' => 'ecommerce/activities',
        'ecommerce_orders ' => 'ecommerce/orders',
        'campaign_transactional' => 'campaign/transactional'
    );


    /**
     * The constructor
     *
     * @param string $apiKey The Amplify application Key
     * @param string $apiSecret The Amplify application Secret
     * @param string $projectId The Amplify ProjectId
     * @param string $debug Optional debug flag
     * @return void
     * */
   public function __construct($amplifyApiKey = "", $amplifyApiSecret = "", $amplifyProjectId = "", $debug = false) {
        $apiKey = !empty($amplifyApiKey) ? $amplifyApiKey : get_option('_AMPLIFY_API_KEY');
        $apiSecret = !empty($amplifyApiSecret) ? $amplifyApiSecret : get_option('_AMPLIFY_API_SECRET');
        $projectId = !empty($amplifyProjectId) ? $amplifyProjectId : get_option('_AMPLIFY_PROJECT_ID');
        $this->setApiKey($apiKey);
        $this->setApiSecret($apiSecret);
        $this->setProjectId($projectId);
        $this->setPublicationUrl();
        $this->setTimeStamp(time());
        // $this->setOtt();
        $this->debug = $debug;
    }

    private function basicSetUp() {
        if (function_exists('curl_init')) {
            $this->showError[] = 'Amplify PHP SDK requires the CURL PHP extension';
        }

        if (!function_exists('json_decode')) {
            $this->showError[] = 'Amplify PHP SDK requires the JSON PHP extension';
        }

        if (!function_exists('http_build_query')) {
            $this->showError[] = 'Amplify PHP SDK requires http_build_query()';
        }
    }

    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'amplify-php-1.0',
    );

    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
// return $this;
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function setProjectId($projectId) {
        $this->projectId = $projectId;
// return $this;
    }

    public function getProjectId() {
        return $this->projectId;
    }

    public function setPublicationUrl() {
        $this->publicationUrl = "http://".$this->host . "/" . $this->version . "/";
    }

    public function getPublicationUrl() {
        return $this->publicationUrl;
    }


    public function setParams($params) {
        $this->params = $params;
// return $this;
    }

    public function getParams() {
        return $this->params;
    }

    public function setApiSecret($apiSecret) {
        $this->apiSecret = $apiSecret;
// return $this;
    }

    public function getApiSecret() {
        return $this->apiSecret;
    }

    public function getRequestUrl() {
        return $this->requestUrl;
    }

    public function setRequestUrl($requestUrl) {
        $this->requestUrl = $requestUrl;
// return $this;
    }

    public function setTimeStamp($timeStamp) {
        $this->timeStamp = $timeStamp;
    }

    public function getTimeStamp() {
        $timeStamp = $this->timeStamp;
        if (empty($timeStamp))
            $this->setTimeStamp(time());
        return $this->timeStamp;
    }

    public function setOtt() {
        if (isset($_COOKIE['_ampUITN']) && !empty($_COOKIE['_ampUITN'])) {
           $this->ott =$_COOKIE['_ampUITN'];
        }
    }

    public function getOtt() {

        return $this->ott;
    }

    public function makeParams($params = false) {
        //print_r($params);
        if (!is_array($params) && !empty($params))
            $this->showError[] = "paramter should be associative array!";
        $this->setOtt();
        if((!isset($params['identifiers']) && isset($_COOKIE['_ampUSER']))){
            $params['identifiers'] = json_decode(base64_decode($_COOKIE['_ampUSER']),true);
        }else if(isset ($_SESSION['identifiers']) && !isset($params['identifiers'])|| count($params['identifiers'])==0 && isset($_SESSION['identifiers'])){
            $params['identifiers']=  json_decode(base64_decode($_SESSION['identifiers']),true);
             
        }
        if (isset($this->ott)) {
            if($this->ott){
             $params['identifiers']['token']=$this->getOtt();
            }
        }
        
        try {
            if (!isset($params['apiKey']))
                $params['apikey'] = $this->getApiKey();
            if (!isset($params['project_id']))
                $params['project_id'] = $this->getProjectId();
            if (!isset($params['timestamp']))
                $params['timestamp'] = $this->getTimeStamp();
            $paramUrl = json_encode($params);
            $this->setParams($paramUrl);
        } catch (Exception $ex) {
            $this->showError[] = $ex->getCode() . ":" . $ex->getMessage();
        }
    }

    function http_call($functionName, $argumentsArray) {
            $apiKey = $this->getApiKey();
            $projectId = $this->getProjectId();
            if (empty($apiKey))
                $this->showError[] = "Invalid Api call, Api key must be provided!";
            if (empty($projectId))
                $this->showError[] = "Invalid Api call, Project Id must be provided!";
            if (!isset($this->functionUrlMap[$functionName]))
                $this->showError[] = "Invalid Function call!";
            try {
                
                $requestUrl = $this->getPublicationUrl() . $this->functionUrlMap[$functionName]; //there should be error handling to make sure function name exist
                if (isset($argumentsArray) && is_array($argumentsArray) && count($argumentsArray) > 0) {
                    $argumentsArray['useragent'] = $_SERVER['HTTP_USER_AGENT'];
                    $argumentsArray['ip'] = $_SERVER['REMOTE_ADDR'];
                    $this->makeParams($argumentsArray);
                }
                $paramdata=$this->getParams();
                return $this->makeRequest($requestUrl,$paramdata);
            } catch (Exception $ex) {
                $this->showError[] = $ex->getCode() . ":" . $ex->getMessage();
            }
        
    }

    
    protected function makeRequest($requestUrl,$data="", $ch = null) {
      
        if (!$ch) {
            $ch = curl_init();
        }
        $options = self::$CURL_OPTS;
       $options[CURLOPT_URL] = $requestUrl;
       $options[CURLOPT_POSTFIELDS]=$data;
       $options[CURLOPT_CUSTOMREQUEST]="POST";

        if ($this->debug) {
//             echo $requestUrl;
            $options[CURLOPT_VERBOSE] = true;
        }
        $options[CURLOPT_HTTPHEADER] =array('Content-Type: application/json');
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        if ($result === false) {
            $this->showError[] = 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        $retrun = json_decode($result, true);
        if ($retrun['responseCode'] == '500')
            $this->showError[] = $retrun;
        return $retrun;
    }

    

    /*
     * Identify system user if it unknowm leave parameter blank
     * amplify->identify('sandeep@socialaxishq.com','Sandeep');
     * Replace with name and email of current user
     */

    public function identify($data = array()) {
        $cdata=array();
        $identifiers['identifiers']=$data;
        $response = $this->http_call('identify', $identifiers);
           if(count($identifiers['identifiers'])>0){
               $jdata= json_encode($identifiers['identifiers']);
               setcookie('_ampUSER',base64_encode($jdata),time()+604800,'/');
               $_SESSION['identifiers']=base64_encode($jdata);
           }
         return $response;
    }
    

    /*
     * add new event with properties
     * $amplify->event('sandeep@socialaxishq.com',array('addtocart'=>array('product'=>'Samsung Note2','category'=>'Mobile','price'=>'456.78')));
     */

   public function event($eventName) {
       if($eventName!=""){
        $eventArray=array("name"=>$eventName,"timestamp"=>time());
        $argumentsArray = array('events' => array($eventArray));
        return $this->http_call('user_events', $argumentsArray);
       }
    }

    public function product_add($productDetails) {
        return $this->http_call('ecommerce_products', $productDetails);
    }
    
    public function customer_action($actionDescription) {
        $argumentsArray = $actionDescription;
        $argumentsArray['referrer'] = isset($_COOKIE['_ampREF']) ? $_COOKIE['_ampREF'] : "";

        return $this->http_call('ecommerce_activities', $argumentsArray);
    }

    public function update_order($data) {
        return $this->http_call('ecommerce_orders',$data);
    }

    /*
     * add user properties
     * $amplify->update('sandeep@socialaxishq.com',array('country'=>'India','city'=>'Noida'));
     */

    public function userProperties($identifier, $propetyArray) {
        $argumentsArray = array('identifiers' => $identifier, 'properties' => $propetyArray);
        return $this->http_call('user_properties', $argumentsArray);
    }

   

    protected function deviceDetector() {
        if (stripos($_SERVER['HTTP_USER_AGENT'], "Android") && stripos($_SERVER['HTTP_USER_AGENT'], "mobile")) {
            $this->deviceDetect = 'android mobile';
        } else if (stripos($_SERVER['HTTP_USER_AGENT'], "Android")) {
            $this->deviceDetect = 'android tablet';
        } else if (stripos($_SERVER['HTTP_USER_AGENT'], "iPhone")) {
            $this->deviceDetect = 'iphone';
        } else if (stripos($_SERVER['HTTP_USER_AGENT'], "iPad")) {
            $this->deviceDetect = 'ipad';
        } else if (stripos($_SERVER['HTTP_USER_AGENT'], "mobile")) {
            $this->deviceDetect = 'generic mobile';
        } else if (stripos($_SERVER['HTTP_USER_AGENT'], "tablet")) {
            $this->deviceDetect = 'generic tablet';
        } else {
            $this->deviceDetect = 'desktop';
        }
    }

    public function describe() {
//        if ($this->debug)
         return  $this->showError;
    }

}
}

?>