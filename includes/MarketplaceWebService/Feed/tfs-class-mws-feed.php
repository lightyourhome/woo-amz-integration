<?php
/**
 * Handles all Feed Calls to Amazon MWS
 * 
 * @package Amazon MWS PHP SDK
 * @version 0.6.0 
 * @since 0.6.0
 * 
 */

include_once ('.config.inc.php'); 
require_once WOO_AMZ_PLUGIN_DIR . 'includes/MarketplaceWebService/Client.php';

class TFS_MWS_FEED {

    /**
    * Service URL for MWS
    * 
    * @since 0.6.0
    */
    private static $serviceUrl = "https://mws.amazonservices.com";

    /**
     * HTTP Config prop
     * 
     * @since 0.6.0
     */
    private static $httpConfig = NULL;

    /**
     * Construct New TFS_MWS_FEED
     * 
     * @param string - the phase of the feed to run
     */
    function __construct( $action = null ) {

        $this->$httpConfig = self::getHttpClient();

        if ( $action !== NULL ) {

            if ( $action == 'SubmitFeed' ) {

                self::invokeSubmitFeed( $this->$httpConfig, self::submitFeedData() );
    
            }
    
            if ( $action == 'FeedList' ) {
    
                self::invokeGetFeedSubmissionList( $this->$httpConfig, self::feedSubmissionListData() );
    
            }
    
            if ( $action == 'FeedResult' ) {
    
                self::invokeGetFeedSubmissionResult( $this->$httpConfig, self::submitFeedResultData() );
    
            }
    
        }

    }

    /**
     * HTTP Config for MWS Client
     * 
     * @since 0.6.0
     */
    private static function getHttpClient() {

        $config = array(

            'ServiceURL'    => self::$serviceUrl,
            'ProxyHost'     => null,
            'ProxyPort'     => -1,
            'MaxErrorRetry' => 3,

        );

        return new MarketplaceWebService_Client(

            AWS_ACCESS_KEY_ID, 
            AWS_SECRET_ACCESS_KEY, 
            $config,
            APPLICATION_NAME,
            APPLICATION_VERSION
            
        );
                
    }

    /**
     * Provides the required parameters for making a submit feed request to class MarketplaceWebService_Model_SubmitFeedRequest
     * and returns an instance for use in method invokeSubmitFeed
     * 
     * @return object - the SubmitFeed object
     */
    private static function submitFeedData() {

        try {

            $feedHandle = fopen( ABSPATH . 'wp-content/uploads/amz_inventory.txt', 'r');

            $parameters = array (
    
                'Merchant'             => MERCHANT_ID,
                'MarketplaceIdList'    => array( 'Id' => array('ATVPDKIKX0DER') ),
                'FeedType'             => '_POST_INVENTORY_AVAILABILITY_DATA_',
                'FeedContent'          => $feedHandle,
                'PurgeAndReplace'      => false,
                'ContentMd5'           => base64_encode(md5(stream_get_contents($feedHandle), true)),
                'MWSAuthToken'         => MERCHANT_ID, // Optional
    
            );
                    
            $request = new MarketplaceWebService_Model_SubmitFeedRequest( $parameters );
            
            return $request;
    
            fclose($feedHandle);

        } catch ( TFS_MWS_FEED $ex ) {





        }

    }

    /**
    * Provides the required parameters for making a submit feed List request to class MarketplaceWebService_Model_GetFeedSubmissionListRequest
    * and returns an instance for use in method invokeGetFeedSubmissionList
    * 
    * @return object - the GetFeedSubmissionList object
    */
    private static function feedSubmissionListData( $feedSubmissionId = '50097018471' ) {

        try {

            if ( ! empty( $feedSubmissionId ) ) {

                $parameters = array (

                    'Merchant'                 => MERCHANT_ID,
                    'FeedSubmissionIdList'     => array('Id' => $feedSubmissionId), // TODO: Add feed submission ID from SubmitFeed response
                    'FeedProcessingStatusList' => array ('Status' => array ('_SUBMITTED_', '_DONE_') ),
                    'MWSAuthToken'             => MERCHANT_ID, // Optional
        
                );
                   
                $request = new MarketplaceWebService_Model_GetFeedSubmissionListRequest( $parameters );
                   
                return $request;

            }

        } catch ( TFS_MWS_FEED $ex ) {





        }

    }

    /**
    * Provides the required parameters for making a submit feed result request to class MarketplaceWebService_Model_GetFeedSubmissionResultRequest
    * and returns an instance for use in method invokeGetFeedSubmissionResult
    * 
    * @return object - the GetFeedSubmissionResultRequest object
    */
    private static function submitFeedResultData( $feedSubmissionId = '50097018471' ) {

        try {

            if ( ! empty( $feedSubmissionId ) ) {

                $parameters = array (

                    'Merchant'             => MERCHANT_ID,
                    'FeedSubmissionId'     => 50097018471, // TODO: Add Feed Submission ID from SubmitFeed Response
                    'FeedSubmissionResult' => fopen( WOO_AMZ_RESPONSE_LOG, 'rw+' ), //TODO add file creation for responses
                    'MWSAuthToken'         => MERCHANT_ID, // Optional
        
                );
                   
                $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest( $parameters );
        
                return $request;
    
            }

        } catch ( TFS_MWS_FEED $ex ) {



        }

    }

