<?php

require_once 'BIM/App/Base.php';

class BIM_App_UserPhone extends BIM_App_Base {

    public function updatePhone( $userId, $phone ) {
        // Validation
        if ( empty($userId) || empty($phone) ) {
            throw new InvalidArgumentException(
                    "Both '\$userId', and '\$phone' must be set" );
        }

        if ( !$this->userExists($userId) ) {
            return false;
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
}

