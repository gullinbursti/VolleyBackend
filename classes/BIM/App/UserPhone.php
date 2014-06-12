<?php

require_once 'BIM/App/Base.php';

class BIM_App_UserPhone extends BIM_App_Base {

    CONST VERIFY_CODE_LENGTH = 4;
    CONST VERIFY_CODE_CHARS = '0123456789';
    CONST VERIFY_COUNT_DOWN = 5;

    private $_userPhoneDao = null;
    private $_nexmoTwoFactorAuth = null;
    private $_userApp = null;

    public function createOrUpdatePhone( $userId, $phone ) {
        //----
        // Validation
        //----
        if ( empty($userId) || empty($phone) ) {
            throw new InvalidArgumentException(
                    "Both '\$userId', and '\$phone' must be set" );
        }

        if ( !$this->userExists($userId) ) {
            return false;
        }

        // TODO - Add check to see if number is registered by another user

        //----
        // Process
        //----
        // Setup
        $phoneNumberEnc = BIM_Utils::blowfishEncrypt( $phone );
        $verifyCode = $this->generateVerifyCode();
        $verifyCountDown = self::VERIFY_COUNT_DOWN;

        // Call Nexmo
        $nexmoAuth = $this->getNexmoTwoFactorAuth();
        // TODO - error check
        $nexmoAuth->sendPin($phone, $verifyCode );

        // Save data
        $dao = $this->getUserPhoneDao();
        $userPhone = $dao->readExistingByUserId( $userId );
        if ( is_null($userPhone) ) {
            // TODO - error check
            $dao->create( $userId, $phoneNumberEnc, $verifyCode,
                    $verifyCountDown );
        } else {
            $phoneId = $userPhone->id;
            // TODO - error check
            $dao->updateNewPhone( $phoneId, $userId, $phoneNumberEnc, $verifyCode,
                    $verifyCountDown );
        }

        return true;
    }

    public function validatePhone( $userId, $phone, $pin ) {
        // Validation
        if ( empty($userId) || empty($phone) || empty($pin) ) {
            throw new InvalidArgumentException(
                    "Both '\$userId', '\$phone', and '\$pin' must be set" );
        }

        if ( !$this->userExists($userId) ) {
            return false;
        }

        // Process
        $phoneNumberEnc = BIM_Utils::blowfishEncrypt( $phone );
        $dao = $this->getUserPhoneDao();
        $verified = $dao->updateVerifyPhonePin( $userId, $phoneNumberEnc, $pin );

        if ( $verified ) {
            $userApp = $this->getUserApp();
            $params = (object) array(
                'phone' => $phone,
                'user_id' => $userId
            );
            $userApp->verifyPhone( $params );
        } else {
            $dao->updatePhonePinVerifyFailed( $userId, $phoneNumberEnc );
        }

        return $verified;
    }

    public function isValid( $userId ) {
        // Validation
        if ( empty($userId) ) {
            throw new InvalidArgumentException( "'\$userId' must be set" );
        }

        if ( !$this->userExists($userId) ) {
            return false;
        }

        $dao = $this->getUserPhoneDao();
        $data = $dao->readVerifyDataByUserId( $userId );
        $result = $data->verified ? true : false;

        return $result;
    }

    /**
     * Thank you:
     *   - http://stackoverflow.com/questions/4356289/php-random-string-generator
     */
    public function generateVerifyCode() {
        $length = self::VERIFY_CODE_LENGTH;
        $characters = self::VERIFY_CODE_CHARS;
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * Static call to BIM_Model_User::get isolated to allow stubbing in
     * unit testing.
     */
    protected function userExists( $userId ) {
        $user = BIM_Model_User::get( $userId );
        return ($user && $user->isExtant());
    }

    public function setUserPhoneDao( $userPhoneDao ) {
        if ( is_null($this->_userPhoneDao) ) {
            $this->_userPhoneDao = $userPhoneDao;
        } else {
            throw new UnexpectedValueException(
                    "'userPhoneDao' can only be set once." );
        }
    }

    public function getUserPhoneDao() {
        if ( is_null($this->_userPhoneDao) ) {
            $app = new BIM_DAO_Mysql_UserPhone( BIM_Config::db() );
            $this->setUserPhoneDao( $app );
        }

        return $this->_userPhoneDao;
    }

    public function setNexmoTwoFactorAuth( $nexmoTwoFactorAuth ) {
        if ( is_null($this->_nexmoTwoFactorAuth) ) {
            $this->_nexmoTwoFactorAuth = $nexmoTwoFactorAuth;
        } else {
            throw new UnexpectedValueException(
                    "'nexmoTwoFactorAuth' can only be set once." );
        }
    }

    public function getNexmoTwoFactorAuth() {
        if ( is_null($this->_nexmoTwoFactorAuth) ) {
            $nexmo = new BIM_Integration_Nexmo_TwoFactorAuth( BIM_Config::nexmo() );
            $this->setNexmoTwoFactorAuth( $nexmo );
        }

        return $this->_nexmoTwoFactorAuth;
    }

    public function setUserApp( $userApp ) {
        if ( is_null($this->_userApp) ) {
            $this->_userApp = $userApp;
        } else {
            throw new UnexpectedValueException(
                    "'userApp' can only be set once." );
        }
    }

    public function getUserApp() {
        if ( is_null($this->_userApp) ) {
            $app = new BIM_App_Users();
            $this->setUserApp( $app );
        }

        return $this->_userApp;
    }
}

