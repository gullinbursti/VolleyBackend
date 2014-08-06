<?php
class BIM_Utils{

    protected static $user = null;
    protected static $request = null;
    protected static $adid = null;
    protected static $deviceToken = null;
    protected static $profile = array();
    protected static $twilioClient = array();

    public static function cancelTimedPushes( $userId, $volleyId ){
        $dao = new BIM_DAO_Mysql_Jobs( BIM_Config::db() );
        $dao->cancelTimedPushes( $userId, $volleyId );
    }

    public static function hashMobileNumber( $number ){
        $c = BIM_Config::sms();
        if( !empty($c->useHashing) ){
            $number = self::blowfishEncrypt($number);
        }
        return $number;
    }

    public static function hashList( &$list ){
        foreach( $list as &$value ){
            $value = self::blowfishEncrypt($value);
        }
    }

    public static function decryptList( &$list ){
        foreach( $list as &$value ){
            $value = self::blowfishDecrypt($value);
        }
    }

    public static function blowfishEncrypt( $number ){
        $c = BIM_Config::sms();
        $iv = base64_decode($c->blowfish->b64iv);
        $enc = base64_encode( mcrypt_encrypt( MCRYPT_BLOWFISH, $c->blowfish->key, $number, MCRYPT_MODE_CBC, $iv ) );
        return $enc;
    }

    public static function blowfishDecrypt( $encryptedNumber ){
        $c = BIM_Config::sms();
        $iv = base64_decode($c->blowfish->b64iv);
        $dec = mcrypt_decrypt( MCRYPT_BLOWFISH, $c->blowfish->key, base64_decode( $encryptedNumber ), MCRYPT_MODE_CBC, $iv );
        return trim($dec);
    }

    public static function getRequest(){
        if( ! self::$request ){
            $c = BIM_Config::app();
            $cdata = trim(preg_replace( "@$c->base_path@", '', $_SERVER['SCRIPT_URL'] ),'/');

            $cdata = explode( '/', $cdata );

            $controller = ucfirst( trim($cdata[0],'/') );
            $controller = str_replace('.php', '', $controller);
            $method = isset( $cdata[1] ) ? trim( $cdata[1], '/' ) : '';

            $controllerClass = "BIM_Controller_$controller";

            if( !$method ){
                $input = (object) ( $_POST ? $_POST : $_GET );
                $actionMethods = BIM_Config::actionMethods();
                $method = !empty( $input->action ) && !empty( $actionMethods[ $controllerClass ][$input->action] ) ? $actionMethods[ $controllerClass ][$input->action] : null;
            }

            self::$request = (object) array(
                'controllerClass' => $controllerClass,
                'method' => $method
            );
        }

        return self::$request;
    }

    public static function getSMSCodeForId( $id ){
        $c = BIM_Config::sms();
        $smsCodeB64 = base64_encode( mcrypt_encrypt( MCRYPT_3DES, $c->secret, $id, MCRYPT_MODE_ECB) );
        $smsCode = preg_replace('@^(.*?)(?:=+)$@', '$1', $smsCodeB64);
        $numEqualSigns = mb_strlen( $smsCodeB64 ) - mb_strlen( $smsCode );
        $smsCode = "c1{$smsCode}{$numEqualSigns}1c";
        return $smsCode;
    }

    public static function getIdForSMSCode( $smsCode ){
        $c = BIM_Config::sms();
        // first we strip c1 1c
        $ptrn = "@^c1(.*?)1c$@";
        $smsCode = preg_replace( $ptrn, '$1', $smsCode );

        // then we look at the last char which is a number
        // that tells us how many equal signs to append
        // to make a proper base64 string
        $idx = mb_strlen( $smsCode ) - 1;
        $numEqualSigns = $smsCode[ $idx ];

        // then we replace the last char
        // and append the equal signs
        $ptrn = "@^(.*?)$numEqualSigns$@";
        $smsCodeB64 = preg_replace( $ptrn, '$1', $smsCode );
        $equalSigns = str_repeat('=', $numEqualSigns);
        $smsCodeB64 .= $equalSigns;

        // then decode and decrypt
        $smsCode = base64_decode( $smsCodeB64 );
        $id = mcrypt_decrypt(MCRYPT_3DES, $c->secret, $smsCode, MCRYPT_MODE_ECB);

        $id = trim( $id );

        return $id;
    }

