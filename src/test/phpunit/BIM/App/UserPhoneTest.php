<?php

require_once 'Hamcrest.php';

class BIM_App_UserPhoneTest extends PHPUnit_Framework_TestCase
{
    const PHONE_ENC_REGEX = '/^[\/\\+\w]+==$/';

    //-------------------------------------------------------------------------
    // Unit test fixtures
    //-------------------------------------------------------------------------
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

    //-------------------------------------------------------------------------
    // createOrUpdatePhone()
    //-------------------------------------------------------------------------
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
    public function validatePhone_invalid_null( $userId, $phone, $pin ) {
        // Arrange
        $app = $this->getNewUserPhoneApp();

        // Act & assert
        $response = $app->validatePhone( $userId, $phone, $pin );
    }

    public function validatePhoneInvalidData() {
        return array(
            //-----$userId---+$phone---------+$pin
            array( null,      '15555555555',  null ),
            array( 921384723, '15555555555',  null ),
            array( null,      '15555555555',  'abcd' ),
            array( 921384723, '15555555555',  null ),
            array( null,      '15555555555',  'abcd' ),
            array( 921384723, '15555555555',  '' ),
            array( 0,         '15555555555',  'abcd' ),
            array( null,      '',             null ),
            array( 921384723, '',             null ),
            array( null,      '',             'abcd' ),
            array( 921384723, '',             null ),
            array( null,      '',             'abcd' ),
            array( 921384723, '',             '' ),
            array( 0,         '',             'abcd' ),
            array( null,      null,           null ),
            array( 921384723, null,           null ),
            array( null,      null,           'abcd' ),
            array( 921384723, null,           null ),
            array( null,      null,           'abcd' ),
            array( 921384723, null,           '' ),
            array( 0,         null,           'abcd' ),
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
        $appMock = $this->getNewUserPhoneApp( true );
        $daoMock = $appMock->getUserPhoneDao();
        $daoMock->expects( $this->once() )
            ->method( 'readExistingByUserId' )
            ->with( $this->equalTo($userId) )
            ->will( $this->returnValue(null) );
        $daoMock->expects( $this->once() )
            ->method( 'create' )
            ->with( $this->equalTo($userId),
                    $this->logicalAnd($this->logicalNot($this->equalTo($phone)),
                        $this->matchesRegularExpression(self::PHONE_ENC_REGEX)),
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
        $appMock = $this->getNewUserPhoneApp( true );
        $daoMock = $appMock->getUserPhoneDao();
        $daoMock->expects( $this->once() )
            ->method( 'readExistingByUserId' )
            ->with( $this->equalTo($userId) )
            ->will( $this->returnValue($daoReadResponse) );
        $daoMock->expects( $this->once() )
            ->method( 'updateNewPhone' )
            ->with( $this->equalTo($phoneId),
                    $this->equalTo($userId),
                    $this->logicalAnd($this->logicalNot($this->equalTo($phone)),
                        $this->matchesRegularExpression(self::PHONE_ENC_REGEX)),
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
    public function createOrUpdatePhone_allValid_callsNexmo()
    {
        // Arrange
        $userId = 411005;
        $phone = '18505550114';
        $pin = 'KU9LBS1LVA';

        $app = $this->getNewUserPhoneApp( true, $pin );
        $nexmoStub = $app->getNexmoTwoFactorAuth();
        $nexmoStub->expects($this->once())
                ->method('sendPin')
                ->with( $this->equalTo($phone), $this->equalTo($pin) )
                ->will($this->returnValue(true));

        // Act & assert
        $app->createOrUpdatePhone( $userId, $phone );
    }

    //-------------------------------------------------------------------------
    // validatePhone()
    //-------------------------------------------------------------------------
    /**
     * @test
     */
    public function validatePhone_nonexistantUser_returnsFalse() {
        // Arrange
        $userId = 7820934;
        $phone = '18085550149';
        $pin = '786F4ZQES5';
        $appMock = $this->getNewUserPhoneApp( false );

        // Act
        $result = $appMock->validatePhone( $userId, $phone, $pin );

        // Assert
        assertThat( $result, is(equalTo(false)) );
    }

    /**
     * Check to make sure parameters fed in are passed.
     *
     * @test
     */
    public function validatePhone_validPin_validateisPinWithValidParams() {
        // Arrange
        $userId = 94346;
        $phone = '15035550143';
        $pin = 'SfXcEiwtIu';
        $appMock = $this->getNewUserPhoneApp( true );
        $daoMock = $appMock->getUserPhoneDao();
        $daoMock->expects( $this->once() )
            ->method( 'updateVerifyPhonePin' )
            ->with( $this->equalTo($userId),
                    $this->logicalAnd($this->logicalNot($this->equalTo($phone)),
                        $this->matchesRegularExpression(self::PHONE_ENC_REGEX)),
                    $this->equalTo($pin)
                )
            ->will($this->returnValue(true));

        // Act & assert
        $appMock->validatePhone( $userId, $phone, $pin );
    }

    /**
     * @test
     */
    public function validatePhone_validPin_returnsTrueOnValidation() {
        // Arrange
        $userId = 7784118;
        $phone = '14045550161';
        $pin = 'rjv14nvzwlc8rr7op';
        $appMock = $this->getNewUserPhoneApp( true );
        $daoMock = $appMock->getUserPhoneDao();
        $daoMock->expects( $this->once() )
            ->method( 'updateVerifyPhonePin' )
            ->will($this->returnValue(true));

        // Act
        $result = $appMock->validatePhone( $userId, $phone, $pin );

        // Assert
        assertThat( $result, is(equalTo(true)) );
    }

    /**
     * @test
     */
    public function validatePhone_validPin_callsAppUserVerifyPhone() {
        // Arrange
        $userId = 891800099;
        $phone = '+16025550171';
        $pin = 'T5L32XE495NSB';
        $appMock = $this->getNewUserPhoneApp( true );
        $daoMock = $appMock->getUserPhoneDao();
        $daoMock->expects( $this->once() )
            ->method( 'updateVerifyPhonePin' )
            ->will($this->returnValue(true));

        // TODO - add $params check.  It being a map is a PITA!!!
        $userAppMock = $appMock->getUserApp();
        $userAppMock->expects( $this->once() )
            ->method( 'verifyPhone' );

        // Act & assert
        $result = $appMock->validatePhone( $userId, $phone, $pin );
    }

    /**
     * @test
     */
    public function validatePhone_invalidPin_updatesFailureCounts() {
        // Arrange
        $userId = 645830;
        $phone = '18085550118';
        $pin = 'pli3u258jcmuzw05q';
        $appMock = $this->getNewUserPhoneApp( true );
        $daoMock = $appMock->getUserPhoneDao();
        $daoMock->expects( $this->once() )
            ->method( 'updateVerifyPhonePin' )
            ->will($this->returnValue(false));

        $daoMock->expects( $this->once() )
            ->method( 'updatePhonePinVerifyFailed' )
            ->with( $this->equalTo($userId),
                    $this->logicalAnd($this->logicalNot($this->equalTo($phone)),
                        $this->matchesRegularExpression(self::PHONE_ENC_REGEX))
                )
            ->will($this->returnValue(true));

        // Act & assert
        $appMock->validatePhone( $userId, $phone, $pin );
    }

    /**
     * @test
     */
    public function validatePhone_invalidPin_returnsFalse() {
        // Arrange
        $userId = 7535295;
        $phone = '19165550192';
        $pin = '9occc9q5p3fenxilv';
        $appMock = $this->getNewUserPhoneApp( true );
        $daoMock = $appMock->getUserPhoneDao();
        $daoMock->expects( $this->once() )
            ->method( 'updateVerifyPhonePin' )
            ->will($this->returnValue(false));

        $daoMock->expects( $this->once() )
            ->method( 'updatePhonePinVerifyFailed' )
            ->will($this->returnValue(true));

        // Act
        $result = $appMock->validatePhone( $userId, $phone, $pin );

        // Assert
        assertThat( $result, is(equalTo(false)) );
    }

    //-------------------------------------------------------------------------
    // getUserPhoneDao() & setUserPhoneDao()
    //-------------------------------------------------------------------------
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

    //-------------------------------------------------------------------------
    // getNexmoTwoFactorAuth() & setNexmoTwoFactorAuth
    //-------------------------------------------------------------------------
    /**
     * @test
     */
    public function getNexmoTwoFactorAuth_nothing_lazyLoads() {
        // Arrange
        $app = new BIM_App_UserPhone();

        // Act
        $nexmo = $app->getNexmoTwoFactorAuth();

        // Assert
        assertThat( $nexmo, is(not(nullValue())) );
        assertThat( $nexmo, is(anInstanceOf('BIM_Integration_Nexmo_TwoFactorAuth')) );
    }

    /**
     * @test
     */
    public function getNexmoTwoFactorAuth_setAll_identical() {
        // Arrange
        $app = new BIM_App_UserPhone();
        $nexmoStub = $this->getMockBuilder( 'BIM_Integration_Nexmo_TwoFactorAuth' )
                ->disableOriginalConstructor()
                ->getMock();

        // Act
        $app->setNexmoTwoFactorAuth( $nexmoStub );
        $nexmo = $app->getNexmoTwoFactorAuth();

        // Assert
        assertThat( $nexmo, is(identicalTo($nexmoStub)) );
    }

    //-------------------------------------------------------------------------
    // getUserApp() & setUserApp
    //-------------------------------------------------------------------------
    /**
     * @test
     */
    public function getUserApp_nothing_lazyLoads() {
        // Arrange
        $app = new BIM_App_UserPhone();

        // Act
        $app = $app->getUserApp();

        // Assert
        assertThat( $app, is(not(nullValue())) );
        assertThat( $app, is(anInstanceOf('BIM_App_Users')) );
    }

    /**
     * @test
     */
    public function getUserApp_setAll_identical() {
        // Arrange
        $app = new BIM_App_UserPhone();
        $appStub = $this->getMockBuilder( 'BIM_App_Users' )
                ->disableOriginalConstructor()
                ->getMock();

        // Act
        $app->setUserApp( $appStub );
        $app = $app->getUserApp();

        // Assert
        assertThat( $app, is(identicalTo($appStub)) );
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function setNexmoTwoFactorAuth_setTwice_exception() {
        // Arrange
        $app = new BIM_App_UserPhone();
        $nexmoStub = $this->getMockBuilder( 'BIM_Integration_Nexmo_TwoFactorAuth' )
                ->disableOriginalConstructor()
                ->getMock();

        // Act & Assert
        $app->setNexmoTwoFactorAuth( $nexmoStub );
        $app->setNexmoTwoFactorAuth( $nexmoStub );
    }

    //-------------------------------------------------------------------------
    // generateVerifyCode()
    //-------------------------------------------------------------------------
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

    /**
     * @test
     */
    public function generateVerifyCode_none_isString() {
        // Arrange
        $appMock = $this->getNewUserPhoneApp();
        $chars = BIM_App_UserPhone::VERIFY_CODE_CHARS;
        $regx = "/^[$chars]+$/";

        // Act & assert - get a bit of a large sample
        assertThat( $appMock->generateVerifyCode(), is(typeOf('string')) );
        assertThat( $appMock->generateVerifyCode(), is(typeOf('string')) );
        assertThat( $appMock->generateVerifyCode(), is(typeOf('string')) );
        assertThat( $appMock->generateVerifyCode(), is(typeOf('string')) );
        assertThat( $appMock->generateVerifyCode(), is(typeOf('string')) );
    }

    //-------------------------------------------------------------------------
    // Unit test helpers
    //-------------------------------------------------------------------------
    protected function getNewUserPhoneApp( $userExists = null,
            $fakeGenerateVerifyCode = null ) {

        // Figure out what to stub
        $whatToStub = array( 'userExists' );
        if ( !is_null($fakeGenerateVerifyCode) ) {
            $whatToStub[] = 'generateVerifyCode';
        }

        // Create mock
        $appMock = $this->getMock( 'BIM_App_UserPhone', $whatToStub );

        if ( is_null( $userExists ) ) {
            // userExists() calls DB, so always block!!!
            $appMock->expects($this->any())
                ->method('userExists')
                ->will($this->throwException(new BadFunctionCallException(
                        "userExists() blocked for unit testing.")));
        } else if ( is_bool($userExists) ) {
            $appMock->expects($this->any())
                ->method('userExists')
                ->will($this->returnValue($userExists));
        } else {
            throw new InvalidArgumentException(
                "'userExists' must be null, or boolean." );
        }

        if ( !is_null($fakeGenerateVerifyCode) ) {
            $appMock->expects($this->any())
                ->method('generateVerifyCode')
                ->will($this->returnValue($fakeGenerateVerifyCode));
        }

        // Fake the DB connection
        $daoStub = $this->getMockBuilder( 'BIM_DAO_Mysql_UserPhone' )
                ->disableOriginalConstructor()
                ->getMock();
        $appMock->setUserPhoneDao( $daoStub );

        // Fake Nexmo connection
        $nexmoStub = $this->getMock( 'BIM_Integration_Nexmo_TwoFactorAuth' );
        $appMock->setNexmoTwoFactorAuth( $nexmoStub );

        // Fake BIM_App_Users
        $appStub = $this->getMock( 'BIM_App_Users' );
        $appMock->setUserApp( $appStub );

        return $appMock;
    }
}

