<?php
class Bim_IntegrationTest_Config
{
    private $_baseUrl;

    function __construct($baseUrl)
    {
        $this->_baseUrl = $baseUrl;
    }

    //-------------------------------------------------------------------------
    // Clubs
    //-------------------------------------------------------------------------
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

    //-------------------------------------------------------------------------
    // Users
    //-------------------------------------------------------------------------
    public function usersGetSubscribees()
    {
        return (object) array(
            'url' => $this->_baseUrl . '/users/getSubscribees',
            'existent' => (object) array(
                'userId' => 64846
            ),
            'nonexistent' => (object) array(
                'userId' => 0
            )
        );
    }

    public function usersProcessImage()
    {
        return (object) array(
            'url' => $this->_baseUrl . '/users/processImage',
            'imgUrl' => 'https://hotornot-challenges.s3.amazonaws.com/86793eee81144ca9ae32c4e7544457a6-bcafe25a99b64c8db308cbe77b07854e_1400615027'
        );
    }

}
?>
