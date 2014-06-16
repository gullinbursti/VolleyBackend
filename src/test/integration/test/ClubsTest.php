<?php

require_once 'Hamcrest.php';
require_once 'CurlHelpers.php';
require_once 'ObjectHelpers.php';
require_once 'CommonValidators.php';

class BIM_integration_endpoint_ClubsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function get_validRequest_validResponse()
    {
        $config = $this->getConfiguration()->clubsGet();
        $url = $config->urlGet;
        $clubId = $config->existent->clubId;
        $userId = $config->existent->userId;

        // Arrange
        $expected_properties = array( 'added', 'blocked', 'club_type',
            'description', 'id', 'img', 'members', 'name', 'owner', 'pending',
            'submissions', 'total_members', 'total_score', 'total_submissions',
            'updated'
        );
        $queryData = (object) array(
            'clubID' => $clubId,
            'userID' => $userId
        );

        // Act
        $response = curlPostQueryReturnJson( $url, $queryData );
        $jsonResponse = $response->httpBodyJson;
        $props = getProperties( $jsonResponse );

        // Assert
        validateCurlResponse( $response );
        assertThat( $props, is(arrayContainingInAnyOrder($expected_properties)) );

        validateId( $jsonResponse->id );
        validateClubType( $jsonResponse->club_type );
        validateDateTimeString( $jsonResponse->added );
        validateDateTimeString( $jsonResponse->updated );

        assertThat( $jsonResponse->name, is(stringValue()) );
        assertThat( $jsonResponse->description, is(stringValue()) );
        assertThat( $jsonResponse->total_members, is(integerValue()) );
        assertThat( $jsonResponse->total_score, is(integerValue()) );
        assertThat( $jsonResponse->total_submissions, is(integerValue()) );

        // TODO - add deeper checks for the following
        assertThat( $jsonResponse->members, is(arrayValue()) );
        assertThat( $jsonResponse->blocked, is(arrayValue()) );
        assertThat( $jsonResponse->submissions, is(arrayValue()) );
        assertThat( $jsonResponse->pending, is(arrayValue()) );
        assertThat( $jsonResponse->owner, is(anObject()) );
    }

    //-------------------------------------------------------------------------
    // Test helpers
    //-------------------------------------------------------------------------
    protected function getConfiguration()
    {
        return BIM_IntegrationTest_IntegrationTestContext::getContext()
            ->getConfiguration();
    }
}
?>