    // here we check for a valid session key
    // in a cookie named as named in the onfig
    public static function getSessionUser( $checkDeviceToken = false ){
        if( ! self::$user ){
            $sessionConf = BIM_Config::session();
            if( $sessionConf->use ){
                $user = BIM_Model_User::getByToken( self::getAdvertisingId() );
                if( !$user || !$user->isExtant() ){
                    $user = BIM_Model_User::getByToken( self::getDeviceToken() );
                    if( !$user || !$user->isExtant() ){
                        $user = null;
                    }
                }
                self::$user = $user;
            }
        }
        if( $checkDeviceToken && self::$user ){
            if( self::$user->device_token != self::$deviceToken ){
                self::$user->setDeviceToken( self::$deviceToken );
            }
        }
        return self::$user;
    }

    public static function getAdvertisingId(){
        if( !self::$adid ){
            self::processHMAC();
        }
        return self::$adid;
    }

    public static function getDeviceToken(){
        if( !self::$deviceToken ){
            self::processHMAC();
        }
        return self::$deviceToken;
    }

    protected static function processHMAC(){
        $hmac = !empty($_SERVER['HTTP_HMAC']) ? $_SERVER['HTTP_HMAC'] : '';
        if( $hmac ){
            list( $hmac, $token ) = explode('+', $hmac, 2);
            $hash = hash_hmac('sha256', $token, "YARJSuo6/r47LczzWjUx/T8ioAJpUKdI/ZshlTUP8q4ujEVjC0seEUAAtS6YEE1Veghz+IDbNQ");
            if( $hash == $hmac ){
                list( $deviceToken, $advertisingId ) = explode('+', $token, 2 );
                self::$adid = $advertisingId;
                self::$deviceToken = $deviceToken;
            }
        }
    }

    public static function setSession( $userId ){
        $value = BIM_Utils::getSMSCodeForId( $userId );

        $conf = BIM_Config::session();
        $name = !empty( $conf->cookie->name ) ? $conf->cookie->name : '/';
        $expires = !empty( $conf->cookie->expires ) ? time() + $conf->cookie->expires : 0;
        $path = !empty( $conf->cookie->path ) ? $conf->cookie->path : '/';
        $domain = !empty( $conf->cookie->domain ) ? $conf->cookie->domain : $_SERVER['HTTP_HOST'];
        $secure = !empty( $conf->cookie->secure ) ? $conf->cookie->secure : false;
        $httpOnly = !empty( $conf->cookie->httpOnly ) ? $conf->cookie->httpOnly : false;

        setcookie($conf->cookie->name,$value,$expires,$path,$domain,$secure,$httpOnly);
    }

    /**
     *
     * @param string $birthdate a date in the format: Y-m-d H:i:s
     */
    public static function ageOK( $userBirthdate ){
        $OK = false;
        $userBirthdate = new DateTime( $userBirthdate );

        $youngBirthdate = new DateTime();
        $youngBirthdate->sub( new DateInterval('P14Y') );

        $oldBirthdate = new DateTime();
        $oldBirthdate->sub( new DateInterval('P20Y') );

        if( ( $userBirthdate < $youngBirthdate ) && ( $userBirthdate > $oldBirthdate ) ){
            // if the users birthdate comes
            // BEFORE our youngest acceptable age
            // AND
            // if the users birthdate comes
            // AFTER our oldest acceptable age
            $OK = true;
        }

        return $OK;
    }

    public static function isProfiling(){
        return (defined( 'PROFILING' ) && PROFILING);
    }

