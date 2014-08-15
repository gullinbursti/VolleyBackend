<?php
class BIM_EventDispatcher_CeleryConnectionProvider {

    private $_host;
    private $_user;
    private $_password;
    private $_virtualHost;

    public function __construct( $config ) {
        $this->_host = $config->host;
        $this->_user = $config->user;
        $this->_password = $config->password;
        $this->_virtualHost = $config->virtual_host;
    }

    public function get() {
        $connection = new Celery(
            $this->_host,
            $this->_user,
            $this->_password,
            $this->_virtualHost
        );

        return $connection;
    }
}

?>
