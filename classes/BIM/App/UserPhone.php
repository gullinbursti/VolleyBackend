<?php

require_once 'BIM/App/Base.php';

class BIM_App_UserPhone extends BIM_App_Base {

    private $_userPhoneDao = null;

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

        //----
        // Process
        //----
        $phoneNumberEnc = BIM_Utils::blowfishEncrypt( $phone );
        $verifyCode = "TESTING";
        $verifyCountDown = 5;

        // TODO: Add check to see if number is registered by another user

        $dao = $this->getUserPhoneDao();
        $userPhone = $dao->readByUserId( $userId );
        if ( is_null($userPhone) ) {
            $dao->create( $userId, $phoneNumberEnc, $verifyCode,
                    $verifyCountDown );
        } else {
            // TODO
        }


        return true;
    }

    public function validatePhone( $userId, $pin ) {
        // Validation
        if ( empty($userId) || empty($pin) ) {
            throw new InvalidArgumentException(
                    "Both '\$userId', and '\$pin' must be set" );
        }

        if ( !$this->userExists($userId) ) {
            return false;
        }


        return true;
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
}

