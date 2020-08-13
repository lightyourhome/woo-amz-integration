<?php
/**
 * Handles all Feed Calls to Amazon MWS
 * 
 * @package Amazon MWS PHP SDK
 * @version 0.6.0 
 * @since 0.6.0
 * 
 */

include_once MWS_CONFIG;
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
     * The submission id that is set after initially submitting a feed
     * 
     * @since 0.6.0
     */
    private static $feedSubmissionId = NULL;

    /**
     * The final feed submission result MD5, used to check integrity of result response
     * 
     * @since 0.6.0
     */
    private static $feedResultMD5 = NULL;

    /**
     * Stores invokeSubmitFeed response to return to frontend via REST API
     * 
     * @since 0.10.0
     */
    public static $submit_feed_response = NULL;

    /**
     * Stores invokeGetFeedSubmissionList response to return to frontend via REST API
     * 
     * @since 0.10.0
     */
    public static $feed_list_response = NULL;

    /**
     * Stores invokeGetFeedSubmissionResult response to return to frontend via REST API
     * 
     * @since 0.10.0
     */
    public static $feed_result_response = NULL;

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
    private static function feedSubmissionListData() {

        try {

            if ( ! empty( self::$feedSubmissionId ) ) {

                $parameters = array (

                    'Merchant'                 => MERCHANT_ID,
                    'FeedSubmissionIdList'     => array('Id' => self::$feedSubmissionId), // TODO: Add feed submission ID from SubmitFeed response
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
    private static function submitFeedResultData() {

        try {

            if ( ! empty( self::$feedSubmissionId ) ) {

                $parameters = array (

                    'Merchant'             => MERCHANT_ID,
                    'FeedSubmissionId'     => self::$feedSubmissionId, // TODO: Add Feed Submission ID from SubmitFeed Response
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

        //feed submission response that is returned on frontend
        $status = array(

            'feed_submission_id'             => '',
            'feed_type'                      => '',
            'submitted_date'                 => '',
            'feed_processing_status'         => '',
            'feed_processing_start_date'     => '',
            'feed_processing_completed_date' => '',
            'request_id'                     => '',
            'response_metadata'              => ''

        );

        try {

            $response = $service->submitFeed($request);

            if ( $response->isSetSubmitFeedResult() ) {

                $submitFeedResult = $response->getSubmitFeedResult();

                $responseLogHandle = fopen( WOO_AMZ_RESPONSE_LOG . "-submit-feed-" . time() . ".txt", 'a+' );

                if ( $submitFeedResult->isSetFeedSubmissionInfo() ) {

                    fwrite( $responseLogHandle, "------------------Feed Submission Info------------------------\n" );

                    //Get the feed submission info object
                    $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();

                    if ( $feedSubmissionInfo->isSetFeedSubmissionId() ) {

                        self::$feedSubmissionId = $feedSubmissionInfo->getFeedSubmissionId();

                        $status['feed_submission_id'] = self::$feedSubmissionId;

                        fwrite( $responseLogHandle, "\nFeed Submission ID: " . self::$feedSubmissionId . "\n" );

                    }

                    if ( $feedSubmissionInfo->isSetFeedType() ) {

                        $status['feed_type'] = $feedSubmissionInfo->getFeedType();

                        fwrite( $responseLogHandle, 'Feed Type: ' . $feedSubmissionInfo->getFeedType() . "\n" );

                    }

                    if ( $feedSubmissionInfo->isSetSubmittedDate() ) {

                        $status['submitted_date'] = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);

                        fwrite( $responseLogHandle, 'Submitted Date: ' . $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT) . "\n" );

                    }

                    if ( $feedSubmissionInfo->isSetFeedProcessingStatus() ) {

                        $status['feed_processing_status'] = $feedSubmissionInfo->getFeedProcessingStatus();

                        fwrite( $responseLogHandle, 'Feed Processing Status: ' . $feedSubmissionInfo->getFeedProcessingStatus() . "\n" );

                    }

                    if ( $feedSubmissionInfo->isSetStartedProcessingDate() ) {

                        $status['feed_processing_start_date'] = $feedSubmissionInfo->getStartedProcessingDate()->format(DATA_FORMAT);

                        fwrite( $responseLogHandle, 'Feed Processing Start Date: ' . $feedSubmissionInfo->getStartedProcessingDate()->format(DATA_FORMAT) . "\n" );

                    }

                    if ( $feedSubmissionInfo->isSetCompletedProcessingDate() ) {

                        $status['feed_processing_completed_date'] = $feedSubmissionInfo->getCompletedProcessingDate()->format(DATA_FORMAT);

                        fwrite( $responseLogHandle, 'Feed Processing Completed Date: ' . $feedSubmissionInfo->getCompletedProcessingDate()->format(DATA_FORMAT) . "\n" );

                    }

                }

                if ( $response->isSetResponseMetadata() ) {

                    fwrite( $responseLogHandle, "---------------------Response Metadata---------------------\n" );

                    $responseMetadata = $response->getResponseMetadata();

                    if ( $responseMetadata->isSetRequestId() ) {

                        $status['request_id'] = $responseMetadata->getRequestId();

                        fwrite( $responseLogHandle, $responseMetadata->getRequestId() . "\n" );

                    }

                } 

                $status['response_metadata'] = $response->getResponseHeaderMetadata();

                fwrite( $responseLogHandle, $response->getResponseHeaderMetadata() . "\n");

                fclose( $responseLogHandle );

                self::$submit_feed_response = $status;

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

            exit;
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

        //feed list response that is returned on frontend
        $status = array(

            'next_token'                    => '',
            'has_next'                      => '',
            'feed_submission_id'            => '',
            'feed_type'                     => '',
            'submitted_date'                => '',
            'feed_processing_status'        => '',
            'feed_processing_date'          => '',
            'completed_processing_date'     => '',
            'request_id'                    => '',
            'response_header_metadata'      => ''

        );
    
        try 
        {
            $response = $service->getFeedSubmissionList($request);

            $responseLogHandle = fopen( WOO_AMZ_RESPONSE_LOG . "-feed-sub-list-" . time() . ".txt", 'a+' );



            if ( $response->isSetGetFeedSubmissionListResult() ) { 
                
                $getFeedSubmissionListResult = $response->getGetFeedSubmissionListResult();

                if ( $getFeedSubmissionListResult->isSetNextToken() ) 
                {

                    $next_token = $getFeedSubmissionListResult->getNextToken();

                    $status['next_token'] = $next_token;

                    fwrite($responseLogHandle, "\nNext Token: " . $next_token . "\n");

                }
                if ( $getFeedSubmissionListResult->isSetHasNext() ) 
                {

                    $has_next = $getFeedSubmissionListResult->getHasNext();

                    $status['has_next'] = $has_next;

                    fwrite( $responseLogHandle, "Has Next: " . $has_next . "\n" );

                }

                $feedSubmissionInfoList = $getFeedSubmissionListResult->getFeedSubmissionInfoList();

                foreach ( $feedSubmissionInfoList as $feedSubmissionInfo ) {

                    fwrite( $responseLogHandle, "-----------------------FeedSubmissionInfo---------------------\n");
                    if ( $feedSubmissionInfo->isSetFeedSubmissionId() ) 
                    {

                        $feed_submission_id = $feedSubmissionInfo->getFeedSubmissionId();

                        $status['feed_submission_id'] = $feed_submission_id;

                        fwrite( $responseLogHandle, "Feed Submission Id: " . $feed_submission_id . "\n");

                    }
                    if ( $feedSubmissionInfo->isSetFeedType() ) 
                    {

                        $feed_type = $feedSubmissionInfo->getFeedType();

                        $status['feed_type'] = $feed_type;

                        fwrite( $responseLogHandle, "Feed Type: " . $feed_type . "\n" );

                    }
                    if ( $feedSubmissionInfo->isSetSubmittedDate() ) 
                    {

                        $submitted_date = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);

                        $status['submitted_date'] = $submitted_date;

                        fwrite( $responseLogHandle, "Submitted Date: " . $submitted_date . "\n" );

                    }
                    if ( $feedSubmissionInfo->isSetFeedProcessingStatus() ) 
                    {
                        
                        /**
                         * TODO:
                         * 
                         * Feed processing status will need to be passed to additional method
                         * that will invoke feed list again to check for completion
                         * 
                         */
                        $feed_processing_status = $feedSubmissionInfo->getFeedProcessingStatus();

                        $status['feed_processing_status'] = $feed_processing_status;

                        fwrite( $responseLogHandle, "Feed Processing Status: " . $feed_processing_status . "\n" );

                    }
                    if ( $feedSubmissionInfo->isSetStartedProcessingDate() ) 
                    {

                        $processing_start_date = $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT);

                        $status['feed_processing_date'] = $processing_start_date;

                        fwrite( $responseLogHandle, "FeedProcessingDate: " . $processing_start_date . "\n");

                    }
                    if ( $feedSubmissionInfo->isSetCompletedProcessingDate() ) 
                    {

                        $completed_processing_date = $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT);

                        $status['completed_processing_date'] = $completed_processing_date;

                        fwrite( $responseLogHandle, "Completed Processing Date: " . $completed_processing_date . "\n"); 

                    }

                }

            } 
            if  ( $response->isSetResponseMetadata() ) { 

                fwrite( $responseLogHandle, "---------------------Response Metadata---------------------\n");

                $responseMetadata = $response->getResponseMetadata();

                if ( $responseMetadata->isSetRequestId() ) 
                {

                    $request_id = $responseMetadata->getRequestId();

                    $status['request_id'] = $request_id;

                    fwrite( $responseLogHandle, "Request ID: " . $request_id . "\n");

                }
            }

            $response_header_metadata = $response->getResponseHeaderMetadata();

            $status['response_header_metadata'] = $response_header_metadata;

            fwrite( $responseLogHandle, "Response Header Metadata: " . $response_header_metadata . "\n" );

            fclose( $responseLogHandle );

            //store $status array in prop $feed_list_response
            self::$feed_list_response = $status;


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

            exit;
            
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

        //feed list response that is returned on frontend
        $status = array(

            'result'                      => '',
            'result_MD5'                  => '',
            'request_id'                  => '',
            'response_header_metadata'    => '',

        );

        try {

            $response = $service->getFeedSubmissionResult($request);

            $responseLogHandle = fopen( WOO_AMZ_RESPONSE_LOG . "-feed-result-list-" . time() . ".txt", 'a+' );
            
            fwrite("---------------------------Response Data-----------------------------\n");

            if ( $response->isSetGetFeedSubmissionResultResult() ) {

                $getFeedSubmissionResultResult = $response->getGetFeedSubmissionResultResult(); 

                $status['result'] = $getFeedSubmissionResultResult;

                fwrite( "Feed Submission Result: " . $getFeedSubmissionResultResult . "\n" );
                
                if ( $getFeedSubmissionResultResult->isSetContentMd5() ) {

                    self::$feedResultMD5 = $getFeedSubmissionResultResult->getContentMd5();

                    $status['result_MD5'] = self::$feedResultMD5;

                    fwrite( $responseLogHandle, "Content MD5: " . self::$feedResultMD5 . "\n");

                }

            }

            if ( $response->isSetResponseMetadata() ) { 

                fwrite("---------------------Response Metadata----------------------\n");

                $responseMetadata = $response->getResponseMetadata();
                
                if ( $responseMetadata->isSetRequestId() ) 
                {

                    $request_id = $responseMetadata->getRequestId();

                    $status['status_id'] = $request_id;

                    fwrite( $responseLogHandle, "Request ID: " . $request_id . "\n" );

                }
            } 

            $response_header_metadata = $response->getResponseHeaderMetadata();

            $status['response_header_metadata'] = $response_header_metadata;

            self::$feed_result_response = $status;

            fwrite( $responseLogHandle, "ResponseHeaderMetadata: " . $response_header_metadata . "\n");

            fclose( $responseLogHandle );

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

            exit;

        }

    }
  
} //END class TFS_MWS_FEED