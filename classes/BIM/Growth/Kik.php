<?php

/**
POST /zerver/API/getFirstHeyHeys HTTP/1.1
Host: kik.heyhey.us
Content-Length: 11
Origin: http://kik.heyhey.us
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36
Content-Type: text/plain;charset=UTF-8
Referer: http://kik.heyhey.us/
Accept-Encoding: gzip,deflate,sdch
Accept-Language: en-US,en;q=0.8
 *
 */
class BIM_Growth_Kik extends BIM_Growth{
    public static function crawlMadeInHeights(){
        $self = new self();
        $url = "http://madeinheights.music.koa.la/get_fans.json";
        $args = array();
        
        $headers = array(
            'Content-Type: text/plain;charset=UTF-8',
            'Referer: http://madeinheights.music.koa.la',
            'Host: madeinheights.music.koa.la',
            'Accept: application/jsom',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        $data = $self->get( $url, $args, false, $headers );
        $data = json_decode( $data );
        
        $date = new DateTime();
        $date->setTimezone( new DateTimeZone( 'UTC' ) );
        $date = $date->format('Y-m-d H:i:s');
        
        if( !empty( $data->users ) ){
            foreach( $data->users as $user ){
                $userData = (object) array(
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->pic,
                    'shout_pic' => $user->thumbnail,
                    'network' => 'madeinheights',
                    'created_at' => $date
                );
                $dao->saveKikUser($userData);
            }
        } else {
            error_log("could not get a random user from kikfriends!");
        }
    }
    
    // http://andygrammer.music.koa.la/get_fans.json
    /**
	{
		"id": 7589,
		"username": "aishahbasri16",
		"first_name": "Aishah",
		"last_name": "Basri",
		"full_name": "Aishah Basri",
		"pic": null,
		"thumbnail": null
	}
    */
    public static function crawlAndyGrammer(){
        $self = new self();
        $url = "http://andygrammer.music.koa.la/get_fans.json";
        $args = array();
        
        $headers = array(
            'Content-Type: text/plain;charset=UTF-8',
            'Referer: http://andygrammer.music.koa.la',
            'Host: andygrammer.music.koa.la',
            'Accept: application/jsom',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        $data = $self->get( $url, $args, false, $headers );
        $data = json_decode( $data );
        
        $date = new DateTime();
        $date->setTimezone( new DateTimeZone( 'UTC' ) );
        $date = $date->format('Y-m-d H:i:s');
        
        if( !empty( $data->users ) ){
            foreach( $data->users as $user ){
                $userData = (object) array(
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->pic,
                    'shout_pic' => $user->thumbnail,
                    'network' => 'andygrammer',
                    'created_at' => $date
                );
                $dao->saveKikUser($userData);
            }
        } else {
            error_log("could not get a random user from kikfriends!");
        }
    }
    
    public static function crawlChatNow(){
        $self = new self();
        $pageNumber = 0;
        $url = "http://chatcard.co/index.php/landing/get_users?username=&page=$pageNumber&userdata=";
        $args = array();
        
        $headers = array(
            'Content-Type: text/plain;charset=UTF-8',
            'Referer: https://chatcard.co/',
            'Host: chatcard.co',
            'Accept: application/jsom',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        $daysAgo = time() - (86400 * 7);
            
            // here is where we get the data
            /*

<li id="_beautiful_believer_" class="app-button userblock  new" data-clickable-class="active">
   <div class="image"><img id="_beautiful_believer__profile_img" style="border-radius:10px" width="70" height="70" src="http://cdn.kik.com/user/pic/_beautiful_believer_"></div>
   <div class="info">
      <ul>
         <li class="username">Stephanie Cassidy</li>
         <li class="kikname">_beautiful_believer_</li>
      </ul>

    </div>
</li>

<img id="[^"]+_profile_img" .+? src="([^"]+)">
<li class="username">(.+?)</li>
<li class="kikname">(.+?)</li>
                      * 
             */
            
        $createdAt = '';
        while( $url ){
            $data = $self->get( $url, $args, false, $headers );
            
            $pattern = '@<img id="[^"]+_profile_img".+?src="([^"]+)">@';
            $images = array();
            preg_match_all($pattern, $data, $images);
            
            $pattern = '@<li class="username">(.+?)</li>@';
            $usernames = array();
            preg_match_all($pattern, $data, $usernames);
            
            $pattern = '@<li class="kikname">(.+?)</li>@';
            $kiknames = array();
            preg_match_all($pattern, $data, $kiknames);
            if( !empty( $images[1] ) ){
                $time = time();
                foreach( $images[1] as $idx => $imgUrl ){
                    $username = $kiknames[1][ $idx ];
                    
                    // add the created at property
                    $d = new DateTime( "@$time" );
                    $createdAt = $d->format('Y-m-d H:i:s');
                    
                    $userData = (object) array(
                        'id' => 0,
                        'username' => $username,
                        'avatar' => $imgUrl,
                        'shout_pic' => '',
                        'network' => 'chatnow',
                        'created_at' => $createdAt,
                    );
                    
                    print_r( $userData );
                    
                    $dao->saveKikUser($userData);
                }
                $pageNumber++;
                $url = "http://chatcard.co/index.php/landing/get_users?username=&page=$pageNumber&userdata=";
                self::sleep(1,"getting $url next - $createdAt");
            } else {
                break;
            }
        }
    }
    
    public static function crawlRandomKikFriends(){
        $self = new self();
        $url = "http://kikfriends.com/ws/random.php?u=vampirediaries_swag&g=2&s=";
        $args = array();
        
        $headers = array(
            'Content-Type: text/plain;charset=UTF-8',
            'Referer: http://kikfriends.com',
            'Host: kikfriends.com',
            'Accept: application/jsom',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        $daysAgo = time() - (86400 / 8);
        while( $url ){
            $data = $self->get( $url, $args, false, $headers );
            $data = json_decode( $data );
            if( !empty( $data ) ){
                foreach( $data as $user ){
                    $userData = (object) array(
                        'id' => 0,
                        'username' => $user->user,
                        'avatar' => '',
                        'shout_pic' => '',
                        'network' => 'kikfriends',
                        'created_at' => '1970-01-01'
                    );
                    $dao->saveKikUser($userData);
                }
                self::sleep(1,"getting $url next");
            } else {
                error_log("could not get a random user from kikfriends!");
                break;
            }
        }
    }
    
    public static function crawlOneDirection(){
        $self = new self();
        $url = "http://cards-oned.herokuapp.com/zerver/Chatroom/getCheckins";
        $args = array('{"args":["onedcard"]}');
        
        $headers = array(
            'Content-Type: text/plain;charset=UTF-8',
            'Referer: http://cards-oned.herokuapp.com',
            'Host: cards-oned.herokuapp.com',
            'Accept: application/jsom',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        /*
		{
			"username": "swegsince09_",
			"pic": "//d33vud085sp3wg.cloudfront.net/uQHJVornNJD0edeCBHSzu88RpeQ/orig.jpg",
			"thumbnail": "//d33vud085sp3wg.cloudfront.net/uQHJVornNJD0edeCBHSzu88RpeQ/thumb.jpg",
			"fullName": "You're gay ∞",
			"firstName": "You're",
			"lastName": "gay ∞",
			"date": 1387577648804
		}
         */
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        $daysAgo = time() - (86400 / 8);
        while( $url ){
            $data = $self->post( $url, $args, false, $headers, true );
            $data = json_decode( $data );
            
            if( !empty( $data->data[0] ) ){
                foreach( $data->data[0] as $user ){
                    $userData = (object) array(
                        'id' => 0,
                        'username' => $user->username,
                        'avatar' => $user->pic,
                        'shout_pic' => $user->thumbnail,
                        'network' => 'oned',
                    );
                    
                    $date = (int) ($user->date / 1000);
                    $date = new DateTime( "@$date");
                    $date = $date->format('Y-m-d H:i:s');
                    
                    $userData->created_at = $date;
                    
                    // print_r( $userData );
                    $dao->saveKikUser($userData);
                }
                self::sleep(5,"getting $url next");
            } else {
                error_log("could not get a random user from one direction!");
                break;
            }
        }
    }
    
    public static function crawlOnlinekikFriends(){
        $self = new self();
        $url = "http://kikfriends.com/ws/online.php?u=vampirediaries_swag&g=2&s=";
        $args = array();
        
        $headers = array(
            'Content-Type: text/plain;charset=UTF-8',
            'Referer: http://kikfriends.com',
            'Host: kikfriends.com',
            'Accept: application/jsom',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        /*
{
	"user": "Karolka...",
	"likes": "",
	"photo": "http:\/\/profilepics.kik.com\/saUArSGFMTIuG-seh66idq6LL7A\/thumb.jpg",
	"dates": "2013-12-20 21:12:31"
}
         */
        
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        $daysAgo = time() - (86400 / 8);
        while( $url ){
            $data = $self->get( $url, $args, false, $headers );
            $data = json_decode( $data );
            if( !empty( $data ) ){
                foreach( $data as $user ){
                    $userData = (object) array(
                        'id' => 0,
                        'username' => $user->user,
                        'avatar' => $user->photo,
                        'shout_pic' => '',
                        'network' => 'kikfriends',
                        'created_at' => $user->dates,
                    );
                    // print_r( $userData );
                    $dao->saveKikUser($userData);
                }
                self::sleep(5,"getting $url next");
            } else {
                error_log("could not get a random user from kikfriends!");
                break;
            }
        }
    }
    
    // http://kik.wattpad.com/api/v3/stories?filter=hot&kikReaders=1&limit=200&offset=201&fields=stories%28kikReaders%29
    public static function crawlWattpad(){
        $self = new self();
        $url = "http://kik.wattpad.com/api/v3/stories?kikReaders=1&limit=20&offset=0";
        $args = array();
        
        $headers = array(
            'Referer: https://kik.wattpad.com/',
            'Host: kik.wattpad.com',
            'X-Requested-With: XMLHttpRequest',
        	'Accept: */*',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        while( $url ){
            $data = $self->get( $url, $args, false, $headers );
            $data = json_decode( $data );
            $date = new DateTime();
            $date->setTimezone( new DateTimeZone( 'UTC' ) );
            $date = $date->format('Y-m-d H:i:s');
            if( !empty( $data->stories ) ){
                foreach( $data->stories as $story ){
                    if( !empty( $story->kikReaders ) ){
                        foreach( $story->kikReaders as $user ){
/*
                    		{
                    			"USERNAME": "sullma03",
                    			"FIRSTNAME": "\u25c7sullma",
                    			"LASTNAME": "\u25c7",
                    			"PICURL": "\/\/d33vud085sp3wg.cloudfront.net\/GPoWNAe_cgP5hcqaZ6a-_r7FfTE\/orig.jpg",
                    			"THUMBNAILURL": "\/\/d33vud085sp3wg.cloudfront.net\/GPoWNAe_cgP5hcqaZ6a-_r7FfTE\/thumb.jpg"
                    		}
 */                            
                            $userData = (object) array(
                                'id' => 0,
                                'username' => $user->USERNAME,
                                'avatar' => $user->PICURL,
                                'shout_pic' => $user->THUMBNAILURL,
                                'network' => 'wattpad',
                                'created_at' => $date,
                            );
                            print_r( $userData );
                            $dao->saveKikUser($userData);
                        }
                    }
                }
                if( !empty( $data->nextUrl ) ){
                    $url =  $data->nextUrl;
                } else {
                    $url = null;
                }
                self::sleep(1,"getting $url next");
            } else {
                error_log("no more stories in wattpad");
                break;
            }
        }
    }
    
    public static function crawlStickerfy(){
        $self = new self();
        $url = "https://kik.stickerfy.us/find_users.json?before=null";
        $args = array();
        
        $headers = array(
            'Content-Type: text/plain;charset=UTF-8',
            'Referer: https://kik.stickerfy.us/',
            'Host: kik.stickerfy.us',
            'Accept: application/jsom',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        $daysAgo = time() - (86400 / 8);
        while( $url ){
            $data = $self->get( $url, $args, false, $headers );
            $data = json_decode( $data );
            if( !empty( $data->users ) ){
                foreach( $data->users as $user ){
                    $userData = (object) array(
                        'id' => $user->id,
                        'username' => $user->username,
                        'avatar' => $user->pic,
                        'shout_pic' => '',
                        'network' => 'stickerfy',
                    );
                    
                    // add the created at property
                    $d = new DateTime( "@$user->timestamp" );
                    $userData->created_at = $d->format('Y-m-d H:i:s');
                    $dao->saveKikUser($userData);
                }
                $lastUser = end($data->users);
                $timeParam = $lastUser->timestamp;
                if( $timeParam >= $daysAgo ){
                    $url = "https://kik.stickerfy.us/find_users.json?before=$timeParam";
                } else {
                    $url = null;
                }
                self::sleep(1,"getting $url next");
            } else {
                break;
            }
        }
    }
    
    public static function crawlWordSwap(){
        $self = new self();
        //$self->setUseProxy( true );
        
        // get the csrf token
        $url = 'https://wordswap.uken.com';
        $response = $self->get( $url );
        $matches = array();
        preg_match('@content="([^"]+)" name="csrf-token"@', $response, $matches);
        $csrfToken = '';
        if( !empty( $matches[1] ) ){
            $csrfToken = $matches[1];
        } else {
            error_log("cannot get a csrf token from $response");
        }
        
        // call the logoff url
        $headers = array(
            'Host: wordswap.uken.com',
            'Accept-Language: en-us',
            'Accept: */*',
            'Referer: https://wordswap.uken.com/',
            'Content-Type: application/x-www-form-urlencoded',
            'If-None-Match: "7215ee9c7d9dc229d2921a40e899ec5f"',
            'Connection: keep-alive',
            'Proxy-Connection: keep-alive',
            'Content-Length: 0',
            'Origin: https://wordswap.uken.com',
            'Accept-Encoding: gzip, deflate',
        );
        $url = 'https://wordswap.uken.com/logoff';
        $args = array();
        $response = $self->post( $url, $args, false, $headers );
        
        // call mopub
        $headers = array(
            'Host: wordswap.uken.com',
            'Referer: https://wordswap.uken.com/',
            'If-None-Match: "d8ae92eeb9343b3a5392708d85ff478c"',
            'Proxy-Connection: keep-alive',
            'Accept-Encoding: gzip, deflate',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-us',
            'Connection: keep-alive',
        );
        $url = 'https://wordswap.uken.com/mopub';
        $args = array();
        $response = $self->get( $url, $args, false, $headers );
        
        //call /games
        $headers = array(
            'Host: wordswap.uken.com',
            'Referer: https://wordswap.uken.com/',
            'If-None-Match: "1d62b338ebadfb72b13569089528f890"',
            'Proxy-Connection: keep-alive',
            'Accept-Encoding: gzip, deflate',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: en-us',
            'Connection: keep-alive',
            "X-CSRF-Token: $csrfToken",
        );
        
        $url = 'https://wordswap.uken.com/api/games';
        $args = array();
        $response = $self->get( $url, $args, false, $headers );
        
        // now call for a random user
        $url = "https://wordswap.uken.com/api/games";
        $args = array('{"opponent":{"username":"uken_random_opponent"}}');
        
        /**
Host: wordswap.uken.com
Accept-Language: en-us
User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Mobile/11B554a
Referer: https://wordswap.uken.com/
Content-Type: application/json;charset=UTF-8
Connection: keep-alive
X-CSRF-Token: zgXgWfuY7Cm28Kig6WDx8L+SfipvFLDJZUdZzfEQVAY=
Cookie: _hangman_session=39c282b0bf493e58083a02cbc2ca1c3b; mopub-udid-cookie=X0oS2NP2PC302ObIkW0P; _ga=GA1.2.405576697.1387579769
Proxy-Connection: keep-alive
Content-Length: 48
Origin: https://wordswap.uken.com
Accept-Encoding: gzip, deflate
         */
        $headers = array(
            'Host: wordswap.uken.com',
        	'Accept-Language: en-us',
            'Referer: https://wordswap.uken.com/',
        	'Content-Type: application/json;charset=UTF-8',
            'Connection: keep-alive',
            "X-CSRF-Token: $csrfToken",
        	'Proxy-Connection: keep-alive',
        	'Origin: https://wordswap.uken.com',
            'Accept-Encoding: gzip, deflate',
        );
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        while( $url ){
            $data =  $self->post( $url, $args, true, $headers, true );
            print_r( array( $data ) ); exit;
            $data = json_decode( $data );
            if( !empty( $data->opponent ) ){
                /**
                {
                	"id": 46481,
                	"username": "shanehill00",
                	"hearts": ["heart-full", "heart-full", "heart-full"],
                	"hearts_busted": ["heart-full", "heart-full", "heart-empty"],
                	"thumbnail": "/assets/thumbnail-default-109dec7334490841842cbc27b7522eb7.png",
                	"my_turn": true,
                	"action": "create",
                	"intersitial_ad": null,
                	"hand": "etrogwgtamqw",
                	"word": null,
                	"timestamp": 1388793936,
                	"kik_opponent": true,
                	"max_strikes": null,
                	"powerups": [],
                	"opponent": {
                		"username": "trexdy",
                		"hearts": ["heart-full", "heart-full", "heart-full"],
                		"thumbnail": "//d33vud085sp3wg.cloudfront.net/5zZCo1Al0hgJtXUSKU2uP_Qi3QE/thumb.jpg"
                	},
                	"rounds": [{
                		"my_glyph": "none",
                		"opponent_glyph": "icon-checkmark",
                		"word": null,
                		"guessed_letters": null,
                		"missed_letters": null
                	}]
                }
                * 
                 */
                
                $date = new DateTime();
                $date->setTimezone( new DateTimeZone( 'UTC' ) );
                $date = $date->format('Y-m-d H:i:s');
                
                foreach( $data->data as $user ){
                    $userData = (object) array(
                        'id' => 0,
                        'username' => $data->opponent->username,
                        'avatar' => $data->opponent->thumbnail,
                        'shout_pic' => $data->opponent->thumbnail,
                        'network' => 'wordswap',
                        'created_at' => $date
                    );
                    $dao->saveKikUser($userData);
                    error_log("got ".$data->opponent->username." from word swap");
                }
                self::sleep(2,"getting $url next");
            } else {
                error_log("could not get a random user from word swap");
                break;
            }
        }
    }
    
    // https://dinodancer.luckypuppygames.com/zerver/MyAPI/findRandomPlayer
    public static function crawlDinoDancer(){
        $self = new self();
        //$self->setUseProxy( true );
        $url = "https://dinodancer.luckypuppygames.com/zerver/MyAPI/findRandomPlayer";
        $args = array('{"args":[]}');
        
        $headers = array(
            'Content-Type: text/plain;charset=UTF-8',
            'Referer: https://dinodancer.luckypuppygames.com/',
            'Host: dinodancer.luckypuppygames.com',
            'Origin: https://dinodancer.luckypuppygames.com',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        while( $url ){
            $data = json_decode( $self->post( $url, $args, false, $headers, true ) );
            if( !empty( $data->data ) ){
                /**
            	{
            		"username": "shelbymmiller79",
            		"thumbnail": "//d33vud085sp3wg.cloudfront.net/z_ORPprv9QFlHcLHQJYNRVLBDS4/thumb.jpg",
            		"pic": "//d33vud085sp3wg.cloudfront.net/z_ORPprv9QFlHcLHQJYNRVLBDS4/orig.jpg",
            		"fullName": "Shelby M. Shoemaker",
            		"firstName": "Shelby",
            		"lastName": "M. Shoemaker",
            		"pushToken": "El5GYW0OOTO9HwQbQez59braNdVnQN6dOehXm7p-6HCYHOoUoDSbuzMhe1M5XffZgojTw4XNVlXBZ6K7wPbXMhRsRAYB0FDPfj5tc5v83OEC",
            		"uid": "859988038",
            		"xp": 15
            	}
                 * 
                 */
                
                $date = new DateTime();
                $date->setTimezone( new DateTimeZone( 'UTC' ) );
                $date = $date->format('Y-m-d H:i:s');
                
                foreach( $data->data as $user ){
                    $userData = (object) array(
                        'id' => $user->uid,
                        'username' => $user->username,
                        'avatar' => $user->pic,
                        'shout_pic' => $user->thumbnail,
                        'network' => 'dinodancer',
                        'created_at' => $date
                    );
                    $dao->saveKikUser($userData);
                    error_log("got $user->username from dino dancer");
                }
                self::sleep(2,"getting $url next");
            } else {
                error_log("could not get a random user from dino dancer!");
                //break;
            }
        }
    }
    
    public static function getHeyHeys(){
        $self = new self();
        $url = "http://kik.heyhey.us/zerver/API/getFirstHeyHeys";
        $args = array('{"args":[]}');
        
        $headers = array(
            'Content-Type: text/plain;charset=UTF-8',
            'Referer: http://kik.heyhey.us/',
            'Host: kik.heyhey.us',
            'Origin: http://kik.heyhey.us',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
        $hoursAgo = time() - (3600 / 12);
        while( $url ){
            $data = json_decode( $self->post( $url, $args, false, $headers, true ) );
            if( !empty( $data->data[0]->shouts ) ){
                foreach( $data->data[0]->shouts as $shout ){
                    // 2013-12-15T04:22:05.477Z
                    $d = new DateTime( $shout->created_at );
                    $d->setTimezone( new DateTimeZone('UTC') );
                    $shout->created_at = $d->format( 'Y-m-d H:i:s');
                    $shout->network = 'heyhey';
                    $dao->saveKikUser($shout);
                }
                $lastShout = end($data->data[0]->shouts);
                $lastShoutTime = new DateTime( $lastShout->created_at );
                $lastShoutTime = $lastShoutTime->getTimestamp();
                if( $lastShoutTime >= $hoursAgo && $data->data[0]->has_more_results ){
                    $timeParam = $lastShout->created_at;
                    $url = 'http://kik.heyhey.us/zerver/API/getMoreHeyHeys';
                    $args = array('{"args":["'.$lastShout->created_at.'"]}');
                    self::sleep(1,"getting $url next - $lastShout->created_at");
                } else {
                    echo "no more kik ids.  done!\n";
                    $url = null; //"http://kik.heyhey.us/zerver/API/getFirstHeyHeys";
                    $args = array('{"args":[]}');
                }
            } else {
                break;
            }
        }
    }
}
