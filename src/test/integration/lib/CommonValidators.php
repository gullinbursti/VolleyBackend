<?

require_once 'Hamcrest.php';
require_once 'CurlHelpers.php';

function validateDateTimeString( $dateTime )
{
    $dateTimeRegex = "/^\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d$/";
    assertThat($dateTime, matchesPattern($dateTimeRegex));
}

function validateId( $id )
{
    assertThat( $id, is(numericValue()) );
}

function validateClubType( $type )
{
    $regex = '/^(FEATURE|NEARBY|SCHOOL|STAFF_CREATED|THIRD_PARTY|USER_GENERATED)$/';
    assertThat($type, matchesPattern($regex));
}

function validateCurlResponse( $response )
{
    assertThat( $response->curlErrorNumber, is(equalTo(CURLE_OK)));
    assertThat( $response->httpCode, is(equalTo(200)));
    assertThat( $response->curlError, is(emptyString()));
}

?>
