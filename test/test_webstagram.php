<?php
set_include_path('.:/Users/shane/dev/volley/php/volley/classes:/Users/shane/dev/volley/php/volley/lib:/Users/shane/dev/volley/php/volley/lib/smtp_mailer_swift/lib/classes');
require_once 'vendor/autoload.php';
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

BIM_Growth_Webstagram_Routines::checkPersonas();

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