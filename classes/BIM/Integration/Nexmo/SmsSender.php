<?php
class BIM_Integration_Nexmo_SmsSender {

    private $_config = null;

    public function __construct( $config = null ) {
        $this->_config = $config;
    }

    public function send( $phone, $message )
    {
        //----
        // Prepare for call
        $endPoint = $this->_config->sendSmsEndpoint;
        $from = $this->_config->from;
        $query = array(
            'api_key' => $this->_config->apiKey,
            'api_secret' => $this->_config->apiSecret,
            'to' => "$phone",
            'from' => "$from",
            'text' => "$message"
        );

        // You really want to turn the query into a URL string so that the
        // HTTP POST Content-Type is application/x-www-form-urlencoded.
        // Anything else will likely cause problems with Nexmo.
        $queryString = http_build_query( $query );

        $curlOptions = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $queryString,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_VERBOSE => false,
            CURLOPT_FAILONERROR => true,
        );

        //----
        // Make web-service call
        $handle = curl_init( $endPoint );
        curl_setopt_array( $handle, $curlOptions );
        $curlResponse = curl_exec($handle);
        $curlErrorNumber = curl_errno( $handle );
        $curlError = curl_error( $handle );
        curl_close($handle);
        $nexmoSuccessful = $this->nexmoCallSuccessful( $curlResponse );

        //----
        // Post processing
        // TODO - Add logging on error

        // Return true IFF curl error num is 0, and nexmoSuccessful is true
        return ($curlErrorNumber == 0) && $nexmoSuccessful;
    }

    protected function nexmoCallSuccessful( $jsonString ) {
        $json = json_decode( $jsonString );

        //error_log( "HERE ==> " . print_r($json, true) );

        if ( $json
            && $json->{'message-count'} == 1
            && $json->messages[0]->status == 0
        ) {
            $successful = true;
        } else {
            $successful = false;
        }

        return $successful;
    }
}

