<?php
class BIM_Integration_Nexmo_TwoFactorAuth {

    private $_config = null;

    public function __construct( $config = null ) {
        $this->_config = $config;
    }
}

