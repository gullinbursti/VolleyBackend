<?php

class BIM_Controller_UserPhone extends BIM_Controller_Base {

    private $_userPhoneApp = null;

    public function updatePhone() {
        $input = (object) ($_POST ? $_POST : $_GET);
        $userId = isset($input->userID) ? $input->userID : null;
        $phone = isset($input->phone) ? $input->phone : null;

        // Validation
        if ( empty($userId) || empty($phone) ) {
            return null;
        }

        // Process
        $app = $this->getUserPhoneApp();
        $app->createOrUpdatePhone( $userId, $phone );

        return true;
    }

    public function validatePhone() {
        $input = (object) ($_POST ? $_POST : $_GET);
        $userId = isset($input->userID) ? $input->userID : null;
        $phone = isset($input->phone) ? $input->phone : null;
        $pin = isset($input->pin) ? $input->pin : null;

        // Validation
        if ( empty($userId) || empty($phone) || empty($pin) ) {
            return null;
        }

        // Process
        $app = $this->getUserPhoneApp();
        $verified = $app->validatePhone( $userId, $phone, $pin );

        return $verified;
    }

    public function setUserPhoneApp( $userPhoneApp ) {
        if ( is_null($this->_userPhoneApp) ) {
            $this->_userPhoneApp = $userPhoneApp;
        } else {
            throw new UnexpectedValueException(
                    "'userPhoneApp' can only be set once." );
        }
    }

    public function getUserPhoneApp() {
        if ( is_null($this->_userPhoneApp) ) {
            $app = new BIM_App_UserPhone();
            $this->setUserPhoneApp( $app );
        }

        return $this->_userPhoneApp;
    }
}
