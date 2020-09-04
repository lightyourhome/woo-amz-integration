<?php

/**
* Class Responsible for registering and deregistering MWS Destinations
*
* DESTINATION WAS ALREADY REGISTERED. THIS WILL NOT WORK PROPERLY.
* 
* @since 0.11.0
* @version 0.1.0
*/

require_once(MWS_CONFIG);
require_once WOO_AMZ_PLUGIN_DIR . 'includes/MWSSubscriptionsService/Client.php';
require_once WOO_AMZ_PLUGIN_DIR . 'includes/MWSSubscriptionsService/Model/RegisterDestinationInput.php';
require_once WOO_AMZ_PLUGIN_DIR . 'includes/MWSSubscriptionsService/Model/RegisterDestinationResponse.php';
require_once WOO_AMZ_PLUGIN_DIR . 'includes/MWSSubscriptionsService/Model/RegisterDestinationResult.php';



class MWS_Destination {

    /**
     * MWS Subscription service Endpoint
     * 
     * @since 0.11.0
     */
    private static $serviceUrl = "https://mws.amazonservices.com/Subscriptions/2013-07-01";

    function __construct( $action = NULL ) {

        if ( $action == 'RegisterDestination' ) {

            self::invokeRegisterDestination( self::getSubscriptionServiceHttpClient(),  self::createRegisterDestinationRequest() );

        }

    }

    /**
     * HTTP Config for MWS Subscriptions Client
     * 
     * @since 0.6.0
     */
    private static function getSubscriptionServiceHttpClient() {

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

    /**
     * Creates the request passed to method invokeRegisterDestination
     * 
     * @since 0.11.0
     */
    private static function createRegisterDestinationRequest() {

        $params = array(

            'SellerId'      => MERCHANT_ID,
            'MWSAuthToken'  => MERCHANT_ID,
            'MarketplaceId' => 'ATVPDKIKX0DER',
            'Destination'   => array( 'DeliveryChannel' => 'SQS', 'AttributeList' => array( 'sqsQueueUrl' => 'https://sqs.us-east-2.amazonaws.com/838401002254/woo_amz_queue') ),

        );

        $request = new MWSSubscriptionsService_Model_RegisterDestinationInput( $params );



    //    $request->setSellerId(MERCHANT_ID);
    //    $request->setMarketplaceId( 'ATVPDKIKX0DER' );

    //    $request->setMWSAuthToken(MERCHANT_ID);
    //    $request->setDestination( 'DeliveryChannel' => 'SQS', 'AttributeList' => array( 'sqsQueueUrl' => 'https://sqs.us-east-2.amazonaws.com/838401002254/woo_amz_queue') ) );

        return $request;
       
    }

    /**
     * Get Register Destination Action Sample
    * Gets competitive pricing and related information for a product identified by
    * the MarketplaceId and ASIN.
    *
    * @param MWSSubscriptionsService_Interface $service instance of MWSSubscriptionsService_Interface
    * @param mixed $request MWSSubscriptionsService_Model_RegisterDestination or array of parameters
    */
    private static function invokeRegisterDestination( $service, $request)
    {

        try {
            
            $response = $service->RegisterDestination($request);

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