<?php
class Bim_IntegrationTest_Config
{
    private $_baseUrl;

    function __construct($baseUrl)
    {
        $this->_baseUrl = $baseUrl;
    }

    public function clubsGet()
    {
        return (object) array(
            'urlGet' => $this->_baseUrl . '/clubs/get',
            'existent' => (object) array(
                'clubId' => 40,
                'userId' => 131820
            ),
            'nonexistent' => (object) array(
                'clubId' => 0,
                'userId' => 0
            )
        );
    }

}
?>
