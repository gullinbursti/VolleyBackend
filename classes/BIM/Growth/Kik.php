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
