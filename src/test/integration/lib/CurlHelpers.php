<?php

function curlPostQueryReturnJson( $endPoint, $queryData )
{
    $response = curlPostQuery( $endPoint, $queryData );
    if ( $response->httpContentType == 'application/json' ) {
        $httpBodyJson = !empty($response->httpBodyString)
            ? json_decode($response->httpBodyString)
            : '';
    } else {
        $httpBodyJson = null;
    }

    $response->httpBodyJson = $httpBodyJson;
    return $response;
}

function curlPostQuery( $endPoint, $queryData )
{
    //----
    // Prepare for call
    $queryString = http_build_query( $queryData );
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
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    $httpContentType = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);
    curl_close($handle);

    $response = (object) array(
        'httpBodyString' => $curlResponse,
        'httpCode' => $httpCode,
        'httpContentType' => $httpContentType,
        'curlErrorNumber' => $curlErrorNumber,
        'curlError' => $curlError
    );

    //----
    // Post processing
    return $response;
}

?>
