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

    /**
     * HTTP Config for MWS Client
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


    function __construct() {






    }





}