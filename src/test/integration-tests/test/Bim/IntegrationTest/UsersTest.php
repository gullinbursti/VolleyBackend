<?php

require_once 'Hamcrest.php';
require_once 'CurlHelpers.php';
require_once 'ObjectHelpers.php';
require_once 'CommonValidators.php';

class Bim_IntegrationTest_UsersTest extends PHPUnit_Framework_TestCase
{
    //-------------------------------------------------------------------------
    // Action - getSubscribees()
    //-------------------------------------------------------------------------
    /**
     * @test
     */
    public function getSubscribees_validRequest_validResponse()
    {
        $config = $this->getConfiguration()->usersGetSubscribees();
        $url = $config->url;
        $userId = $config->existent->userId;

        // Arrange
        $expected_properties = array( 'accept_time', 'init_time', 'source',
            'state', 'target', 'user'
        );
        $user_expected_properties = array( 'avatar_url', 'id', 'username' );

        $queryData = (object) array(
            'userID' => $userId
        );

        // Act
        $response = curlPostQueryReturnJson( $url, $queryData );
        $jsonResponse = $response->httpBodyJson;

        // Assert
        validateCurlResponse( $response );
        assertThat( $jsonResponse, is(nonEmptyArray()) );

        foreach ($jsonResponse as $entry) {
            $props = getProperties( $entry );
            assertThat( $props,
                is(arrayContainingInAnyOrder($expected_properties))
            );
            validateId( $entry->target );
            validateId( $entry->source );
            assertThat( $entry->accept_time, is(integerValue()) );
            validateEpoc( $entry->init_time );
            assertThat( $entry->state, is(integerValue()) );

            $user = $entry->user;
            assertThat( $user, is(anObject()) );
            $user_props = getProperties( $user );
            assertThat( $user_props,
                is(arrayContainingInAnyOrder($user_expected_properties))
            );
            validateId( $user->id );
            validateUsername( $user->username );
            validateUrl( $user->avatar_url );
        }
    }

    /**
     * @test
     * @dataProvider getSubscribees_invalidParams_dataProvider
     */
    public function getSubscribees_invalidParams_returnsEmptyArray( $userId )
    {
        $config = $this->getConfiguration()->usersGetSubscribees();
        $url = $config->url;
        $queryData = (object) array(
            'userID' => $userId
        );

        // Act
        $response = curlPostQueryReturnJson( $url, $queryData );
        $jsonResponse = $response->httpBodyJson;

        // Assert
        validateCurlResponse( $response );
        assertThat( $jsonResponse, is(emptyArray()) );
    }

    public function getSubscribees_invalidParams_dataProvider()
    {
        return array(
            array(''),
            array(null)
        );
    }

    /**
     * @test
     */
    public function getSubscribees_nonexistentUser_returnsEmptyArray()
    {
        $config = $this->getConfiguration()->usersGetSubscribees();
        $url = $config->url;
        $userId = $config->nonexistent->userId;
        $queryData = (object) array(
            'userID' => $userId
        );

        // Act
        $response = curlPostQueryReturnJson( $url, $queryData );
        $jsonResponse = $response->httpBodyJson;

        // Assert
        validateCurlResponse( $response );
        assertThat( $jsonResponse, is(emptyArray()) );
    }

    //-------------------------------------------------------------------------
    // Action - processImage()
    //-------------------------------------------------------------------------
    /**
     * @test
     */
    public function processImage_validRequest_validResponse()
    {
        $config = $this->getConfiguration()->usersProcessImage();
        $url = $config->url;
        $imgUrl = $config->imgUrl;

        // Arrange
        $expected_properties = array( 'result' );
        $queryData = (object) array(
            'imgURL' => $imgUrl
        );

        // Act
        $response = curlPostQueryReturnJson( $url, $queryData );
        $jsonResponse = $response->httpBodyJson;

        // Assert
        validateCurlResponse( $response );
        assertThat( $jsonResponse, is(anObject()) );

        $props = getProperties( $jsonResponse );
        assertThat( $props,
            is(arrayContainingInAnyOrder($expected_properties))
        );
        assertThat( $jsonResponse->result, is(equalTo(true)) );
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
