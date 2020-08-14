<?php

/**
* Class responsible for Creating and Updating MWS Subscriptions
* 
* @version 0.1.0
* @since 0.11.0
*/

require_once(MWS_CONFIG);


class MWS_Subscriptions {

    /**
     * MWS Subscriptions endpoint
     * 
     * @since 0.11.0
     */
    private static $serviceUrl = "https://mws.amazonservices.com/Subscriptions/2013-07-01";


    function __construct( $action = NULL ) {

        if ( $action == 'CreateSubscription' ) {

            self::invokeCreateSubscription( self::getHttpClient(), self::createSubscriptionRequest() );

        }

    }
    
    private static function getHttpClient() {

        $config = array (
            'ServiceURL' => self::$serviceUrl,
            'ProxyHost' => null,
            'ProxyPort' => -1,
            'ProxyUsername' => null,
            'ProxyPassword' => null,
            'MaxErrorRetry' => 3,
        );
         
        $service = new MWSSubscriptionsService_Client(
            
            AWS_ACCESS_KEY_ID,
            AWS_SECRET_ACCESS_KEY,
            APPLICATION_NAME,
            APPLICATION_VERSION,
            $config
        );
         
        return $service;


    }

    private static function createSubscriptionRequest() {

        $params = array(

            'SellerId'       => MERCHANT_ID,
            'MWSAuthToken'   => MERCHANT_ID,
            'MarketplaceId'  => 'ATVPDKIKX0DER',
            'Subscription'   => 'FeedProcessingFinished'

        );


        $request = new MWSSubscriptionsService_Model_CreateSubscriptionInput( $params );
       
        return $request;

    }

    private static function invokeCreateSubscription($service, $request)
    {
        try {
          $response = $service->CreateSubscription($request);
  
          echo ("Service Response\n");
          echo ("=============================================================================\n");
  
          $dom = new DOMDocument();
          $dom->loadXML($response->toXML());
          $dom->preserveWhiteSpace = false;
          $dom->formatOutput = true;
          echo $dom->saveXML();
          echo("ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
  
       } catch (MWSSubscriptionsService_Exception $ex) {
          echo("Caught Exception: " . $ex->getMessage() . "\n");
          echo("Response Status Code: " . $ex->getStatusCode() . "\n");
          echo("Error Code: " . $ex->getErrorCode() . "\n");
          echo("Error Type: " . $ex->getErrorType() . "\n");
          echo("Request ID: " . $ex->getRequestId() . "\n");
          echo("XML: " . $ex->getXML() . "\n");
          echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
       }
   }

}