    public static function startProfiling(){
        $profilingKey = 'PROFILING';
        if( !empty( $_GET['__profile__'] ) ){
            self::$profile['start_time'] = microtime(true);
            define($profilingKey,TRUE);
        } else {
            define($profilingKey,FALSE);
        }
    }

    public static function endProfiling(){
        if( BIM_Utils::isProfiling() ){
            $totalTime = microtime(true) - self::$profile['start_time'];
            file_put_contents('/tmp/req_profile', $totalTime );
            file_put_contents('/tmp/sql_profile', print_r(BIM_DAO_Mysql::$profile ,1) );
            file_put_contents('/tmp/es_profile', print_r(BIM_DAO_ElasticSearch::$profile ,1) );
            file_put_contents('/tmp/cache_profile', print_r(BIM_Cache::$profile ,1) );
        }
    }

    public static function copyImage( $image, $name, $bucket = 'hotornot-challenges' ){
        return self::putImage($image, $name, $bucket);
    }

    public static function putImage( $image, $name, $bucket = 'hotornot-challenges' ){
        if( is_string($image) ){
            $image = new Imagick( $image );
        }
        $conf = BIM_Config::aws();
        S3::setAuth($conf->access_key, $conf->secret_key);
        S3::putObjectString($image->getImageBlob(), $bucket, $name, S3::ACL_PUBLIC_READ, array(), 'image/jpeg' );
    }

    public static function putBase64Image( &$imgStr, $name, $bucket = 'hotornot-challenges' ){
        $blob = base64_decode(
            str_replace('data:image/jpeg;base64,', '', $imgStr )
        );
        self::putImageBlob($blob, $name, $bucket);
    }

    public static function putBase64ChallengeImage( &$imgStr, $extra = '' ){
        $base = 'https://d1fqnfrnudpaz6.cloudfront.net/';
        $name = uniqid().'_'.time();
        if($extra) $name .= "_$extra";
        $extension = 'Large_640x1136.jpg';

        $imageName = "{$name}{$extension}";
        self::putBase64Image($imgStr, $imageName);

        $data = (object) array(
            'base' => $base,
            'name' => $name,
            'extension' => $extension,
            'url' => "{$base}{$name}{$extension}",
            'urlSuffix' => "{$base}{$name}",
            'imageName' => "{$name}{$extension}",
        );
        return $data;
    }

    public static function putImageBlob( &$blob, $name, $bucket = 'hotornot-challenges' ){
        $image = new Imagick();
        $image->readimageblob($blob);
        self::putImage($image, $name, $bucket);
    }

    public static function processImage( $imgPrefix, $bucket = 'hotornot-challenges' ){
        $image = self::getImage($imgPrefix);
        if( $image ){
            $conf = BIM_Config::aws();
            S3::setAuth($conf->access_key, $conf->secret_key);
            $convertedImages = self::finalizeImages($image);
            $parts = parse_url( $imgPrefix );
            $path = trim($parts['path'] , '/');
            foreach( $convertedImages as $suffix => $image ){
                $name = "{$path}{$suffix}.jpg";
                S3::putObjectString($image->getImageBlob(), $bucket, $name, S3::ACL_PUBLIC_READ, array(), 'image/jpeg' );
            }
        }
        return (bool) $image;
    }

    public static function processBase64Upload( &$imgStr, $allInfo = false ){
        $data = self::putBase64ChallengeImage( $imgStr );
        BIM_Jobs_Challenges::queueProcessImage($data->urlSuffix);
        return $allInfo ? $data : $data->urlSuffix;
    }

    public static function processUserImage( $imgPrefix, $bucket = 'hotornot-avatars' ){
        $imgPrefix = preg_replace('@Large_640x1136\.jpg@', '', $imgPrefix);
        $imgPrefix = preg_replace('@\.jpg@', '', $imgPrefix);
        self::processImage($imgPrefix, $bucket);
    }

