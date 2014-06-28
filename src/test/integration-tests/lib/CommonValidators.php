<?

require_once 'Hamcrest.php';
require_once 'CurlHelpers.php';

function validateDateTimeString( $dateTime )
{
    $dateTimeRegex = "/^\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d$/";
    assertThat($dateTime, matchesPattern($dateTimeRegex));
}

function validateEpoc( $epoc )
{
    assertThat( $epoc, is(numericValue()) );
    assertThat( $epoc, is(greaterThan(0)) );
}

function validateId( $id )
{
    assertThat( $id, is(numericValue()) );
}

function validateUsername( $username )
{
    assertThat( $username, is(stringValue()) );
}

function validateUrl( $url )
{
    assertThat( $url, is(stringValue()) );
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
