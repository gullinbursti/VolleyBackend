<?php
//set_include_path('.:/Users/shane/dev/volley/php/volley/classes:/Users/shane/dev/volley/php/volley/lib:/Users/shane/dev/volley/php/volley/lib/smtp_mailer_swift/lib/classes');
require_once 'vendor/autoload.php';

/*
$job = (object) array(
    "nextRunTime" => '2013-11-29 23:57:08',
    "class" => 'BIM_Jobs_Growth',
    "method" => 'doBlastJob',
    "name" => 'do_blast_job',
    "params" => json_encode(
        array(
            "persona_id" => 'RiversLa496',
            "target" => (object) array(
                "id" => '12113563',
                "name" => 'tsuritb',
                "url" => 'http://web.stagram.com/n/tsuritb',
                "selfie" => ''
            )
        )
    ),

    "is_temp" => '1',
    "disabled" => '0'
);
*/
$job = json_decode(
'{"nextRunTime":"2013-12-02 13:01:01","class":"BIM_Jobs_Growth","method":"doBlastJob","name":"do_blast_job","params":{"persona_id":"BrennanCh716","target":{"id":"239452459","name":"rzn34","url":"http:\/\/web.stagram.com\/n\/rzn34","selfie":""},"comment":"likeee! join selfie club? selfieclub is on the Apple app store. get it! #eqlcoy"},"is_temp":true,"disabled":0}'
);
$job->params = json_encode( $job->params );

print_r( $job );

$j = new BIM_Jobs_Growth();
$j->doBlastJob($job);

// BIM_Growth_Webstagram_Routines::doBlastJob("AblesMi307");

/**
    [0] => login
    [1] => posting
    [2] => https://instagram.com/accounts/login/?next=/oauth/authorize/%3Fclient_id%3D9d836570317f4c18bca0db6d2ac38e29%26redirect_uri%3Dhttp%253A%252F%252Fweb.stagram.com%252F%26response_type%3Dcode%26scope%3Dlikes%2Bcomments%2Brelationships
    [3] => Array
        (
            [csrfmiddlewaretoken] => f5a3ec6cdacdaa1dc4a84f8de81b9eb5
            [username] => alleyla
            [password] => i8ngot6
        )

    [4] => 

 */

//$g = new BIM_Growth();

//$url = "https://instagram.com/accounts/login/?next=/oauth/authorize/%3Fclient_id%3D9d836570317f4c18bca0db6d2ac38e29%26redirect_uri%3Dhttp%253A%252F%252Fweb.stagram.com%252F%26response_type%3Dcode%26scope%3Dlikes%2Bcomments%2Brelationships";

//$headers = array(
//    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
//    'Referer: https://instagram.com/accounts/login/',
//    'Origin: https://instagram.com'
//);

//$args = array(
  //  'csrfmiddlewaretoken' => 'f5a3ec6cdacdaa1dc4a84f8de81b9eb5',
  //  'username' => 'alleyla',
  //  'password' => 'i8ngot6',
//);

//$response = $g->post( $url, $args, t, $headers );

//print_r( $response );

//BIM_Growth_Webstagram_Routines::checkPersonas();

/**
$persona = (object) array(
    'name' => 'alleyla',
    'instagram' => (object) array(
        'username' => 'alleyla',
        'password' => 'i8ngot6'
    )
);
$r = new BIM_Growth_Webstagram_Routines( $persona );
$r->handleLogin();
**/