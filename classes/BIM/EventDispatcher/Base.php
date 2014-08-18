<?php
class BIM_EventDispatcher_Base {

    private $_celeryConnectionProvider = null;
    private $_enabled = false;

    public function __construct( $config = null ) {
        if ( !isset($config) ) {
            $config = BIM_Config::eventDispatcher();
        }

        $this->_enabled = $config->enabled;
    }

    protected function dispatchEvent( $eventName ) {
        if ( !$this->_enabled ) {
            return;
        }

        $args = func_get_args();
        $eventName = array_shift($args);

        try {
            $connection = $this->getCeleryConnection();

            if ( isset($connection) ) {
                $result = $connection->PostTask($eventName, $args);

                if (isset($result)) {
                    $result->forget();
                }
            }
        } catch (Exception $exception) {
            // Intentionally trying to catch all exceptions.  We want to keep
            // everything moving even if the event is not delivered.
            // TODO : Log this better
            error_log("Failed to post task: " . $exception->getTraceAsString());
        }
    }

    protected function getCeleryConnection() {
        try {
            $connection = $this->getCeleryConnectionProvider()->get();
        } catch ( Exception $exception ) {
            error_log("Failed to get Celery connection: "
                . $exception->getTraceAsString());
            $connection = null;
        }
        return $connection;
    }

    public function setCeleryConnectionProvider( $celeryConnectionProvider ) {
        if ( is_null($this->_celeryConnectionProvider) ) {
            $this->_celeryConnectionProvider = $celeryConnectionProvider;
        } else {
            throw new UnexpectedValueException(
                    "'celeryConnectionProvider' can only be set once." );
        }
    }

    protected function getCeleryConnectionProvider() {
        if ( is_null($this->_celeryConnectionProvider) ) {
            $config = BIM_Config::eventDispatcher()->celery;
            $provider = new BIM_EventDispatcher_CeleryConnectionProvider($config);
            $this->setCeleryConnectionProvider( $provider );
        }

        return $this->_celeryConnectionProvider;
    }
}

?>
