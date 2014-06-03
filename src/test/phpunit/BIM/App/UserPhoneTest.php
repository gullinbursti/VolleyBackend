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
//    public function createOrUpdatePhone_valid_callsUserPhoneApp() {
//        // Arrange
//        $userId = 921384723;
//        $phone = '15555555555';
//        $GLOBALS['_POST'] = array( 'userID' => $userId, 'phone' => $phone );
//        $controller = $this->getNewUserPhoneApp();
//        $observer = $controller->getUserPhoneApp();
//        $observer->expects($this->once())
//                ->method('createOrUpdatePhone')
//                ->with($this->equalTo($userId), $this->equalTo($phone));
//
//        // Act & assert
//        $controller->createOrUpdatePhone();
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
     * @dataProvider createOrUpdatePhoneInvalidData
     * @expectedException InvalidArgumentException
     */
    public function createOrUpdatePhone_invalid_null( $userId, $phone ) {
        // Arrange
        $app = $this->getNewUserPhoneApp();

        // Act & assert
        $response = $app->createOrUpdatePhone( $userId, $phone );
    }

    public function createOrUpdatePhoneInvalidData() {
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
    public function createOrUpdatePhone_nonexistantUser_false() {
        // Arrange
        $userId = 83962387;
        $phone = '12025550105';
        $appMock = $this->getMock( 'BIM_App_UserPhone', array('userExists') );
        $appMock->expects($this->once())
                ->method('userExists')
                ->with($this->equalTo($userId))
                ->will($this->returnValue(false));

        // Act
        $result = $appMock->createOrUpdatePhone( $userId, $phone );

        // Assert
        assertThat( $result, is(equalTo(false)) );
    }


    /**
     * @test
     */
    public function createOrUpdatePhone_newUserNewPhone_true() {
        // Arrange
        $userId = 108655;
        $phone = '15025550172';
        $appMock = $this->getNewUserPhoneApp();
        $daoMock = $appMock->getUserPhoneDao();
        $daoMock->expects( $this->once() )
            ->method( 'create' )
            ->with( $this->equalTo($userId),
                    $this->logicalAnd($this->logicalNot($this->equalTo($phone)),
                        $this->matchesRegularExpression("/^\w+==$/")),
                    // TODO: More constraints
                    $this->anything(),
                    // TODO: More constraints
                    $this->anything() )
            ->will($this->returnValue(111));

        // Act & assert (expects)
        $result = $appMock->createOrUpdatePhone( $userId, $phone );

        // Assert
        assertThat( $result, is(equalTo(true)) );
    }

    /**
     * @test
     */
    public function createOrUpdatePhone_existingUserNewPhone_true() {
        // Arrange
        $userId = 3481;
        $phone = '16515550125';
        $phoneId = 96710;
        $daoReadResponse = (object) array( 'id' => $phoneId );
        $appMock = $this->getNewUserPhoneApp();
        $daoMock = $appMock->getUserPhoneDao();
        $daoMock->expects( $this->once() )
            ->method( 'readByUserId' )
            ->with( $this->equalTo($userId) )
            ->will( $this->returnValue($daoReadResponse) );
        $daoMock->expects( $this->once() )
            ->method( 'updateNewPhone' )
            ->with( $this->equalTo($phoneId),
                    $this->equalTo($userId),
                    $this->logicalAnd($this->logicalNot($this->equalTo($phone)),
                        $this->matchesRegularExpression("/^\w+==$/")),
                    // TODO: More constraints
                    $this->anything(),
                    // TODO: More constraints
                    $this->anything() )
            ->will($this->returnValue(true));

        // Act & assert (expects)
        $result = $appMock->createOrUpdatePhone( $userId, $phone );

        // Assert
        assertThat( $result, is(equalTo(true)) );
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

    /**
     * @test
     */
    public function getUserPhoneDao_nothing_lazyLoads() {
        // Arrange
        $app = new BIM_App_UserPhone();

        // Act
        $dao = $app->getUserPhoneDao();

        // Assert
        assertThat( $dao, is(not(nullValue())) );
        assertThat( $dao, is(anInstanceOf('BIM_DAO_Mysql_UserPhone')) );
    }

    /**
     * @test
     */
    public function getUserPhoneDao_setAll_identical() {
        // Arrange
        $app = new BIM_App_UserPhone();
        $daoStub = $this->getMockBuilder( 'BIM_DAO_Mysql_UserPhone' )
                ->disableOriginalConstructor()
                ->getMock();

        // Act
        $app->setUserPhoneDao( $daoStub );
        $dao = $app->getUserPhoneDao();

        // Assert
        assertThat( $dao, is(identicalTo($daoStub)) );
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function setUserPhoneDao_setTwice_exception() {
        // Arrange
        $app = new BIM_App_UserPhone();
        $daoStub = $this->getMockBuilder( 'BIM_DAO_Mysql_UserPhone' )
                ->disableOriginalConstructor()
                ->getMock();

        // Act & Assert
        $app->setUserPhoneDao( $daoStub );
        $app->setUserPhoneDao( $daoStub );
    }

    /**
     * @test
     */
    public function generateVerifyCode_none_correctLength() {
        // Arrange
        $appMock = $this->getNewUserPhoneApp();
        $length = BIM_App_UserPhone::VERIFY_CODE_LENGTH;

        // Act & assert - get a bit of a large sample
        assertThat( strlen($appMock->generateVerifyCode()), is(equalTo($length)) );
        assertThat( strlen($appMock->generateVerifyCode()), is(equalTo($length)) );
        assertThat( strlen($appMock->generateVerifyCode()), is(equalTo($length)) );
        assertThat( strlen($appMock->generateVerifyCode()), is(equalTo($length)) );
        assertThat( strlen($appMock->generateVerifyCode()), is(equalTo($length)) );
    }

    /**
     * @test
     */
    public function generateVerifyCode_none_correctChars() {
        // Arrange
        $appMock = $this->getNewUserPhoneApp();
        $chars = BIM_App_UserPhone::VERIFY_CODE_CHARS;
        $regx = "/^[$chars]+$/";

        // Act & assert - get a bit of a large sample
        assertThat( $appMock->generateVerifyCode(), matchesPattern($regx) );
        assertThat( $appMock->generateVerifyCode(), matchesPattern($regx) );
        assertThat( $appMock->generateVerifyCode(), matchesPattern($regx) );
        assertThat( $appMock->generateVerifyCode(), matchesPattern($regx) );
        assertThat( $appMock->generateVerifyCode(), matchesPattern($regx) );
    }

    protected function getNewUserPhoneApp() {
        $appMock = $this->getMock( 'BIM_App_UserPhone', array('userExists') );

        // All users exist!
        $appMock->expects($this->any())
                ->method('userExists')
                ->will($this->returnValue(true));

        // Fake the DB connection
        $daoStub = $this->getMockBuilder( 'BIM_DAO_Mysql_UserPhone' )
                ->disableOriginalConstructor()
                ->getMock();

        $appMock->setUserPhoneDao( $daoStub );
        return $appMock;
    }
}

