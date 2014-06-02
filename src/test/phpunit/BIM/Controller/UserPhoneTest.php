<?php

require_once 'Hamcrest.php';

class BIM_Controller_UserPhoneTest extends PHPUnit_Framework_TestCase
{
    private $_userPhoneController = null;

    /**
     * @before
     */
    protected function setUp() {
        $this->clearHttpRequest();
    }

    /**
     * @after
     */
    protected function tearDown() {
        $_userPhoneController = null;
    }

    /**
     * @test
     */
    public function updatePhone_valid_callsUserPhoneApp() {
        // Arrange
        $userId = 921384723;
        $phone = '15555555555';
        $GLOBALS['_POST'] = array( 'userID' => $userId, 'phone' => $phone );
        $controller = $this->getUserPhoneController();
        $observer = $controller->getUserPhoneApp();
        $observer->expects($this->once())
                ->method('updatePhone')
                ->with($this->equalTo($userId), $this->equalTo($phone));

        // Act & assert
        $controller->updatePhone();
    }

    /**
     * @test
     */
    public function validatePhone_valid_callsUserPhoneApp() {
        // Arrange
        $userId = 921384723;
        $pin = 'abcd';
        $GLOBALS['_POST'] = array( 'userID' => $userId, 'pin' => $pin );
        $controller = $this->getUserPhoneController();
        $observer = $controller->getUserPhoneApp();
        $observer->expects($this->once())
                ->method('validatePhone')
                ->with($this->equalTo($userId), $this->equalTo($pin));

        // Act & assert
        $controller->validatePhone();
    }

    /**
     * @test
     * @dataProvider updatePhoneInvalidData
     */
    public function updatePhone_invalid_null( $post ) {
        // Arrange
        $GLOBALS['_POST'] = $post;
        $controller = $this->getUserPhoneController();

        // Act
        $response = $controller->updatePhone();

        // Assert
        assertThat( $response, is(nullValue()) );
    }

    public function updatePhoneInvalidData() {
        return array(
            array(array( )),
            array(array( 'userID' => 921384723 )),
            array(array( 'phone' => '15555555555' )),
            array(array( 'userID' => 921384723, 'phone' => null )),
            array(array( 'userID' => null,      'phone' => '15555555555' )),
            array(array( 'userID' => 921384723, 'phone' => '' )),
            array(array( 'userID' => 0,         'phone' => '15555555555' )),
            // userId instead of userID
            array(array( 'userId' => 921384723, 'phone' => '15555555555' )),
            // Phone instead of phone
            array(array( 'userID' => 921384723, 'Phone' => '15555555555' ))
        );
    }

    /**
     * @test
     * @dataProvider validatePhoneInvalidData
     */
    public function validatePhone_invalid_null( $post ) {
        // Arrange
        $GLOBALS['_POST'] = $post;
        $controller = $this->getUserPhoneController();

        // Act
        $response = $controller->validatePhone();

        // Assert
        assertThat( $response, is(nullValue()) );
    }

    public function validatePhoneInvalidData() {
        return array(
            array(array( )),
            array(array( 'userID' => 921384723 )),
            array(array( 'pin' => 'abcd' )),
            array(array( 'userID' => 921384723, 'pin' => null )),
            array(array( 'userID' => null,      'pin' => 'abcd' )),
            array(array( 'userID' => 921384723, 'pin' => '' )),
            array(array( 'userID' => 0,         'pin' => 'abcd' )),
            // userId instead of userID
            array(array( 'userId' => 921384723, 'pin' => 'abcd' )),
            // Pin instead of pin
            array(array( 'userID' => 921384723, 'Pin' => 'abcd' ))
        );
    }

    protected function getUserPhoneController() {
        if ( is_null($this->_userPhoneController) ) {
            $controller = new BIM_Controller_UserPhone();
            $appStub = $this->getMock( 'BIM_App_UserPhone' );
            $controller->setUserPhoneApp( $appStub );
            $this->_userPhoneController = $controller;
        }
        return $this->_userPhoneController;
    }

    protected function clearHttpRequest() {
        $GLOBALS['_POST'] = array();
        $GLOBALS['_GET'] = array();
    }
}

