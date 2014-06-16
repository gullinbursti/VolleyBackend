<?php
class BIM_IntegrationTest_IntegrationTestContext
{
    private static $_context = null;

    private $_configuration = null;

    public static function getContext()
    {
        if ( is_null(self::$_context) ) {
            self::$_context = new self();
        }

        return self::$_context;
    }

    public function setConfiguration( $configuration )
    {
        if ( is_null($this->_configuration) ) {
            $this->_configuration = $configuration;
        } else {
            throw new UnexpectedValueException(
                    "Configuration can only be set once." );
        }
    }

    public function getConfiguration()
    {
        if ( !is_null($this->_configuration) ) {
            return $this->_configuration;
        } else {
            throw new RuntimeException( "Configuration was never set" );
        }
    }
}

?>
