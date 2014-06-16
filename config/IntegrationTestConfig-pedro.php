<?php
class BIM_IntegrationTest_Config_Pedro
{
    const BASE_URL = 'http://api-dev.letsvolley.com/api/pedro';

    public function clubsGet()
    {
        return (object) array(
            'urlGet' => self::BASE_URL . '/clubs/get',
            'existent' => (object) array(
                'clubId' => 40,
                'userId' => 131820
            ),
            'existent' => (object) array(
                'clubId' => 0,
                'userId' => 0
            )
        );
    }

}
?>
