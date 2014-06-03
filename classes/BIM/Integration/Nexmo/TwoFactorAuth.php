<?php
class BIM_Integration_Nexmo_TwoFactorAuth {

    private $_config = null;

    public function __construct( $config = null ) {
        $this->_config = $config;
    }

    public function sendPin( $phone, $pin )
    {
        $apiKey = $this->_config->apiKey;
        $apiSecret = $this->_config->apiSecret;
        $endPoint = $this->_config->twoFactorJsonEndpoint;
    }
}

