<?php

class BIM_Model_User{

    public function __construct( $params = null, $getFriends = false ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );

        if( !is_object($params) ){
            $params = $dao->getData( $params );
        }

        if( !empty($params->id) ){
            foreach( $params as $prop => $value ){
                $this->$prop = $value;
            }

            // $this->abuse_ct = 0;

            $birthdate = new DateTime( "@$this->age" );
            $this->age = $birthdate->format('Y-m-d H:i:s');

            $votes = $this->getTotalVotes();
            $pics = $this->getTotalVolleys();

            // find the avatar image
            $avatar_url = $this->getAvatarUrl();

            // adding some additional properties
            $this->name = $this->username;
            $this->token = $this->device_token;

            if( !$this->token ){
                $this->token = '';
            }

            if( !$this->email ){
                $this->email = '';
            }

            if( !$this->device_token ){
                $this->device_token = '';
            }

            $this->is_celeb = $this->isCelebrity();
            $this->avatar_url = $avatar_url;
            $this->votes = $votes;
            //$this->pokes = $pokes;
            $this->pics = $pics;
            $this->meta = '';
            $this->sms_code = BIM_Utils::getSMSCodeForId( $this->id );
            $this->friends = $getFriends ?
                BIM_App_Social::getFollowers( (object) array( 'userID' => $this->id ) )
                : -1;
            $this->sms_verified = $this->smsVerified();
            $this->is_suspended = $this->isSuspended();
            $this->is_verified = $this->isApproved();
            if( empty($this->adid) ){
                $this->adid = '';
            }
        }
    }

    public function isTeamVolleyUser(){
        $c = BIM_Config::app();
        return ($this->id == $c->team_volley_id);
    }

    public function isSuperUser(){
        $super = false;
        $c = BIM_Config::app();
        $isSuperAdId = !empty($c->super_users) && in_array($this->adid, $c->super_users );
        if( $this->isTeamVolleyUser() || $isSuperAdId ){
            $super = true;
        }
        return $super;
    }

    public function ageOK(){
        return BIM_Utils::ageOK($this->age);
    }

    public function isCelebrity(){
        return BIM_Utils::isCelebrity($this->id);
    }

    public static function purgeById( $ids ){
        if( !is_array($ids) ){
            $ids = array( $ids );
        }
        $users = self::getMulti($ids);
        foreach( $users as $user ){
            if( $user->isExtant() ){
                $user->purgeFromCache();
            }
        }
    }

    public function hasFriendList(){
        return ( property_exists( $this, 'friends' ) && $this->friends != -1  );
    }

    public function populateFriends(){
        $this->friends = BIM_App_Social::getFollowers( (object) array( 'userID' => $this->id ) );
    }

    private function smsVerified( ){
        $smsVerified = 0;
        if( ! property_exists($this, 'sms_verified')  || $this->sms_verified < 0 ){
            $smsVerified = (int) self::isVerified( $this->id );
            $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
            $dao->setSmsVerified($this->id, $smsVerified);
        } else {
            $smsVerified = $this->sms_verified;
        }
        return $smsVerified == 0 ? false : true;
    }

    public static function getAllPushTokens(){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        return $dao->getAllPushTokens( );
    }

    public function getTotalVotes(){
        if( ! property_exists($this, 'total_votes') || $this->total_votes < 0 ){
            $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
            $this->total_votes = $dao->getTotalVotes( $this->id );
            // now we put the total in a caching column for faster object builds
            $dao->setTotalVotes($this->id, $this->total_votes);
        }
        return $this->total_votes;
    }

    public function getTotalVolleys(){
        if( ! property_exists($this, 'total_challenges') || $this->total_challenges < 0 ){
            $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
            $this->total_challenges = $dao->getTotalChallenges( $this->id );
            // now we put the total in a caching column for faster object builds
            $dao->setTotalVolleys($this->id, $this->total_challenges);
        }
        return $this->total_challenges;
    }

    public function isSuspended(){
        //return false;
        return (!empty( $this->abuse_ct ) && $this->abuse_ct >= 20);
    }

    public function isApproved(){
        return (!empty( $this->abuse_ct ) && $this->abuse_ct <= -10);
    }

    /**
     * increments or decrements the flag count for the user
     *
     * @param boolean $approves
     */
    public function flag( $volleyId, $userId, $count ){
        $count = (int) $count;
        $this->abuse_ct = 0; //+= $count;
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->flag( $volleyId, $this->id, $userId, $count );
        $this->purgeFromCache();
    }

    public function setAgeRange( $ageRange ){
        $this->age = $ageRange;
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->setAgeRange( $this->id, $ageRange );
        $this->purgeFromCache();
    }

    public function setAdvertisingId( $adId ){
        $this->adid = $adId;
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->setAdvertisingId( $this->id, $adId );
        $this->purgeFromCache();
    }

    public function setDeviceToken( $deviceToken ){
        $this->device_token = $deviceToken;
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->setDeviceToken( $this->id, $deviceToken );
        $this->purgeFromCache();
    }

    public static function isVerified( $userId ){
        $dao = new BIM_DAO_ElasticSearch_ContactLists( BIM_Config::elasticSearch() );
        $res = $dao->getPhoneList( (object) array('id' => $userId ) );
        $res = json_decode($res);
        $verified = (!empty( $res->_source->hashed_number ) && $res->_source->hashed_number );
        return $verified;
    }

    public function isExtant(){
        return ( isset( $this->id ) && $this->id );
    }

    public function getAvatarUrl() {

        // no custom url
        if ($this->img_url == "") {

            // has fb login
            if ($this->fb_id != "")
                return ("https://graph.facebook.com/". $this->fb_id ."/picture?type=square");

            // has nothing, default
            else
                return ( 'https://s3.amazonaws.com/hotornot-avatars/defaultAvatar.png' );
        }

        // use custom
        return ($this->img_url);
    }

    /**
     *
update tblUsers set age = -1 where id in (select id from tblUsers where username like "%snap4snap%" union select id from tblUsers where username like "%picchampX%" union select id from tblUsers where username like "%swagluver%" union select id from tblUsers where username like "%coolswagger%" union select id from tblUsers where username like "%yoloswag%" union select id from tblUsers where username like "%tumblrSwag%" union select id from tblUsers where username like "%instachallenger%" union select id from tblUsers where username like "%hotbitchswaglove%" union select id from tblUsers where username like "%lovepeaceswaghot%" union select id from tblUsers where username like "%hotswaglover%" union select id from tblUsers where username like "%snapforsnapper%" union select id from tblUsers where username like "%snaphard%" union select id from tblUsers where username like "%snaphardyo%" union select id from tblUsers where username like "%yosnaper%" union select id from tblUsers where username like "%yoosnapyoo");

select added from tblUsers where username like "%snap4snap%"
union
select added from tblUsers where username like "%picchampX%"
union
select added from tblUsers where username like "%swagluver%"
union
select added from tblUsers where username like "%coolswagger%"
union
select added from tblUsers where username like "%yoloswag%"
union
select added from tblUsers where username like "%tumblrSwag%"
union
select added from tblUsers where username like "%instachallenger%"
union
select added from tblUsers where username like "%hotbitchswaglove%"
union
select added from tblUsers where username like "%lovepeaceswaghot%"
union
select added from tblUsers where username like "%hotswaglover%"
union
select added from tblUsers where username like "%snapforsnapper%"
union
select added from tblUsers where username like "%snaphard%"
union
select added from tblUsers where username like "%snaphardyo%"
union
select added from tblUsers where username like "%yosnaper%"
union
select added from tblUsers where username like "%yoosnapyoo";

delete from tblUsers where username like "%snap4snap%";
delete from tblUsers where username like "%picchampX%";
delete from tblUsers where username like "%swagluver%";
delete from tblUsers where username like "%coolswagger%";
delete from tblUsers where username like "%yoloswag%";
delete from tblUsers where username like "%tumblrSwag%";
delete from tblUsers where username like "%instachallenger%";
delete from tblUsers where username like "%hotbitchswaglove%";
delete from tblUsers where username like "%lovepeaceswaghot%";
delete from tblUsers where username like "%hotswaglover%";
delete from tblUsers where username like "%snapforsnapper%";
delete from tblUsers where username like "%snaphard%";
delete from tblUsers where username like "%snaphardyo%";
delete from tblUsers where username like "%yosnaper%";
delete from tblUsers where username like "%yoosnapyoo";
     *
     *
     * Enter description here ...
     * @param unknown_type $token
     * @param unknown_type $adId
     */

    public static function create( $adId, $params = null ){
            // default names
            $username = '';
            if( empty( $params->username ) ){
                $defaultName_arr = array(
                    "snap4snap",
                    "picchampX",
                    "swagluver",
                    "coolswagger",
                    "yoloswag",
                    "tumblrSwag",
                    "instachallenger",
                    "hotbitchswaglove",
                    "lovepeaceswaghot",
                    "hotswaglover",
                    "snapforsnapper",
                    "snaphard",
                    "snaphardyo",
                    "yosnaper",
                    "yoosnapyoo"
                );

                $rnd_ind = mt_rand(0, count($defaultName_arr) - 1);
                $username = $defaultName_arr[$rnd_ind] . time();
                $username = $username.'.'.uniqid(true);
            } else {
                $username = $params->username;
            }

            $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
            $email = empty( $params->email ) ? null : $params->email;
            $id = $dao->create($username, $adId, $email);
            return self::get($id);
    }

    public static function createOld( $adId ){
            // default names
            $username = '';
            if( empty( $params->username ) ){
                $defaultName_arr = array(
                    "snap4snap",
                    "picchampX",
                    "swagluver",
                    "coolswagger",
                    "yoloswag",
                    "tumblrSwag",
                    "instachallenger",
                    "hotbitchswaglove",
                    "lovepeaceswaghot",
                    "hotswaglover",
                    "snapforsnapper",
                    "snaphard",
                    "snaphardyo",
                    "yosnaper",
                    "yoosnapyoo"
                );

                $rnd_ind = mt_rand(0, count($defaultName_arr) - 1);
                $username = $defaultName_arr[$rnd_ind] . time();
                $username = $username.'.'.uniqid(true);
            } else {
                $username = $params->username;
            }

            $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
            $email = empty( $params->email ) ? null : $params->email;
            $id = $dao->create($username, $adId, $email);
            return self::get($id);
    }

    public function poke( $targetId ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $pokeId = $dao->poke( $this->id, $targetId );
        if( $pokeId ){
            $this->pokes += 1;
            $this->purgeFromCache();
            $this->purgeFromCache( $targetId );
        }
    }

    public function updateUsernameAvatarFirstRun( $username, $imgUrl, $birthdate = null, $password = null, $deviceToken = '' ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        if( empty($this->avatar_url) ||  ! preg_match('@defaultAvatar@i', $imgUrl) ){
            $this->avatar_url = $imgUrl;
        } else if( !empty($this->avatar_url) && preg_match('@defaultAvatar@i', $imgUrl) ){
            $imgUrl = $this->avatar_url;
        }
        $dao->updateUsernameAvatarFirstRun( $this->id, $username, $imgUrl, $birthdate, $password, $deviceToken );
        $this->username = $username;
        if( !empty($birthdate) ){
            $this->age = $birthdate;
        }
        $this->purgeFromCache();
        $this->queuePurgeVolleys();
    }

    public function updateUsernameAvatar( $username, $imgUrl, $birthdate = null, $password = null ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->updateUsernameAvatar( $this->id, $username, $imgUrl, $birthdate, $password );
        $this->username = $username;
        $this->avatar_url = $imgUrl;
        if( !empty($birthdate) ){
            $this->age = $birthdate;
        }
        $this->purgeFromCache();
        $this->queuePurgeVolleys();
    }

    public function reCache(){
        $cache = new BIM_Cache( BIM_Config::cache() );
        $key = self::makeCacheKeys($this->id);
        $cache->set($key,$this);
    }

    public function purgeFromCache( $id = null ){
        $cache = new BIM_Cache( BIM_Config::cache() );
        if(!$id) $id = $this->id;
        $key = self::makeCacheKeys($id);
        $cache->delete( $key );
        if( !empty($this->device_token) ){
            $cache->delete( $this->device_token );
        }
        if( !empty($this->adid) ){
            $cache->delete( $this->adid );
        }
    }

    public function cacheIdByToken( $token = null){
        $cache = new BIM_Cache( BIM_Config::cache() );
        if(!$token && !empty($this->device_token) ){
            $token = $this->device_token;
        }
        if( $token ){
            $cache->set( $token, $this->id );
        }
    }

    public static function getCachedIdFromToken( $token ){
        $cache = new BIM_Cache( BIM_Config::cache() );
        return $cache->get( $token );
    }

    public function updatePaiid( $isPaid ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->updatePaid( $this->id, $isPaid );
        $this->paid = $isPaid;
        $this->purgeFromCache();
    }

    public function updateNotifications( $isNotifications ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->updateNotifications( $this->id, $isNotifications );
        $this->notifications = $isNotifications;
        $this->purgeFromCache();
    }

    public function updateAbuseCount( $count ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->updateAbuseCount( $this->id, $count );
        $this->abuse_ct = 0; //$count;
        $this->purgeFromCache();
    }

    public function updateUsername( $username ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->updateUsername( $this->id, $username );
        $this->username = $username;
        $this->purgeFromCache();
        $this->queuePurgeVolleys();
    }

    public function queuePurgeVolleys(){
        if( $this->isExtant() ){
            BIM_Jobs_Users::queuePurgeUserVolleys($this->id);
        }
    }

    public function purgeVolleys(){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $ids = $dao->getAllIdsForUser( $this->id, true );
        $volleys = BIM_Model_Volley::getMulti($ids);
        foreach( $volleys as $volley ){
            $volley->purgeFromCache();
        }
    }

    public function updateFBUsername( $fbId, $username, $gender ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->updateFBUsername($this->id, $fbId, $username, $gender );
        $this->username = $username;
        $this->purgeFromCache();
    }

    public function updateFB( $fbId, $gender ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->updateFB($this->id, $fbId, $gender );
        $this->gender = $gender;
        $this->purgeFromCache();
    }

    public function getFBInviteId( $fbId ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        return $dao->getFbInviteId( $fbId );
    }

    public function updateLastLogin( ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $lastLogin = $dao->updateLastLogin( $this->id );
        $this->last_login = $lastLogin;
        $this->purgeFromCache();
    }

    public function acceptFbInviteToVolley( $inviteId ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $volleys = $this->getFbInvitesToVolley( $inviteId );
        // loop thru the challenges
        foreach ( $volleys as $volley ) {
            $volley->acceptFbInviteToVolley( $this->id, $inviteId );
        }
        $this->purgeFromCache();
    }

    public function getFbInvitesToVolley( $inviteId ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $ids = $dao->getFbInvitesToVolley( $inviteId );
        return BIM_Model_Volley::getMulti($ids);
    }

    public function getOpponenetsWithSnaps(){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $userData = $dao->getOpponentsWithSnaps($this->id);
        $ids = array();
        foreach( $userData as $user ){
            $ids[] = $user->creator_id;
            $ids[] = $user->user_id;
        }
        $ids = array_unique($ids);
        return self::getMulti($ids);
    }

    public static function getRandomIds( $total = 1, $exclude = array(), $date  ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        return $dao->getRandomIds( $total, $exclude, $date );
    }

    public function archive(){
        if( $this->isExtant() ){
            $this->purgeVolleys();
            $this->purgeFromCache();
            $this->volleys = BIM_Model_Volley::getMulti($this->getVolleyIds());
            $data = json_encode($this);
            $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
            $dao->archive($this->id, $this->username, $data);
        }
    }

    public function removeFriends(){
        if( $this->isExtant() ){
            $dao = new BIM_DAO_ElasticSearch_Social( BIM_Config::elasticSearch() );
            $docs = $dao->deleteRelationships( $this->id );
        }
    }

    public function removeLikes(){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $volleyIds = $dao->getLikedVolleys( $this->id );
        $volleys = BIM_Model_Volley::getMulti($volleyIds);
        $dao->removeLikes( $this->id );
        if( !empty( $volleys ) ){
            foreach( $volleys as $volley ){
                $volley->setRecentLikes();
            }
        }
    }

    public static function makeCacheKeys( $ids ){
        return BIM_Utils::makeCacheKeys('user', $ids);
    }

    /**
     *
     * do a multifetch to memcache
     * if there are any missing objects
     * get them from the db, one a t a time
     *
    **/
    public static function getMulti( $ids, $assoc = false, $getFriends = false ) {
        $userKeys = self::makeCacheKeys( $ids );
        $cache = new BIM_Cache( BIM_Config::cache() );
        $users = $cache->getMulti( $userKeys );

        // now we determine which things were not in memcache dn get those
        $retrievedKeys = array_keys( $users );
        $missedKeys = array_diff( $userKeys, $retrievedKeys );
        if( $missedKeys ){
            $missedIds = array();
            foreach( $missedKeys as $userKey ){
                $userId = explode('_',$userKey);
                $missedIds[] = end($userId);
            }
            $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
            $missingData = $dao->getData($missedIds);
            foreach( $missingData as $userData ){
                $user = new self( $userData, $getFriends );
                if( $user->isExtant() ){
                    $users[ $user->id ] = $user;
                    $key = self::makeCacheKeys($user->id);
                    $cache->set( $key, $user );
                }
            }
        }
        //now sort the users according to the order in which they were asked
        $userArr = array();
        foreach( $users as $key => $user ){
            $userArr[ $user->id ] = $user;
            if($getFriends && !$user->hasFriendList() ){
                $user->populateFriends();
                $user->reCache();
            }
        }
        $users = array();
        foreach( $ids as $id ){
            if( isset( $userArr[ $id ] ) ){
                $users[ $id ] = $userArr[ $id ];
            }
        }

        return $assoc ? $users : array_values( $users );
    }

    public static function get( $id, $forceDb = false ){
        $cacheKey = self::makeCacheKeys($id);
        $user = null;
        $cache = new BIM_Cache( BIM_Config::cache() );
        if( !$forceDb ){
            $user = $cache->get( $cacheKey );
        }
        if( !$user ){
            $user = new self($id);
            if( $user->isExtant() ){
                $cache->set( $cacheKey, $user );
            }
        }

        if( $user && $user->isExtant() && !$user->hasFriendList() ){
            // we go to elastic search to get the friends list
            // here unless we have already done so
            $user->populateFriends();
            $user->reCache();
        }
        return $user;
    }

    public static function getIdByUsername( $name, $forceDb = false ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $id = $dao->getIdByUsername( $name );
        return $id;
    }

    public static function getIdByPhone( $phone, $forceDb = false ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $id = $dao->getIdByPhone( $phone );
        return $id;
    }

    public static function getByUsername( $name, $forceDb = false ){
        $me = null;
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $id = $dao->getIdByUsername( $name );
        if( $id ){
            $me = self::get( $id , $forceDb );
        }
        return $me;
    }

    public static function getByToken( $token, $forceDb = false ){
        $me = null;
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );

        $id = self::getCachedIdFromToken($token);
        if( $id ){
            $me = self::get( $id, $forceDb );
        } else {
            $id = $dao->getIdByToken( $token );
            if( $id ){
                $me = self::get( $id, $forceDb );
                if( $me->isExtant() ){
                    // this puts us in the cache
                    $me->cacheIdByToken( $token );
                }
            }
        }
        return $me;
    }

    public static function getUsersWithSimilarName( $username ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $ids = $dao->getUsersWithSimilarName( $username );
        $users = self::getMulti($ids, false);
        foreach( $users as $user ){
            if( !$user->hasFriendList() ){
                $user->friends = array();
            }
        }
        return $users;
    }

    /**
     * we get the user object and the list of their images
     * and serialize it and store into an archived users table
     *
     * the archived users table
     * user_id, username, blob
     *
     */
    public static function archiveUser( $ids ){
        $wantArray = true;
        if( !is_array($ids)){
            $ids = array( $ids );
            $wantArray = false;
        }
        foreach( $ids as $id ){
            $user = self::get($id);
            $user->removeLikes();
            $user->archive();
            $user->delete();
            $user->removeFriends();
            $return[] = $user;
        }

        if( !$wantArray ){
            if( $return ){
                $return = $return[0];
            } else {
                $return = null;
            }
        }

        return $return;
    }

    public static function archiveByName( $userNames ){
        $wantArray = true;
        if( !is_array($userNames)){
            $userNames = array( $userNames );
            $wantArray = false;
        }

        $return = array();
        foreach( $userNames as $name ){
            $user = self::getByUsername($name);
            $return[] = self::archiveUser($user->id);
        }

        if( !$wantArray ){
            if( $return ){
                $return = $return[0];
            } else {
                $return = null;
            }
        }
        return $return;
    }

    public static function blockUser( $ids ){
        if( !is_array($ids)){
            $ids = array( $ids );
        }
        foreach( $ids as $id ){
            $user = BIM_Model_User::get($id);
            print_r( array("blocking: ", $user ) );
            $user->archive();
            $user->block();
        }
    }

    public static function blockByName( $userNames ){
        foreach( $userNames as $name ){
            $user = BIM_Model_User::getByUsername($name);
            self::blockUser($user->id);
        }
    }

    public function delete(){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $this->purgeFromCache();
        $this->purgeVolleys();
        $dao->delete($this->id);
    }

    public function block(){
        $this->purgeContent();
        $this->removeFriends();
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $dao->block($this->id);
    }

    public function purgeContent(){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $this->purgeFromCache();
        $this->purgeVolleys();
        $dao->purgeContent($this->id);
    }

    public function getVolleyIds(){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        return $dao->getVolleysForUserId($this->id);
    }

    public function canPush(){
        return (!empty($this->notifications) && $this->notifications == 'Y');
    }

    public function hasSelfie(){
        return !empty($this->img_url);
    }

    public function getClubs( $idsOnly = false, $sort = NULL ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $clubData = $dao->getClubIds( $this->id );
        if( !$idsOnly ){
            $clubs = (object) array(
                'owned' => array(),
                'member' => array(),
                'pending' => array(),
                'other' => array()
            );

            $clubData = BIM_Model_Club::getMulti( $clubData, false, $sort );
            foreach( $clubData as $club ){
                if( $club->isOwner( $this->id ) ){
                    $clubs->owned[] = $club;
                } else if ( $club->isMember( $this->id ) ) {
                    $clubs->member[] = $club;
                } else if ( $club->isPending( $this->id ) ) {
                    $clubs->pending[] = $club;
                }
            }
            $clubData = $clubs;
        }
        return $clubData;
    }

    public function getClubInvites( $idsOnly = false ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $clubIds = $dao->getClubInvites( $this->id );
        if( !$idsOnly ){
            $clubIds = BIM_Model_Club::getMulti( $clubIds );
        }
        return $clubIds;
    }

    public static function getSuspendees( $limit = 50 ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $ids = $dao->getSuspendees( $limit );
        return self::getMulti($ids);
    }

    public static function getPendingSuspendees( $limit = 50 ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $ids = $dao->getPendingSuspendees( $limit );
        return self::getMulti($ids);
    }

    public static function processProfileImages( $userIds ){
        $conf = BIM_Config::aws();
        S3::setAuth($conf->access_key, $conf->secret_key);
        while( $userIds ){
            $ids = array_splice($userIds, 0, 250);
            $users = BIM_Model_User::getMulti($ids);
            foreach( $users as $user ){
                if( !empty( $user->img_url ) && !preg_match( '@facebook.com@', $user->img_url ) ){
                    $imgPrefix = preg_replace('@\.jpg@','', $user->img_url );
                    self::processImage( $imgPrefix );
                    echo "processed user $user->id\n\n";
                }
            }
            print count( $userIds )." remaining\n\n====\n\n";
        }
    }

    public static function processImage( $imgPrefix, $bucket = 'hotornot-avatars' ){
        error_log("converting $imgPrefix");
        $image = self::getImage($imgPrefix);
        if( $image ){
            $conf = BIM_Config::aws();
            S3::setAuth($conf->access_key, $conf->secret_key);
            $convertedImages = BIM_Utils::finalizeImages($image);
            $parts = parse_url( $imgPrefix );
            $path = trim($parts['path'] , '/');
            foreach( $convertedImages as $suffix => $image ){
                $name = "{$path}{$suffix}.jpg";
                S3::putObjectString($image->getImageBlob(), $bucket, $name, S3::ACL_PUBLIC_READ, array(), 'image/jpeg' );
                echo "put {$imgPrefix}{$suffix}.jpg\n";
            }
        }
    }

    protected static function getImage( $imgPrefix ){
        $image = null;
        $imgUrl = "{$imgPrefix}Large_640x1136.jpg";
        try{
            $image = new Imagick( $imgUrl );
        } catch ( Exception $e ){
            $msg = $e->getMessage()." - $imgUrl";
            error_log( $msg );
            $image = null;
            $imgUrl = "{$imgPrefix}.jpg";
            try{
                $image = new Imagick( $imgUrl );
            } catch( Exception $e ){
                $msg = $e->getMessage()." - $imgUrl";
                error_log( $msg );
                $image = null;
            }
        }
        echo "\n";
        return $image;
    }

    /*
     * returns a data structure indicating which one exists
     * in the following structure
     *
     * array(
     *         'username' => true | false
     *         'email' => true | false
     * )
     *
     * returns false if neither one exists
     *
     */
    public static function usernameOrEmailExists( $input ){
        $result = (object) array();
        if( filter_var( $input->email, FILTER_VALIDATE_EMAIL) ){
            $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
            $data = $dao->usernameOrEmailExists( $input );
            if( $data ){
                foreach( $data as $row ){
                    $prop = $row->property;
                    $result->$prop = 1;
                }
            } else {
                $result->ok = true;
            }
        } else {
            $result->email = 2;
        }
        return $result;
    }

    public static function usernameOrEmailExistsOld( $input ){
        $result = (object) array();
        if( filter_var( $input->email, FILTER_VALIDATE_EMAIL) ){
            $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
            $data = $dao->usernameOrEmailExists( $input );
            if( $data ){
                foreach( $data as $row ){
                    $prop = $row->property;
                    $result->$prop = $row->value;
                }
            }
        } else {
            $result->email = $input->email;
        }
        return $result;
    }

    public static function getLikers( $userId ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $likers = $dao->getLikers( $userId );

        $ids = array();
        foreach( $likers as $liker ){
            $ids[] = $liker->id;
        }
        $likerObjs = self::getMulti($ids, true);

        foreach( $likers as $liker ){
            $liker->user = $likerObjs[ $liker->id ];
        }
        return $likers;
    }

    public static function getVerifiers( $userId ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $verifiers = $dao->getVerifiers( $userId );
        $ids = array();

        foreach( $verifiers as $verifier ){
            $ids[] = $verifier->id;
        }

        $verifierObjs = self::getMulti($ids, true);
        foreach( $verifiers as $verifier ){
            $verifier->user = $verifierObjs[ $verifier->id ];
        }
        return $verifiers;
    }

    public static function getShoutouts( $userId ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $shouters = $dao->getShoutouts( $userId );
        $ids = array();
        foreach( $shouters as $shouter ){
            $ids[] = $shouter->id;
        }
        $shouterObjs = self::getMulti($ids, true);
        foreach( $shouters as $shouter ){
            $shouter->user = $shouterObjs[ $shouter->id ];
        }
        return $shouters;
    }

    /**
     *
     * retrieves the latest X items for the users activity feed
     *
     * we get their:
     *
     *         50 latest followers
     *         50 latest likers
     *         50 latest verifies
     *
     * and collate them together according to date and return the top 50
     *
     * verikfy - 1
     * follow - 2
     * like - 3
     * shoutout - 4
     * reply - 5
     *
     *
     * @param int $userId
     */
    public static function getActivity( $userId, $lastUpdated = '' ) {
        $activities = array();

        $params = (object) array(
            'from' => 0,
            'size' => 50,
            'userID' => $userId,
        );

        $tz = new DateTimeZone('UTC');
        $date = new DateTime();
        $date->setTimezone( $tz );

        $likers = self::getLikers( $userId );
        foreach( $likers as $liker ){
            $date = new DateTime( $liker->added );
            $date->setTimezone( $tz );
            $club_name = !is_null($liker->club_name)
                ? $liker->club_name
                : '';
            $activities[] = (object) array(
                'id' => "3_{$liker->user->id}_{$date->getTimestamp()}",
                'activity_type' => 3,
                'club_id' => $liker->club_id,
                'club_name' => $club_name,
                'user' => (object) array(
                     'id' => $liker->user->id,
                     'username' => $liker->user->username,
                     'avatar_url' => $liker->user->avatar_url,
                ),
                'challengeID' => $liker->challenge_id,
                'time' => $liker->added,
            );
        }

        $verifiers = self::getVerifiers( $userId );
        foreach( $verifiers as $verifier ){
            $date->setTimestamp($verifier->added);
            $club_name = !is_null($verifier->club_name)
                ? $verifier->club_name
                : '';

            $activities[] = (object) array(
                'id' => "1_{$verifier->user->id}_{$verifier->added}",
                'activity_type' => 1,
                'club_id' => $verifier->club_id,
                'club_name' => $club_name,
                'user' => (object) array(
                     'id' => $verifier->user->id,
                     'username' => $verifier->user->username,
                     'avatar_url' => $verifier->user->avatar_url,
                ),
                'time' => $date->format('Y-m-d H:i:s'),
            );
        }

        $userVolleys = BIM_Model_Volley::getVolleys( $userId );
        foreach( $userVolleys as $userVolley ){
            if( $userVolley->is_private ){
                continue;
            }

            $club_name = !is_null($userVolley->club_name)
                ? $userVolley->club_name
                : '';

            foreach( $userVolley->challengers as $challenger ){
                $activities[] = (object) array(
                    'id' => "5_{$challenger->id}_{$date->getTimestamp()}",
                    'activity_type' => 5,
                    'user' => (object) array(
                         'id' => $challenger->id,
                         'username' => $challenger->username,
                         'avatar_url' => $challenger->img,
                    ),
                    'challengeID' => $userVolley->id,
                    'club_id' => $userVolley->club_id,
                    'club_name' => $club_name,
                    'time' => $challenger->joined,
                );
            }
        }

        $activities = self::sortAndTrimActivities( $activities, $lastUpdated );

        return $activities;
    }

    protected static function sortAndTrimActivities( $activities, $oldestDate,
            $limit = 20 ) {

        // Shared comparator
        $comparator = function($a, $b){
            if ($a->time == $b->time) {
                return 0;
            }
            return ($a->time < $b->time ) ? 1 : -1;
        };

        // Sort
        usort( $activities, $comparator );

        // Trim the array
        if ( empty($oldestDate) || (strcmp($oldestDate, "0000-00-00 00:00:00") == 0)) {
            $splice_length = $limit;
        } else {
            $splice_length = $limit;
            $needle = (object) array( 'time' => $oldestDate );
            self::arrayBinarySearch( $needle, $activities, $comparator,
                    $splice_length );

            ++$splice_length;
            $splice_length = $splice_length <= $limit
                ? $splice_length
                : $limit;
        }
        $activities = array_splice($activities, 0, $splice_length);

        return $activities;
    }

    /**
     * Thank you: http://www.php.net//manual/en/function.array-search.php#93352
     */
    // TODO - Move to a Util class
    protected static function arrayBinarySearch(
            $needle, $haystack, $comparator , &$probe ) {
        $high = Count( $haystack ) -1;
        $low = 0;

        while ( $high >= $low ) {
            $probe = Floor( ( $high + $low ) / 2 );
            $comparison = $comparator( $haystack[$probe], $needle );
            if ( $comparison < 0 )
            {
                $low = $probe +1;
            }
            elseif ( $comparison > 0 )
            {
                $high = $probe -1;
            }
            else
            {
                return true;
            }
        }
        //The loop ended without a match
        //Compensate for needle greater than highest haystack element
        if($comparator($haystack[count($haystack)-1], $needle) < 0) {
            $probe = count($haystack);
        }
        return false;
    }

    public static function getRandomKikUser( ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        return $dao->getRandomKikUser( );
    }

    public static function getKikUser( $kikId ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $id = $dao->getKikUserId( $kikId );
        $user = BIM_Model_User::get( $id );
        $user->kik_id = $kikId;
        return $user;
    }

    public static function createKikUser( $input ){
        $user = self::getKikUser( $input->username );
        if( !$user->isExtant() ){
            $username = '';
            $ct = 0;
            $maxAttempts = 10;
            while( !$username && $ct < $maxAttempts ){
                $username = $ct ? $input->username."_$ct" : $input->username;
                $user = BIM_Model_User::getByUsername($username);
                if( $user && $user->isExtant() ){
                    $username = '';
                    $ct++;
                }
            }
            if( $username ){
                $email = "$username@kik.builtinmenlo.com";
                $adId = 'kik_'.uniqid(true);
                $user = self::create($adId);
                $app = new BIM_App_Users();
                $deviceToken = !empty($input->device_token) ? $input->device_token : 'kik_'.uniqid(true);
                $birthdate = '1970-01-01';

                $app->updateUsernameAvatarFirstRun($user->id, $username, $input->pic, $birthdate, $email, true, $deviceToken);
                $user->username = $username;

                $input->bim_id = $user->id;
                $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
                $dao->createKikUser( $input );
                echo "created $username for $input->username\n";
            }
        }
        return $user;
    }

    public static function logKikSend( $input ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        return $dao->logKikSend( $input );
    }

    public static function logKikOpen( $input ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        return $dao->logKikOpen( $input );
    }

    public static function getLatestKikUsers( ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $kikUsers = $dao->getLatestKikUsers( );

        $bimIds = array();
        foreach( $kikUsers as $kikUser )
            $bimIds[] = $kikUser->bim_id;
        $volleyDao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $verifyVolleyIds = $volleyDao->getVerifyVolleyIdForUser($bimIds);
        $volleys = BIM_Model_Volley::getMulti($verifyVolleyIds, true);
        foreach( $kikUsers as $kikUser ){
            foreach( $volleys as $volley ){
                if( $volley->creator->id == $kikUser->bim_id ){
                    $volley->creator->kik_id = $kikUser->username;
                    $volley->creator->last_login = $kikUser->last_login;
                    break;
                }
            }
        }
        $volleys = array_values($volleys);
        usort( $volleys,
            function ($a, $b) {
                if ($a->creator->last_login == $b->creator->last_login) {
                    return 0;
                }
                return ($a->creator->last_login > $b->creator->last_login) ? -1 : 1;
            }
        );
        return $volleys;
    }

    public static function getKikNames( $ids ){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $kikUsers = $dao->getKikNames( $ids );
        $names = array();
        foreach( $kikUsers as $user ){
            $names[$user->id] = $user->kik_id;
        }
        return $names;
    }
}
