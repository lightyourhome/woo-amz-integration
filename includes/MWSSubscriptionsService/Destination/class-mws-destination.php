<?php

/**
* Class Responsible for registering and deregistering MWS Destinations
* 
* @since 0.11.0
* @version 0.1.0
*/

require_once(MWS_CONFIG);
require_once WOO_AMZ_PLUGIN_DIR . 'includes/MWSSubscriptionsService/Client.php';


class MWS_Destination {

    /**
     * MWS Subscription service Endpoint
     * 
     * @since 0.11.0
     */
    private static $serviceUrl = "https://mws.amazonservices.com/Subscriptions/2013-07-01";

    function __construct() {






    }

    /**
     * HTTP Config for MWS Subscriptions Client
     * 
     * @since 0.6.0
     */
    private static function getSubscriptionServiceHttpClient() {

        $config = array(

            'ServiceURL'    => self::$serviceUrl,
            'ProxyHost'     => null,
            'ProxyPort'     => -1,
            'MaxErrorRetry' => 3,

        );

        $service = new MWSSubscriptionsService_Client(
            AWS_ACCESS_KEY_ID,
            AWS_SECRET_ACCESS_KEY,
            APPLICATION_NAME,
            APPLICATION_VERSION,
            $config
        );

    }

    /**
     * Creates the request passed to method invokeRegisterDestination
     * 
     * @since 0.11.0
     */
    private static function createRegisterDestinationRequest() {

        $request = new MWSSubscriptionsService_Model_RegisterDestinationInput();

        $request->setSellerId(MERCHANT_ID);

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
    private static function invokeRegisterDestination(MWSSubscriptionsService_Interface $service, $request)
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