    public static function getImage( $imgPrefix ){
        $image = null;
        $imgUrl = "{$imgPrefix}Large_640x1136.jpg";
        try{
            $image = new Imagick( $imgUrl );
        } catch ( Exception $e ){
            $msg = $e->getMessage()." - $imgUrl";
            error_log( $msg );
            $image = null;
        }
        return $image;
    }

    public static function finalizeImages( $image ){
        $convertedImages = array();

        $tabImage = clone $image;
        self::cropTab($tabImage, 640, 960);
        $convertedImages['Tab_640x960'] = $tabImage;

        self::resize($image, 320, 568);
        self::cropY($image, 320, 320);
        $mediumImage = clone $image;
        $convertedImages['Medium_320x320'] = $mediumImage;

        self::resize($image, 160, 160);
        $smallImage = clone $image;
        $convertedImages['Small_160x160'] = $smallImage;

        return $convertedImages;
    }

    public static function resize( $image, $width, $height ){
        $image->setImagePage(0,0,0,0);
        $image->setImageResolution( $width, $height );
        $image->resizeImage($width, $height, imagick::FILTER_LANCZOS, 1);
    }

    public static function cropX( $image, $width, $height ){
        $x = (int) ($image->getImageWidth() - $width)/2;
        $image->setImagePage(0,0,0,0);
        $image->setImageResolution($width,$height);
        $image->cropImage($width, $height, $x, 0);
    }

    public static function cropY( $image, $width, $height ){
        $y = (int) ($image->getImageHeight() - $height)/2;
        $image->setImagePage(0,0,0,0);
        $image->setImageResolution($width,$height);
        $image->cropImage($width, $height, 0, $y);
    }

    public static function cropTab( $image, $width, $height ){
        $y = 0;
        $image->setImagePage(0,0,0,0);
        $image->setImageResolution($width,$height);
        $image->cropImage($width, $height, 0, 100);
    }

    public static function isCelebrity( $id ){
        $isCeleb = false;
        $c = BIM_Config::app();
        if( !empty( $c->celebrities ) && in_array( $id, $c->celebrities ) ){
            $isCeleb = true;
        }
        return (int) $isCeleb;
    }

    public static function makeCacheKeys( $prefix, $ids ){
        if( $ids ){
            $return1 = false;
            if( !is_array( $ids ) ){
                $ids = array( $ids );
                $return1 = true;
            }
            $c = BIM_Config::app();
            if( !empty($c->release_id) ){
                $prefix = $c->release_id."_$prefix";
            }
            foreach( $ids as &$id ){
                $id = "{$prefix}_{$id}";
            }
            if( $return1 ){
                $ids = $ids[0];
            }
        }
        return $ids;
    }

    public static function getTagsFromPool(){
        $dao = new BIM_DAO_Mysql_Growth( BIM_Config::db() );
        return $dao->getTagsInPool();
    }

    public static function saveToTagPool( $tags ){
        $dao = new BIM_DAO_Mysql_Growth( BIM_Config::db() );
        $dao->saveToTagPool($tags);
    }

    public static function getRandomTags( $total = 20 ){
        $tags = array();
        for( $n = 0; $n < $total; $n++){
            $tags[] = self::getRandomTag();
        }
        return $tags;
    }

    public static function getRandomTag( $tagLength = 6 ){
        $str = str_split('abcdefghijklmnopqrstuvwxyz');
        $tag = array();
        for( $n = 0; $n < $tagLength; $n++ ){
            $tag[] = array_rand( $str );
        }
        foreach( $tag as &$char ){
            $char = $str[$char];
        }
        $tag = '#'.join('',$tag);
        return $tag;
    }

