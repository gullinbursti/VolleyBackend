<?php

require_once 'BIM/App/Base.php';

class BIM_App_UserPhone extends BIM_App_Base {

    public function updatePhone( $userId, $phone ) {
        error_log( "HERE --> updatePhone()" );
        return true;
    }

    public function validatePhone( $userId, $pin ) {
        error_log( "HERE --> validatePhone()" );
        return true;
    }
}

