<?php

require_once 'Hamcrest.php';

class BIM_Controller_UserPhoneTest extends PHPUnit_Framework_TestCase
{
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
    }

    /**
     * @test
     */
    public function updatePhone_valid_callsUserPhoneApp() {
        // Arrange
        $userId = 921384723;
        $phone = '15555555555';
        $GLOBALS['_POST'] = array( 'userID' => $userId, 'phone' => $phone );
        $controller = $this->getNewUserPhoneController();
        $observer = $controller->getUserPhoneApp();
        $observer->expects($this->once())
                ->method('createOrUpdatePhone')
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
        $phone = '18085550125';
        $pin = 'abcd';
        $GLOBALS['_POST'] = array(
            'userID' => $userId,
            'phone' => $phone,
            'pin' => $pin
        );
        $controller = $this->getNewUserPhoneController();
        $observer = $controller->getUserPhoneApp();
        $observer->expects($this->once())
                ->method('validatePhone')
                ->with($this->equalTo($userId),
                    $this->equalTo($phone),
                    $this->equalTo($pin));

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
        $controller = $this->getNewUserPhoneController();

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
        $controller = $this->getNewUserPhoneController();

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
            array(array( 'phone' => '13175550130' )),
            array(array( 'userID' => 921384723, 'phone' => '16085550106', 'pin' => null )),
            array(array( 'userID' => null,      'phone' => '16085550106', 'pin' => 'abcd' )),
            array(array( 'userID' => 921384723, 'phone' => '16085550106', 'pin' => '' )),
            array(array( 'userID' => 0,         'phone' => '16085550106', 'pin' => 'abcd' )),
            array(array( 'userID' => 921384723, 'phone' => '', 'pin' => null )),
            array(array( 'userID' => null,      'phone' => '', 'pin' => 'abcd' )),
            array(array( 'userID' => 921384723, 'phone' => '', 'pin' => '' )),
            array(array( 'userID' => 0,         'phone' => '', 'pin' => 'abcd' )),
            array(array( 'userID' => 921384723, 'phone' => null, 'pin' => null )),
            array(array( 'userID' => null,      'phone' => null, 'pin' => 'abcd' )),
            array(array( 'userID' => 921384723, 'phone' => null, 'pin' => '' )),
            array(array( 'userID' => 0,         'phone' => null, 'pin' => 'abcd' )),

            // userId instead of userID
            array(array( 'userId' => 921384723, 'pin' => 'abcd' )),
            // Pin instead of pin
            array(array( 'userID' => 921384723, 'Pin' => 'abcd' )),
            // Phone instead of phone
            array(array( 'userID' => 921384723, 'pin' => 'abcd', 'Phone' => '18435550113' ))
        );
    }

    /**
     * @test
     */
    public function getUserPhoneApp_nothing_lazyLoads() {
        // Arrange
        $controller = new BIM_Controller_UserPhone();

        // Act
        $app = $controller->getUserPhoneApp();

        // Assert
        assertThat( $app, is(not(nullValue())) );
        assertThat( $app, is(anInstanceOf('BIM_App_UserPhone')) );
    }

    /**
     * @test
     */
    public function getUserPhoneApp_setAll_identical() {
        // Arrange
        $controller = new BIM_Controller_UserPhone();
        $appStub = $this->getMock( 'BIM_App_UserPhone' );

        // Act
        $controller->setUserPhoneApp( $appStub );
        $app = $controller->getUserPhoneApp();

        // Assert
        assertThat( $app, is(identicalTo($appStub)) );
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function setUserPhoneApp_setTwice_exception() {
        // Arrange
        $controller = new BIM_Controller_UserPhone();
        $appStub = $this->getMock( 'BIM_App_UserPhone' );

        // Act & Assert
        $controller->setUserPhoneApp( $appStub );
        $controller->setUserPhoneApp( $appStub );
    }

    //-------------------------------------------------------------------------
    // isValid()
    //-------------------------------------------------------------------------
    /**
     * @test
     * @dataProvider isValid_badUser_dataSource
     */
    public function isValid_badUser_returnsNull( $userId ) {
        $GLOBALS['_POST'] = array(
            'userID' => $userId
        );
        $controller = $this->getNewUserPhoneController();

        // Act
        $actual = $controller->isValid($userId);

        // Assert
        assertThat( $actual, is(equalTo(null)) );
    }

    public function isValid_badUser_dataSource() {
        return array(
            array(null),
            array(0)
        );
    }

    /**
     * @test
     * @dataProvider isValid_goodUser_dataSource
     */
    public function isValid_goodUser_returnsStatus( $userId, $status, $expected ) {
        $GLOBALS['_POST'] = array(
            'userID' => $userId
        );
        $controller = $this->getNewUserPhoneController();
        $appMock = $controller->getUserPhoneApp();
        $appMock->expects($this->once())
            ->method('isValid')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($status));

        // Act
        $actual = $controller->isValid($userId);

        // Assert
        assertThat( $actual, is(equalTo($expected)) );
    }

    public function isValid_goodUser_dataSource() {
        return array(
            array(42842459, 1, true),
            array(42842459, 100, true),
            array(42842459, 555, true),
            array(42842459, true, true),
            array(42842459, 0, false),
            array(42842459, null, false),
            array(42842459, false, false)
        );
    }

    //-------------------------------------------------------------------------
    // Unit test helpers
    //-------------------------------------------------------------------------
    protected function getNewUserPhoneController() {
        $controller = new BIM_Controller_UserPhone();
        $appStub = $this->getMock( 'BIM_App_UserPhone' );
        $controller->setUserPhoneApp( $appStub );
        return $controller;
    }

    protected function clearHttpRequest() {
        $GLOBALS['_POST'] = array();
        $GLOBALS['_GET'] = array();
    }
}

