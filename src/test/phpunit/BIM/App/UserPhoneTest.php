<?php

require_once 'Hamcrest.php';

class BIM_App_UserPhoneTest extends PHPUnit_Framework_TestCase
{
    /**
     * @before
     */
    protected function setUp() {
    }

    /**
     * @after
     */
    protected function tearDown() {
    }

//    /**
//     * @test
//     */
//    public function updatePhone_valid_callsUserPhoneApp() {
//        // Arrange
//        $userId = 921384723;
//        $phone = '15555555555';
//        $GLOBALS['_POST'] = array( 'userID' => $userId, 'phone' => $phone );
//        $controller = $this->getNewUserPhoneApp();
//        $observer = $controller->getUserPhoneApp();
//        $observer->expects($this->once())
//                ->method('updatePhone')
//                ->with($this->equalTo($userId), $this->equalTo($phone));
//
//        // Act & assert
//        $controller->updatePhone();
//    }
//
//    /**
//     * @test
//     */
//    public function validatePhone_valid_callsUserPhoneApp() {
//        // Arrange
//        $userId = 921384723;
//        $pin = 'abcd';
//        $GLOBALS['_POST'] = array( 'userID' => $userId, 'pin' => $pin );
//        $controller = $this->getNewUserPhoneApp();
//        $observer = $controller->getUserPhoneApp();
//        $observer->expects($this->once())
//                ->method('validatePhone')
//                ->with($this->equalTo($userId), $this->equalTo($pin));
//
//        // Act & assert
//        $controller->validatePhone();
//    }

    /**
     * @test
     * @dataProvider updatePhoneInvalidData
     * @expectedException InvalidArgumentException
     */
    public function updatePhone_invalid_null( $userId, $phone ) {
        // Arrange
        $app = $this->getNewUserPhoneApp();

        // Act & assert
        $response = $app->updatePhone( $userId, $phone );
    }

    public function updatePhoneInvalidData() {
        return array(
            //-----$userId---+$phone
            array( null,      null ),
            array( 921384723, null ),
            array( null,      '15555555555' ),
            array( 921384723,  null ),
            array( null,       '15555555555' ),
            array( 921384723,  '' ),
            array( 0,          '15555555555' ),
        );
    }

    /**
     * @test
     * @dataProvider validatePhoneInvalidData
     * @expectedException InvalidArgumentException
     */
    public function validatePhone_invalid_null( $userId, $pin ) {
        // Arrange
        $app = $this->getNewUserPhoneApp();

        // Act & assert
        $response = $app->validatePhone( $userId, $pin );
    }

    public function validatePhoneInvalidData() {
        return array(
            //-----$userId---+$phone
            array( null,      null ),
            array( 921384723, null ),
            array( null,      'abcd' ),
            array( 921384723, null ),
            array( null,      'abcd' ),
            array( 921384723, '' ),
            array( 0,         'abcd' ),
        );
    }

    /**
     * @test
     */
    public function updatePhone_nonexistantUser_false() {
        // Arrange
        $userId = 83962387;
        $phone = '12025550105';
        $appMock = $this->getMock( 'BIM_App_UserPhone', array('userExists') );
        $appMock->expects($this->once())
                ->method('userExists')
                ->with($this->equalTo($userId))
                ->will($this->returnValue(false));

        // Act
        $result = $appMock->updatePhone( $userId, $phone );

        // Assert
        assertThat( $result, is(equalTo(false)) );
    }

    /**
     * @test
     */
    public function validatePhone_nonexistantUser_false() {
        // Arrange
        $userId = 7820934;
        $phone = '18085550149';
        $appMock = $this->getMock( 'BIM_App_UserPhone', array('userExists') );
        $appMock->expects($this->once())
                ->method('userExists')
                ->with($this->equalTo($userId))
                ->will($this->returnValue(false));

        // Act
        $result = $appMock->validatePhone( $userId, $phone );

        // Assert
        assertThat( $result, is(equalTo(false)) );
    }








//    /**
//     * @test
//     */
//    public function getUserPhoneApp_nothing_lazyLoads() {
//        // Arrange
//        $controller = new BIM_App_UserPhone();
//
//        // Act
//        $app = $controller->getUserPhoneApp();
//
//        // Assert
//        assertThat( $app, is(not(nullValue())) );
//        assertThat( $app, is(anInstanceOf('BIM_App_UserPhone')) );
//    }
//
//    /**
//     * @test
//     */
//    public function getUserPhoneApp_setAll_identical() {
//        // Arrange
//        $controller = new BIM_App_UserPhone();
//        $appStub = $this->getMock( 'BIM_App_UserPhone' );
//
//        // Act
//        $controller->setUserPhoneApp( $appStub );
//        $app = $controller->getUserPhoneApp();
//
//        // Assert
//        assertThat( $app, is(identicalTo($appStub)) );
//    }
//
//    /**
//     * @test
//     * @expectedException UnexpectedValueException
//     */
//    public function setUserPhoneApp_setTwice_exception() {
//        // Arrange
//        $controller = new BIM_App_UserPhone();
//        $appStub = $this->getMock( 'BIM_App_UserPhone' );
//
//        // Act & Assert
//        $controller->setUserPhoneApp( $appStub );
//        $controller->setUserPhoneApp( $appStub );
//    }

    protected function getNewUserPhoneApp() {
        $controller = new BIM_App_UserPhone();
        //$appStub = $this->getMock( 'BIM_App_UserPhone' );
        //$controller->setUserPhoneApp( $appStub );
        return $controller;
    }
}