    //TODO SUBMISSION ID NEEDS TO BE WRITTEN TO DATABASE
    /**
    * Submit Feed Action Sample
    * Uploads a file for processing together with the necessary
    * metadata to process the file, such as which type of feed it is.
    * PurgeAndReplace if true means that your existing e.g. inventory is
    * wiped out and replace with the contents of this feed - use with
    * caution (the default is false).
    *   
    * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
    * @param mixed $request MarketplaceWebService_Model_SubmitFeed or array of parameters
    */
    private static function invokeSubmitFeed( $service, $request ) {

        try {

            $response = $service->submitFeed($request);

            if ( $response->isSetSubmitFeedResult() ) {

                $finalSubmitFeedResult = array(

                    'FeedSubmissionId'        => '',
                    'FeedType'                => '',
                    'SubmittedDate'           => '',
                    'FeedProcessingStatus'    => '',
                    'StartedProcessingDate'   => '',
                    'CompletedProcessingDate' => '',
                    'HeaderResponseMetaData'  => '',
                    'RequestId'               => ''

                );

                $submitFeedResult = $response->getSubmitFeedResult();

                if ( $submitFeedResult->isSetFeedSubmissionInfo() ) {

                    //Get the feed submission info object
                    $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();

                    if ( $feedSubmissionInfo->isSetFeedSubmissionId() ) {

                        $finalSubmitFeedResult['FeedSubmissionId'] = $feedSubmissionInfo->getFeedSubmissionId();

                    }

                    if ( $feedSubmissionInfo->isSetFeedType() ) {

                        $finalSubmitFeedResult['FeedType'] = $feedSubmissionInfo->getFeedType();

                    }

                    if ( $feedSubmissionInfo->isSetSubmittedDate() ) {


                        $finalSubmitFeedResult['SubmittedDate'] = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);

                    }

                    if ( $feedSubmissionInfo->isSetFeedProcessingStatus() ) {

                        $finalSubmitFeedResult['FeedProcessingStatus'] = $feedSubmissionInfo->getFeedProcessingStatus();

                    }

                    if ( $feedSubmissionInfo->isSetStartedProcessingDate() ) {

                        $finalSubmitFeedResult['StartedProcessingDate'] = $feedSubmissionInfo->getStartedProcessingDate()->format(DATA_FORMAT);

                    }

                    if ( $feedSubmissionInfo->isSetCompletedProcessingDate() ) {

                        $finalSubmitFeedResult['CompletedProcessingDate'] = $feedSubmissionInfo->getCompletedProcessingDate()->format(DATA_FORMAT);

                    }

                }

                if ( $response->isSetResponseMetadata() ) {

                    $responseMetadata = $response->getResponseMetadata();

                    if ( $responseMetadata->isSetRequestId() ) {

                        $finalSubmitFeedResult['RequestId'] = $responseMetadata->getRequestId();

                    }

                }

                $finalSubmitFeedResult['CompletedProcessingDate'] = $response->getResponseHeaderMetadata();

            }
                
        } catch (MarketplaceWebService_Exception $ex) {

            $handle = fopen( WOO_AMZ_ERROR_LOG, 'a+');

            fwrite( $handle, time() . "\n" );
            fwrite( $handle, 'Caught Exception: ' . $ex->getMessage() . "\n");
            fwrite( $handle, 'Response Status Code: ' . $ex->getStatusCode() . "\n");
            fwrite( $handle, 'Error Code: ' . $ex->getErrorCode() . "\n");
            fwrite( $handle, 'Error Type: ' . $ex->getErrorType() . "\n");
            fwrite( $handle, 'Request ID: ' . $ex->getRequestId() . "\n");
            fwrite( $handle, 'XML: ' . $ex->getXML() . "\n");
            fwrite( $handle, 'ResponseHeaderMetaData: ' . $ex->getResponseHeaderMetadata() . "\n");
            fwrite( $handle, "END ERROR\n\n");

            fclose( $handle );

        }

    }

    /**
     * Get Feed Submission List Action
    * returns a list of feed submission identifiers and their associated metadata
    *   
    * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
    * @param mixed $request MarketplaceWebService_Model_GetFeedSubmissionList or array of parameters
    */
    private static function invokeGetFeedSubmissionList($service, $request) 
    {
    
        try 
        {
            $response = $service->getFeedSubmissionList($request);
            
            echo ("Service Response\n");
            echo ("=============================================================================\n");

            echo("        GetFeedSubmissionListResponse\n");
            if ($response->isSetGetFeedSubmissionListResult()) { 
                echo("            GetFeedSubmissionListResult\n");
                $getFeedSubmissionListResult = $response->getGetFeedSubmissionListResult();
                if ($getFeedSubmissionListResult->isSetNextToken()) 
                {
                    echo("                NextToken\n");
                    echo("                    " . $getFeedSubmissionListResult->getNextToken() . "\n");
                }
                if ($getFeedSubmissionListResult->isSetHasNext()) 
                {
                    echo("                HasNext\n");
                    echo("                    " . $getFeedSubmissionListResult->getHasNext() . "\n");
                }
                $feedSubmissionInfoList = $getFeedSubmissionListResult->getFeedSubmissionInfoList();
                foreach ($feedSubmissionInfoList as $feedSubmissionInfo) {
                    echo("                FeedSubmissionInfo\n");
                    if ($feedSubmissionInfo->isSetFeedSubmissionId()) 
                    {
                        echo("                    FeedSubmissionId\n");
                        echo("                        " . $feedSubmissionInfo->getFeedSubmissionId() . "\n");
                    }
                    if ($feedSubmissionInfo->isSetFeedType()) 
                    {
                        echo("                    FeedType\n");
                        echo("                        " . $feedSubmissionInfo->getFeedType() . "\n");
                    }
                    if ($feedSubmissionInfo->isSetSubmittedDate()) 
                    {
                        echo("                    SubmittedDate\n");
                        echo("                        " . $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT) . "\n");
                    }
                    if ($feedSubmissionInfo->isSetFeedProcessingStatus()) 
                    {
                        echo("                    FeedProcessingStatus\n");
                        echo("                        " . $feedSubmissionInfo->getFeedProcessingStatus() . "\n");
                    }
                    if ($feedSubmissionInfo->isSetStartedProcessingDate()) 
                    {
                        echo("                    StartedProcessingDate\n");
                        echo("                        " . $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT) . "\n");
                    }
                    if ($feedSubmissionInfo->isSetCompletedProcessingDate()) 
                    {
                        echo("                    CompletedProcessingDate\n");
                        echo("                        " . $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT) . "\n");
                    }
                }
            } 
            if ($response->isSetResponseMetadata()) { 
                echo("            ResponseMetadata\n");
                $responseMetadata = $response->getResponseMetadata();
                if ($responseMetadata->isSetRequestId()) 
                {
                    echo("                RequestId\n");
                    echo("                    " . $responseMetadata->getRequestId() . "\n");
                }
            } 

            echo("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
        } catch (MarketplaceWebService_Exception $ex) {
            echo("Caught Exception: " . $ex->getMessage() . "\n");
            echo("Response Status Code: " . $ex->getStatusCode() . "\n");
            echo("Error Code: " . $ex->getErrorCode() . "\n");
            echo("Error Type: " . $ex->getErrorType() . "\n");
            echo("Request ID: " . $ex->getRequestId() . "\n");
            echo("XML: " . $ex->getXML() . "\n");
            echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
        }

    }


    /**
     * Get Feed Submission Result Action Sample
    * retrieves the feed processing report
    *   
    * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
    * @param mixed $request MarketplaceWebService_Model_GetFeedSubmissionResult or array of parameters
    */
    private static function invokeGetFeedSubmissionResult($service, $request) 
    {
        try {
                $response = $service->getFeedSubmissionResult($request);
                
                    echo ("Service Response\n");
                    echo ("=============================================================================\n");
    
                    echo("        GetFeedSubmissionResultResponse\n");
                    if ($response->isSetGetFeedSubmissionResultResult()) {
                    $getFeedSubmissionResultResult = $response->getGetFeedSubmissionResultResult(); 
                    echo ("            GetFeedSubmissionResult");
                    
                    if ($getFeedSubmissionResultResult->isSetContentMd5()) {
                        echo ("                ContentMd5");
                        echo ("                " . $getFeedSubmissionResultResult->getContentMd5() . "\n");
                    }
                    }
                    if ($response->isSetResponseMetadata()) { 
                        echo("            ResponseMetadata\n");
                        $responseMetadata = $response->getResponseMetadata();
                        if ($responseMetadata->isSetRequestId()) 
                        {
                            echo("                RequestId\n");
                            echo("                    " . $responseMetadata->getRequestId() . "\n");
                        }
                    } 
    
                    echo("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
            } catch (MarketplaceWebService_Exception $ex) {
            echo("Caught Exception: " . $ex->getMessage() . "\n");
            echo("Response Status Code: " . $ex->getStatusCode() . "\n");
            echo("Error Code: " . $ex->getErrorCode() . "\n");
            echo("Error Type: " . $ex->getErrorType() . "\n");
            echo("Request ID: " . $ex->getRequestId() . "\n");
            echo("XML: " . $ex->getXML() . "\n");
            echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
        }
    }

  
} //END class TFS_MWS_FEED