    //
    public static function getGeneralSelfieClubComment( $tag = array() ){
        if( !is_array( $tag ) ){
            $tag = array( $tag );
        }
        $tag = join(' ', $tag );

        $compliments = array(
            'sweeeet!',
            ':)))) <3 (((((:',
            'niiiice! :)',
            'niiice <3!',
            'luv this!',
            'likeee!',
            'loveee ur posts!',
            '<3 ur posts!',
            'omg love this!',
            'all day everyday!',
            'the best award goes to u :)',
            'so sweeeeet!',
            'tooo awesome!',
            'loveee it!',
            'luv it!',
        );

        $questions = array(
            'join selfie club???js',
            'join selfieclub?!!!!a',
            'join selfie cllub?!?',
            'wanna join?',
            'join?',
            'joinn?',
            'jjoin?',
            'join??',
            'wan join?',
            'want to join?',
            'wnna join?!',
            'you should join!',
            'join this?',
            'join selfie club?',
            'you should join selfie club :)',
            'joinnn selfie club??',
            'join this now!!',
            'thinkin u should join dis!',
        );

        $callsToAction = array(
            '>>>',
            'go get the selfieclub iOS app',
            'selfieclub is on the Apple app store. get it!',
            'get the app on the apple app store ',
            'go here',
            'install selfieclub on ur iOS device!',
            'go get selfieclub!  on the app store now :))))',
            'lol check this',
            'check this',
            '>>>> tap >>>',
            '>>>>>>> tap this',
            '>>>>>> TAP THIS',
            '>>> join selfieclub >>>>>',
            'join selfieclub go here >>>',
            'go here for selfieclub >>>',
            'get it >>>',
            'join selfieclub >>',
            'tap this to get selfieclub>>>>>> ',
            'selfieclub? tappp this to join >>>>>>',
        );

        $compliment = $compliments[array_rand( $compliments )];
        $question = $questions[array_rand( $questions )];
        $callToAction = $callsToAction[array_rand( $callsToAction )];

        $comment = "$compliment $question $callToAction $tag";
        return $comment;
    }
    // returns a unique 6 letter string
    // returns a unique 6 letter string
    public static function getRandomComment( $tag = array() ){
        if( !is_array( $tag ) ){
            $tag = array( $tag );
        }
        $tag = join(' ', $tag );

        $compliments = array(
            'Cute selfie!',
            'Cute!',
            'cute selfies :)',
            '<3 selfie!',
            'luv this!',
            'likeee!',
            'loveee ur posts!',
            '<3 ur posts!',
            'omg love this!',
            'all day everyday!',
            'selfie award goes to u :)',
            'so selfie!',
            'selfie loveeeee!',
            'loveee it!',
            'luv it!',
        );

        $questions = array(
            'join selfie club???js',
            'join selfieclub?!!!!a',
            'join selfie cllub?!?',
            'wanna join?',
            'join?',
            'joinn?',
            'jjoin?',
            'join??',
            'wan join?',
            'want to join?',
            'wnna join?!',
            'you should join!',
            'join this?',
            'join selfie club?',
            'you should join selfie club :)',
            'joinnn selfie club??',
            'join this now!!',
            'thinkin u should join dis!',
        );

        $callsToAction = array(
            '>>>',
            'go get the selfieclub iOS app',
            'selfieclub is on the Apple app store. get it!',
            'get the app on the apple app store ',
            'go here',
            'install selfieclub on ur iOS device!',
            'go get selfieclub!  on the app store now :))))',
            'lol check this',
            'check this',
            '>>>> tap >>>',
            '>>>>>>> tap this',
            '>>>>>> TAP THIS',
            '>>> join selfieclub >>>>>',
            'join selfieclub go here >>>',
            'go here for selfieclub >>>',
            'get it >>>',
            'join selfieclub >>',
            'tap this to get selfieclub>>>>>> ',
            'selfieclub? tappp this to join >>>>>>',
        );

        $compliment = $compliments[array_rand( $compliments )];
        $question = $questions[array_rand( $questions )];
        $callToAction = $callsToAction[array_rand( $callsToAction )];

        $comment = "$compliment $question $callToAction $tag";
        return $comment;
    }

    public static function getTwilioClient(){
        if( ! self::$twilioClient ){
            $conf = BIM_Config::twilio();
            self::$twilioClient = new Services_Twilio( $conf->api->accountSid, $conf->api->authToken );
        }
        return self::$twilioClient;
    }
}
