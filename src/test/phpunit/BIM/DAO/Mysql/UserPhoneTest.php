<?php

require_once 'Hamcrest.php';

class BIM_DAO_Mysql_UserPhoneTest extends PHPUnit_Framework_TestCase
{
    private static $_testUserId1 = null;
    private static $_testUserId2 = null;

    private $_userPhoneDao = null;
    private $_existentUserPhoneId = null;

    protected static function user1() {
        return (object) array(
            'username' => 'UNITTEST_USER_NAME1_Q5C789FDBQ7OARIR6690C55CZXR6EIHDOUF8X4CL',
            'adId' => 'UNITTEST_ADID1_YIFE38HQNSG10T1IBB'
        );
    }

    protected static function user2() {
        return (object) array(
            'username' => 'UNITTEST_USER_NAME2_VVBDZQVOAQ83IYV57CW9S70RXJ39MXFLT73Q6N5L',
            'adId' => 'UNITTEST_ADID2_N3P8L12Y0XJIDTW529'
        );
    }

    protected static function nonexistentUserPhone() {
        return (object) array(
            'userId' => self::$_testUserId1,
            'phoneNumberEnc' => 'UNITTEST_PHONE_N_NHC90SQD8P8W2NXXZQB8CSZA2136J1VVY643NMHV',
            'verifyCode' => '1234',
            'verifyCountDown' => 5
        );
    }

    protected static function existentUserPhone() {
        return (object) array(
            'userId' => self::$_testUserId2,
            'phoneNumberEnc' => 'UNITTEST_PHONE_E_CSZA2136J1VVY643NMHVFGEMAJUVINP0N2UR1JEB',
            'verifyCode' => '4321',
            'verifyCountDown' => 5
        );
    }

    /**
     * @beforeClass
     */
    public static function setUpBeforeClass() {
        $userDao = self::getUserDao();
        self::deleteTestUsers( $userDao );
        self::createTestUsers( $userDao );
    }

    /**
     * @afterClass
     */
    public static function tearDownAfterClass() {
        $userDao = self::getUserDao();
        self::deleteTestUsers( $userDao );
    }

    /**
     * @before
     */
    protected function setUp() {
        $this->deleteTestUserPhones();
        $this->createTestUserPhone();
    }

    /**
     * @after
     */
    protected function tearDown() {
        $this->deleteTestUserPhones();
    }

    /**
     * @test
     */
    public function create_valid_created_test()
    {
        // Arrange
        $dao = $this->getUserPhoneDao();
        $phone = self::nonexistentUserPhone();

        // Act
        $nowish = date( 'Y-m-d H:i:' );
        $id = $dao->create( $phone->userId, $phone->phoneNumberEnc,
                $phone->verifyCode, $phone->verifyCountDown);
        $newEntry = $dao->readById( $id );

        // Assert
        assertThat( $newEntry->id, is(equalTo($id)) );
        assertThat( $newEntry->user_id, is(equalTo($phone->userId)) );
        assertThat( $newEntry->phone_number_enc, is(equalTo($phone->phoneNumberEnc)) );
        assertThat( $newEntry->verify_code, is(equalTo($phone->verifyCode)) );
        assertThat( $newEntry->verify_count_down, is(equalTo($phone->verifyCountDown)) );
        assertThat( $newEntry->verified_date, is(nullValue()) );
        assertThat( $newEntry->verify_last_attempt, is(nullValue()) );
        assertThat( $newEntry->verify_count_total, is(equalTo(0)) );
        assertThat( $newEntry->verified, is(equalTo(0)) );
        assertThat( $newEntry->created, is(equalTo($newEntry->updated)) );
        assertThat( $newEntry->created, startsWith($nowish) );
    }

    /**
     * @test
     */
    public function readById_existent_object()
    {
        // Arrange
        $dao = $this->getUserPhoneDao();
        $phone = self::existentUserPhone();
        $id = $this->_existentUserPhoneId;

        // Act
        $entry = $dao->readById( $id );

        // Assert
        assertThat( $entry, is(not(nullValue())) );
        assertThat( $entry->id, is(equalTo($id)) );
        assertThat( $entry->user_id, is(equalTo($phone->userId)) );
        assertThat( $entry->phone_number_enc, is(equalTo($phone->phoneNumberEnc)) );
        assertThat( $entry->verify_code, is(equalTo($phone->verifyCode)) );
        assertThat( $entry->verify_count_down, is(equalTo($phone->verifyCountDown)) );
    }

    /**
     * @test
     */
    public function readById_nonExistent_null()
    {
        // Arrange
        $dao = $this->getUserPhoneDao();
        $phone = self::existentUserPhone();
        $id = $this->_existentUserPhoneId;
        $dao->deleteById( $id );

        // Act
        $entry = $dao->readById( $id );

        // Assert
        assertThat( $entry, is(nullValue()) );
    }


    protected static function getUserDao() {
        return new BIM_DAO_Mysql_User( BIM_Config::db() );
    }

    protected static function deleteTestUsers( $userDao ) {
        self::deleteTestUser( $userDao, self::user1()->username );
        self::deleteTestUser( $userDao, self::user2()->username );
    }

    protected static function createTestUsers( $userDao ) {
        $user1 = self::user1();
        self::$_testUserId1 = $userDao->create( $user1->username, $user1->adId );

        $user2 = self::user2();
        self::$_testUserId2 = $userDao->create( $user2->username, $user2->adId );
    }

    protected static function deleteTestUser( $userDao, $userName ) {
        $userId = $userDao->getIdByUsername( $userName );
        if ( !empty($userId) ) {
            $userDao->delete( $userId );
        }
    }

    protected function getUserPhoneDao() {
        if ( is_null($this->_userPhoneDao) ) {
            $this->_userPhoneDao = new BIM_DAO_Mysql_UserPhone( BIM_Config::db() );
        }

        return $this->_userPhoneDao;
    }

    protected function createTestUserPhone() {
        $phone = self::existentUserPhone();
        $this->_existentUserPhoneId = $this->getUserPhoneDao()->create(
            $phone->userId,
            $phone->phoneNumberEnc,
            $phone->verifyCode,
            $phone->verifyCountDown
        );
    }

    protected function deleteTestUserPhones() {
        $this->getUserPhoneDao()->deleteByPhoneNumberEnc(
                self::nonexistentUserPhone()->phoneNumberEnc );
        $this->getUserPhoneDao()->deleteByPhoneNumberEnc(
                self::existentUserPhone()->phoneNumberEnc );
    }
}

