<?php 

class BIM_Model_Volley{
    
    public function __construct($volleyId, $populateUserData = true ) {
        
        $volley = null;
        if( is_object($volleyId) ){
            $volley = $volleyId;
        } else {
            $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
            $volley = $dao->get( $volleyId );
        }
        
        if( $volley && property_exists($volley,'id') ){
            $this->id = $volley->id;
            $this->status = $volley->status_id;
            $this->_setSubject($volley);
            $this->comments = 0; //$dao->commentCount( $volley->id );
            $this->viewed = array();
            $this->has_viewed = '';
            $this->total_replies = $volley->total_replies;
            $this->started = $volley->started;
            $this->added = $volley->added;
            $this->updated = $volley->updated;
            $this->expires = $volley->expires;
            $this->is_private = (int) $volley->is_private;
            $this->is_verify = (int) $volley->is_verify;
            
            $this->total_likers = 0;
            // setting up recent likes
            $this->recent_likes = $volley->recent_likes;
            if( $this->recent_likes == '' ){
                $this->setRecentLikes();
            } else {
                $this->recent_likes = json_decode( $this->recent_likes );
            }
            $this->populateRecentLikes();
            
            $creator = (object) array(
                'id' => $volley->creator_id,
                'img' => $volley->creator_img ? $volley->creator_img : '',
                'score' => $volley->creator_likes,
                'subject' => $this->subject,
            );
            if($this->is_private){
                $this->viewed[ $creator->id ] = $volley->has_viewed;
            }
            
            $this->creator = $creator;
            $this->resolveScore($creator);
            
    	    $this->is_explore = $volley->is_explore;
            $this->is_celeb = BIM_Utils::isCelebrity( $volley->creator_id );
            
            $challengers = array();
            
            $allUsers = array( $creator->id => 1 );
            
            foreach( $volley->challengers as $challenger ){
                $joined = new DateTime( "@$challenger->joined" );
                $joined = $joined->format('Y-m-d H:i:s');
                
                $target = (object) array(
                    'id' => $challenger->challenger_id,
                	'img' => $challenger->challenger_img ? $challenger->challenger_img : '',
                    'score' => $challenger->likes,
                    'subject' => empty($challenger->subject) ? $this->subject : $challenger->subject,
                	'joined' => $joined,
                    'joined_timestamp' => $challenger->joined,
                );
                if($this->is_private){
                    $this->viewed[ $target->id ] = $challenger->has_viewed;
                }
                $this->resolveScore($target);
                $challengers[] = $target;
                $allUsers[ $target->id ] = 1;
            }
            
            $this->challengers = $challengers;
            
            $this->total_users = count( $allUsers );
            
            if( $populateUserData ){
                $this->populateUsers();
            }
        }
    }
    
    private function populateRecentLikes(){
        if( $this->recent_likes ){
            $users = BIM_Model_User::getMulti($this->recent_likes, true);
            foreach( $this->recent_likes as &$id ){
                if( !empty( $users[ $id ] ) ){
                    $uData = $users[ $id ];
                    $id = (object) array(
                    	'id' => $uData->id, 
                    	'username' => $uData->username
                    );
                }
            }
        }
    }
    
    public function getPics( $userId ){
        $pics = array();
        if( $this->creator->id == $userId ){
            $pics[] = $this->creator;
        }
        foreach( $this->challengers as $challenger ){
            if( $challenger->id == $userId ){
                $pics[] = $challenger;
            }
        }
        return $pics;
    }
    
    public function getUserPics( $userId ){
        $pics = array();
        if( $this->isCreator($userId) ){
            $pics[] = $this->creator->img;
        }
        foreach( $this->challengers as $challenger ){
            if( $challenger->id == $userId ){
                $pics[] = $challenger->img;
            }
        }
        return $pics;
    }
    
    public function setAsCreator( $userId ){
        $data = $this->hasUser( $userId );
        if( $data ){
            $this->creator = $data;
        }
        return $data;
    }
    
    public function setRecentLikes( ){
        $dao = new BIM_DAO_Mysql_Volleys(BIM_Config::db());
        $this->recent_likes = $dao->getRecentLikes( $this->id );
        $this->total_likers = count($this->recent_likes);
        array_splice( $this->recent_likes, 3 );
        if( $this->recent_likes ){
            $dao->setRecentLikes( $this->id, $this->recent_likes );
        }
    }
    
    private function _setSubject( $me ){
        if( empty($me->subject) ){
            $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
            $this->subject = $dao->getSubject( $me->subject_id );
            $dao->setSubject( $this->id );
        } else {
            $this->subject = $me->subject;
        }
    }
    /*
     * This function will gather all of the user ids 
     * and call BIM_Model_User::getMulti()
     * and populate the data structures accordingly
     * 
     */
    protected function populateUsers(){
        $userIds = $this->getUsers();
        $users = BIM_Model_User::getMulti($userIds, true);
        
        // populate the creator
        $creator = $users[ $this->creator->id ];
        self::_updateUser($this->creator, $creator);
        
        // populate the challengers
        $challengers = $this->challengers;
	    foreach ( $challengers as $challenger ){
            $target = $users[ $challenger->id ];
            self::_updateUser($challenger, $target);
        }
    }
    
    /**
     * this function is for updating the various user data in the creator or challengers array
     */
    private static function _updateUser($user,$update){
        $user->username = $update->username;
        $user->avatar = $update->getAvatarUrl();
        $user->age = $update->age;
    }
    
    private function resolveScore( $userData ){
        $score = !empty($userData->score) ?  $userData->score : 0;
        
        if( $userData->score < 0 ){
            $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
            $score = $dao->getLikes($this->id, $userData->id);
            $isCreator = $this->isCreator($userData->id);
            $dao->setLikes( $this->id, $userData->id, $score, $isCreator );
        }
        $userData->score = $score;
    }
    
    public function getLatestImage(){
        $img = null;
        if( $this->challengers ){
            $img = end( $this->challengers );
            $img = $img->img;
        }
        if( !$img ){
            $img = $this->creator->img;
        }
        return $img;
    }
    
    /**
     * return the list of users
     * in the volley including the creator
     */
    public function getUsers(){
        $userIds = array();
        foreach( $this->challengers as $challenger ){
	        $userIds[] = $challenger->id;
	    }
    	$userIds[] = $this->creator->id;
    	return array_unique($userIds);
    }
    
    public function comment( $userId, $text ){
        $comment = BIM_Model_Comments::create( $this->id, $userId, $text );
        $this->purgeFromCache();
    }
    
    public function getComments(){
        return BIM_Model_Comments::getForVolley( $this->id );
    }
    
    public function getCreatorImage(){
        return $this->creator->img.'Small_160x160.jpg';
    }
    
    public function isExpired(){
        $expires = -1;
        if( !empty( $this->expires ) && $this->expires > -1 ){
            $expires = $this->expires - time();
            if( $expires < 0 ){
                $expires = 0;
            }
        }
        return ($expires == 0);
    }
    
    /**
     * 
     * returns true or false depending
     * if the passed user id can cast an approve vote
     * for the creator of this volley
     * 
     * This ONLY anly applies to a verification volley
     * 
     * if this IS NOT a verification volley then this 
     * function will always return true
     * 
     * @param int $userId
     */
    public function canApproveCreator( $userId ){
        $OK = true;
        if( !empty($this->is_verify) ){
            $OK = false;
            if( ! $this->isCreator($userId) && ! $this->hasApproved( $userId ) ){
                $OK = true;
            }
        }
        return $OK;
    }
    
    public function sortChallengers(){
        if($this->challengers){
            usort($this->challengers, 
                function( $a, $b ){
                    $al = $a->joined_timestamp;
                    $bl = $b->joined_timestamp;
                    if ( $al == $bl ) {
                        return 0;
                    }
                    return ($al > $bl) ? 1 : -1;
                }
            );
        }
    }
    
    public function isCreator( $userId ){
        return ($this->creator->id == $userId );
    }
    
    public function hasApproved( $userId ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        return $dao->hasApproved( $this->id, $userId );
    }
    
    public static function create( $userId, $hashTag, $imgUrl, $targetIds = array(), $isPrivate = false, $expires = -1, $isVerify = false, $status = 2 ) {
        $volleyId = null;
        $hashTagId = self::getHashTagId($userId, $hashTag);
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $volleyId = $dao->add( $userId, $targetIds, $hashTagId, $hashTag, $imgUrl, $isPrivate, $expires, $isVerify, $status );
        BIM_Model_User::purgeById( array( $userId ) );
        return self::get( $volleyId );
    }
    
/**
(object) array(
	"id"=> "37726",
	"status"=> "9",
	"subject"=> "#__verifyMe__",
	"comments"=> 0,
	"has_viewed"=> 0,
	"started"=> "2013-10-25 20:29:19",
	"added"=> "2013-10-25 20:29:19",
	"updated"=> "2013-10-26 15:31:30",
	"expires"=> "-1",
	"is_private"=> "N",
	"is_verify"=> 1,
	"is_celeb"=> 0,
	"creator"=> (object) array(
		"id"=> "13248",
		"img"=> "https:\/\/d3j8du2hyvd35p.cloudfront.net\/925a2ae0a711f6c72d456ca2e4ef75d1aaa98e9db45587eb7dec23b5d46f80b3-1382801487.jpg",
		"score"=> 0,
		"username"=> "bimtester7",
		"avatar"=> "https:\/\/d3j8du2hyvd35p.cloudfront.net\/925a2ae0a711f6c72d456ca2e4ef75d1aaa98e9db45587eb7dec23b5d46f80b3-1382801487Large_640x1136.jpg",
		"age"=> "1998-10-25 00:00:00"
	),
	"challengers"=> []
) 
 */
    public static function getAccountSuspendedVolley( $targetId ){
        $target = BIM_Model_User::get($targetId);
        $img = $target->getAvatarUrl();
        $img = str_replace('Large_640x1136.jpg', '', $img);
        
        $vv = (object) array(
        	"id"=> 1,
        	"status"=> "9",
        	"subject"=> "#Account_Disabled_Temporarily",
        	"comments"=> 0,
        	"has_viewed"=> 0,
        	"started"=> "1970-01-01 00:00:00",
        	"added"=> "1970-01-01 00:00:00",
        	"updated"=> "1970-01-01 00:00:00",
        	"is_verify"=> 1,
        	"is_celeb"=> 0,
        	"creator"=> (object) array(
        		"id"=> $target->id,
        		"img"=> $img,
        		"score"=> 0,
        		"username"=> $target->username,
        		"avatar"=> $target->getAvatarUrl(),
        		"age"=> $target->age
        	),
        	"challengers"=> array(
                (object) array(
                    'id' => $target->id,
                    'img' => $img,
                    'score' => 0,
                    'joined' => '1970-01-01 00:00:00',
                    'username' => $target->username,
                    'avatar' => $target->getAvatarUrl(),
                    'age' => $target->age,
                )
        	)
        );
        return $vv;
    }
    
    public static function getVerifyVolley( $creatorId ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $id = $dao->getVerifyVolleyIdForUser( $creatorId );
        return BIM_Model_Volley::get( $id );
    }
    
    public static function createVerifyVolley( $targetId, $status = 9 ){
	    $target = BIM_Model_User::get( $targetId );
	    $imgUrl = trim($target->getAvatarUrl());
	    // now we get our avatar image and 
	    // create the a url that points to the large version
	    if( preg_match('/defaultAvatar/',$imgUrl) ){
	        $imgUrl = preg_replace('/defaultAvatar\.png/i', 'defaultAvatar_o.jpg', $imgUrl);
	    } else if( preg_match('/orig\.jpg/',$imgUrl) ){ 
	        $imgUrl = 'https://d3j8du2hyvd35p.cloudfront.net/defaultAvatar_o.jpg';
	    } else {
    	    $imgUrl = preg_replace('/Large_640x1136/i', '', $imgUrl);
	    }
	    return self::create($targetId, '#__verifyMe__', $imgUrl, array(), 'N', -1, true, $status);
    }
    
    public static function addVerifVolley( $targetId, $imgUrl = null ){
        $vv = self::getVerifyVolley($targetId);
        if( $vv->isNotExtant() ){
    	    $target = BIM_Model_User::get( $targetId );
    	    //if( $target->ageOK() ){
                $vv = self::createVerifyVolley($targetId);
    	    //}
        } else if( $imgUrl ){
            $vv->updateImage( $imgUrl );
        }
    }
    
    public static function getHashTagId( $userId, $hashTag = 'N/A' ) {
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $hashTagId = $dao->getHashTagId($hashTag, $userId);
        if( !$hashTagId ){
            $hashTagId = $dao->addHashTag($hashTag, $userId);
        }
        return $hashTagId;
    }
    
    public function canJoin( $userId ){
        $canJoin = true;
        if( $this->is_private ){
            $canJoin = false;
            if( $this->hasUser($userId) ){
                $canJoin = true;
            }
        }
        return $canJoin;
    }
    
    // $userId, $imgUrl
    public function join( $userId, $imgUrl, $hashTag = '' ){
        if( $this->canJoin($userId) ){
            $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
            if( !$hashTag ){
                $hashTag = $this->subject;
            }
            $dao->join( $this->id, $userId, $imgUrl, $hashTag );
            
            $this->purgeFromCache();
            BIM_Model_User::purgeById( $userId );
        }
    }
    
    public function updateStatus( $status ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $dao->updateStatus( $this->id, $status );
        $this->purgeFromCache();
    }
    
    public function updateHashTag( $hashTag ){
        $hashTag = preg_replace('@#@','',$hashTag);
        $hashTag = "#$hashTag";
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $dao->updateHashTag( $this->id, $hashTag );
        $this->purgeFromCache();
    }
    
    public function updateImage( $url ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
	    $url = preg_replace('/Large_640x1136/i', '', $url);
        $dao->updateImage( $this->id, $url );
        $this->purgeFromCache();
    }
    
    public function acceptFbInviteToVolley( $userId, $inviteId ){
        $this->updateStatus(2);
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $dao->acceptFbInviteToVolley( $this->id, $userId, $inviteId );
        $this->purgeFromCache();
    }
    
    public function upVote( $targetId, $userId, $imgUrl ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $dao->upVote( $this->id, $userId, $targetId, $imgUrl  );
        $this->setRecentLikes();
        $this->purgeFromCache();
    }
    
    public function purgeFromCache(){
        $key = self::makeCacheKeys($this->id);
        $conf = BIM_Config::cache();
        $cache = new BIM_Cache( $conf );
        $cache->delete( $key );
    }
    
    public function accept( $userId, $imgUrl ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $dao->accept( $this->id, $userId, $imgUrl );
        $this->purgeFromCache();
    }
    
    public function cancel(){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $dao->cancel( $this->id );
        $this->purgeFromCache();
    }
    
    public function flag( $userId ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $dao->flag( $this->id, $userId );
        $this->purgeFromCache();
    }
    
    public function setPreviewed( $userId ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $dao->setPreviewed( $this->id, $userId );
        $this->purgeFromCache();
        BIM_Model_User::purgeById($userId);
    }
    
    public function isExtant(){
        return !empty( $this->id );
    }
    
    public function isNotExtant(){
        return (!$this->isExtant());
    }
    
    public function updateUser( $userObj ){
        if( $this->creator->id == $userObj->id ){
            self::_updateUser($this->creator, $userObj);
        }
        if( !empty( $this->challengers ) ){
            foreach( $this->challengers as $challenger ){
                if( $challenger->id == $userObj->id ){
                    self::_updateUser($challenger, $userObj);
                }
            }
        }
    }
    
    public function hasChallenger( $userId ){
        $has = false;
        if( !empty( $this->challengers ) ){
            foreach( $this->challengers as $challenger ){
                if( $challenger->id == $userId ){
                    $has = $challenger;
                    break;
                }
            }
        }
        return $has;
    }
    
    public function hasUser( $userId ){
        $has = ($this->creator->id == $userId) ? $this->creator : null;
        if( !$has ){
            $has = $this->hasChallenger($userId);
        }
        return $has;
    }
    
    public static function getRandomAvailableByHashTag( $hashTag, $userId = null ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $v = $dao->getRandomAvailableByHashTag( $hashTag, $userId );
        if( $v ){
            $v = self::get( $v->id );
        }
        return $v;
    }
    
    public static function getAllForUser( $userId ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $volleyIds = $dao->getAllIdsForUser( $userId, true );
        return self::getMulti($volleyIds);
    }
    
    /** 
     * Helper function to build a list of opponents a user has played with
     * @param $user_id The ID of the user to get challenges (integer)
     * @return An array of user IDs (array)
    **/
    public static function getOpponents($user_id, $private = false) {
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $ids = $dao->getOpponents( $user_id, $private );
        // push opponent id
        $id_arr = array();
        foreach( $ids as $row ){
            $id_arr[] = ( $user_id == $row->creator_id ) ? $row->challenger_id : $row->creator_id;
        }
        $id_arr = array_unique($id_arr);
        return $id_arr;
    }
    
    /** 
     * Helper function to build a list of challenges between two users
     * @param $user_id The ID of the 1st user to get challenges (integer)
     * @param $opponent_id The ID of 2nd the user to get challenges (integer)
     * @param $last_date The timestamp to start at (integer)
     * @return An associative obj of challenge IDs paired w/ timestamp (array)
    **/
    public static function withOpponent($userId, $opponentId, $lastDate="9999-99-99 99:99:99", $private ) {
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $volleys = $dao->withOpponent($userId, $opponentId, $lastDate, $private);
        
        $volleyArr = array();
        foreach( $volleys as $volleyData ){
            $volleyArr[ $volleyData->id ] = $volleyData->updated;
        }
        return $volleyArr;
    }
    
    /** 
     * Gets all the public challenges for a user
     * @param $user_id The ID of the user (integer)
     * @return The list of challenges (array)
    **/
    public static function getVolleys($userId ) {
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $volleyIds = $dao->getIdsForCreator($userId);
        return self::getMulti($volleyIds);
    }
    
    public static function getPrivateVolleys( $userId ) {
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $volleyIds = $dao->getIds($userId, true);
        return self::getMulti($volleyIds);
    }
    
    public static function makeCacheKeys( $ids ){
        return BIM_Utils::makeCacheKeys('volley', $ids);
    }
    
    /** 
     * 
     * do a multifetch to memcache
     * if there are any missing objects
     * get them from the db.
     *   
     * we get multiple objects in one query
     * to reduce trips to the db and network 
     * 
    **/
    public static function getMulti( $ids, $assoc = false ) {
        $volleyKeys = self::makeCacheKeys( $ids );
        $cache = new BIM_Cache( BIM_Config::cache() );
        $volleys = $cache->getMulti( $volleyKeys );
        // now we determine which things were not in memcache dn get those
        $retrievedKeys = array_keys( $volleys );
        $missedKeys = array_diff( $volleyKeys, $retrievedKeys );
        if( $missedKeys ){
            $missingVolleys = array();
            foreach( $missedKeys as $volleyKey ){
                $volleyId = explode('_',$volleyKey);
                $missingVolleys[] = end($volleyId);
            }
            $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
            $missingVolleyData = $dao->get($missingVolleys);
            foreach( $missingVolleyData as $volleyData ){
                $volley = new self( $volleyData, false );
                if( $volley->isExtant() ){
                    $volleys[ $volley->id ] = $volley;
                }
            }
            self::populateVolleyUsers( $volleys );
            foreach( $volleys as $volley ){
                $key = self::makeCacheKeys($volley->id);
                $cache->set( $key, $volley );
            }
        }
        
        // now reorder according to passed ids
        $volleyArr = array();
        foreach( $volleys as $id => $volley ){
            // $volley->sortChallengers();
            $volleyArr[ $volley->id ] = $volley;
        }
        $volleys = array();
        foreach( $ids as $id ){
            if( !empty( $volleyArr[ $id ] ) ){
                $volleys[ $id ] = $volleyArr[ $id ];
            }
        }
        
        return $assoc ? $volleys : array_values($volleys);
    }
    
    private static function populateVolleyUsers( $volleys ){
        $userIds = array();
        foreach( $volleys as $volley ){
            $ids = $volley->getUsers();
            array_splice( $userIds, count( $userIds ), 0, $ids );
        }
        $userIds = array_unique($userIds);
        $users = BIM_Model_User::getMulti($userIds);
        foreach( $users as $user ){
            foreach( $volleys as $volley ){
                $updated = $volley->updateUser( $user );
            }
        }
    }

    public static function get( $volleyId, $forceDb = false ){
        $cacheKey = self::makeCacheKeys($volleyId);
        $volley = null;
        $cache = new BIM_Cache( BIM_Config::cache() );
        if( !$forceDb ){
            $volley = $cache->get( $cacheKey );
        }
        if( !$volley ){
            $volley = new self($volleyId);
            if( $volley->isExtant() ){
                $cache->set( $cacheKey, $volley );
            }
        }
        //$volley->sortChallengers();
        return $volley;
    }
    
    public static function getVolleysWithFriends( $userId ){
        $friends = BIM_App_Social::getFollowed( (object) array('userID' => $userId, 'size' => 100 ) );
        $friendIds = array_map(function($friend){return $friend->user->id;}, $friends);
        // we add our own id here so we will include our challenges as well, not just our friends
        $friendIds[] = $userId;
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $ids = $dao->getVolleysWithFriends($userId, $friendIds);
        return self::getMulti($ids);
    }
    
    public static function getTopHashTags( $subjectName ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        return $dao->getTopHashTags($subjectName);
    }
    
    public static function getTopVolleysByVotes( $timeInPast = null, $limit = 64 ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $ids = $dao->getTopVolleysByVotes( $timeInPast, $limit );
        return self::getMulti($ids);
    }
    
    public static function getManagedVolleys( ){
        $ids = self::getExploreIds();
        $ct = min( array(count($ids), 16) );
        $indexes = array_rand( $ids, $ct );
        $newIds = array();
        foreach( $indexes as $index ){
            $newIds[] = $ids[ $index ];
        }
        shuffle($newIds);
        return self::getMulti( $newIds );
    }
    
    public static function autoVolley( $userId ){
		// starting users & snaps
        $teamVolleyId = BIM_Config::app()->team_volley_id;
        $snap_arr = array(
        	array(// @Team Volley #welcomeVolley
        		'user_id' => $teamVolleyId, 
        		'subject_id' => "1367", 
        		'img_prefix' => "https://hotornot-challenges.s3.amazonaws.com/fffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffb_0000000000"),
        	
        	array(// @Team Volley #teamVolleyRules
        		'user_id' => $teamVolleyId, 
        		'subject_id' => "1368", 
        		'img_prefix' => "https://hotornot-challenges.s3.amazonaws.com/fffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffb_0000000001"),
        		
        	array(// @Team Volley #teamVolley
        		'user_id' => $teamVolleyId, 
        		'subject_id' => "1369", 
        		'img_prefix' => "https://hotornot-challenges.s3.amazonaws.com/fffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffb_0000000002"),
        		
        	array(// @Team Volley #teamVolleygirls
        		'user_id' => $teamVolleyId, 
        		'subject_id' => "1370", 
        		'img_prefix' => "https://hotornot-challenges.s3.amazonaws.com/fffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffb_0000000003")
        );
        // choose random snap
        $snap = $snap_arr[ array_rand( $snap_arr ) ];
		$subjectId = $snap['subject_id'];
		$autoUserId = $snap['user_id'];
		$img = $snap['img_prefix'];

		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
		$hashTag = $dao->getSubject($subjectId);
		
		self::create($userId, $hashTag, $img, array( $userId ), 'N', -1);
    }
    
    public static function warmCache(){
		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $volleyIds = $dao->getVolleyIds();
        while( $volleyIds ){
            $ids = array_splice($volleyIds, 0, 250);
            self::getMulti($ids);
            print count( $volleyIds )." remaining\n";
        }
    }
    
    public static function getSticky(){
        $c = BIM_Config::app();
        $ids = array();
        if( !empty($c->sticky_volleys) ){
            $ids = $c->sticky_volleys;
        }
        return self::getMulti($ids);
    }
    
    public static function assignVerifyVolleysToAll(){
        $dao = new BIM_DAO_Mysql_User( BIM_Config::db() );
        $userIds = $dao->getAllIds();
        foreach( $userIds as $userId ){
            $user = BIM_Model_User::get( $userId );
            if( $user->isExtant() ){
                $vv = self::getVerifyVolley($user->id);
                if( $vv->isNotExtant() ){
                    $vv = self::createVerifyVolley($user->id);
                    if( $vv->isExtant() ){
                        print "created a verify volley for $user->username : $user->id\n";
                    } else {
                        print "could not create a verify volley for $user->username : $user->id\n";
                    }
                } else {
                    print "a verify volley exists for $user->username : $user->id\n";
                }
            }
        }
    }
    
    public function getFlagCounts(){
        $counts = (object) array(
            'approves' => 0,
            'flags' => 0,
            'abuse_ct' => 0,
        );
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $data = $dao->getFlagCounts( $this->id );
        if( $data ){
            $counts->approves = $data[0]?:0;
            $counts->flags = $data[1]?:0;
            $counts->abuse_ct = ($counts->flags + $counts->approves);
        }
        return $counts;
    }    
    
    public static function fixVerificationImages(){
		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $volleyIds = $dao->getAllVerificationVolleyIds();
        while( $volleyIds ){
            $ids = array_splice($volleyIds, 0, 250);
            $volleys = self::getMulti($ids);
            foreach( $volleys as $volley ){
                $imgUrl = $volley->creator->avatar;
        	    if( preg_match('/\.png/',$imgUrl) ){
        	        $imgUrl = preg_replace('/defaultAvatar\.png/i', 'defaultAvatar_o.jpg', $imgUrl);
        	    } else {
            	    $imgUrl = preg_replace('/^(.*?)\.jpg$/', '$1_o.jpg', $imgUrl);
        	    }
	            if( $imgUrl ){
                    $volley->updateImage( $imgUrl );
                    echo "updated volley $volley->id for user ".$volley->creator->username." with image $imgUrl\n";
	            }
            }
            print count( $volleyIds )." remaining\n";
        }
    }
    
    public static function fixAbuseCount(){
		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $volleyIds = $dao->getAllVerificationVolleyIds();
        while( $volleyIds ){
            $ids = array_splice($volleyIds, 0, 250);
            $volleys = self::getMulti($ids);
            foreach( $volleys as $volley ){
                $counts = $volley->getFlagCounts();
                $user = BIM_Model_User::get( $volley->creator->id );
                echo " updating abuse count for $user->username with $counts->abuse_ct\n ";
                $user->updateAbuseCount( $counts->abuse_ct );
            }
            print count( $volleyIds )." remaining\n";
        }
    }
    
    public static function processVolleyImages( $volleyIds ){
        $conf = BIM_Config::aws();
        S3::setAuth($conf->access_key, $conf->secret_key);
        while( $volleyIds ){
            $ids = array_splice($volleyIds, 0, 250);
            $volleys = BIM_Model_Volley::getMulti($ids);
            foreach( $volleys as $volley ){
                self::processImage( $volley->creator->img );
                foreach( $volley->challengers as $challenger ){
                    if( !empty( $challenger->img ) ){
                        self::processImage( $challenger->img );
                    }
                }
                echo "processed volley $volley->id\n\n";
            }
            print count( $volleyIds )." remaining\n\n====\n\n";
        }
    }
    
    public static function processImage( $imgPrefix, $bucket = 'hotornot-challenges' ){
        echo "converting $imgPrefix\n";
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
        }
        echo "\n";
        return $image;
    }
    
    public static function updateExploreIds( $volleyData ){
		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
		return $dao->updateExploreIds( $volleyData );
    }
    
    public static function getExploreIds( ){
		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
		return $dao->getExploreIds( );
    }
    
    public static function isCreatorImage( $imgUrl ){
		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
		$ids = $dao->getIdsByCreatorImage( $imgUrl );
		return !empty($ids);
    }
    
    public static function deleteByImage( $imgUrl ){
		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
		$dao->deleteByImage( $imgUrl );
    }
    
    public static function isParticipantImage( $imgUrl ){
		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
		$ids = $dao->getIdsByParticipantImage( $imgUrl );
		return !empty($ids);
    }

    public static function deleteImageByUserIdAndImage( $userId, $imgPrefix ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $dao->deleteImageByUserIdAndImage( $userId, $imgPrefix );
    }
    
    public static function deleteParticipantByImage( $imgUrl ){
		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
		$dao->deleteParticipantByImage( $imgUrl );
    }
    
    public static function deleteVolleys( $ids, $userId = null ){
		$dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
		$dao->deleteVolleys($ids, $userId);
    }
    
    public static function makeShoutoutVolley($volleyId, $creatorId){
        $volley = BIM_Model_Volley::get( $volleyId );
        $shoutout = null;
        if( $volley->isExtant() && !preg_match('@defaultAvatar@',$volley->creator->img) ){
            $suffix = 'Large_640x1136.jpg';
            
            $namePrefix = 'Shoutout_Volley_Image-'.uniqid(true);
            $name = "{$namePrefix}{$suffix}";
            $imgUrlPrefix = "https://d1fqnfrnudpaz6.cloudfront.net/$namePrefix";
            
            $imgUrl = $volley->creator->img;
            $imgUrl = preg_replace('@\.jpg|Large_640x1136|_o\.jpg@','', $imgUrl);
            $imgUrl = $imgUrl.$suffix;
            
            BIM_Utils::copyImage( $imgUrl, $name );
            BIM_Utils::processImage($imgUrlPrefix);
            
            $hashTag = "#shoutout";
            
            $shoutout = BIM_Model_Volley::create( $creatorId, $hashTag, $imgUrlPrefix );
            self::logShoutout($shoutout->id, $volley->id, $volley->creator->id);
            BIM_Push::shoutoutPush( $creatorId, $volley->creator->id, $shoutout->id );
            // 20% chance we establish a follow relationship between shoutee and the shouter
            if( mt_rand(1,100) <= 20 ){
                $params = (object) array(
                    'target' => $creatorId,
                    'userID' => $volley->creator->id
                );
                BIM_App_Social::addFriend($params);
            }
        }
        return $shoutout;
    }
    
    public static function shoutoutVerifyVolley($creatorId, $targetId){
        $user = BIM_Model_User::get( $targetId );
        $shoutout = null;
        if( !empty( $user->img_url ) && !preg_match('@default@i', $user->img_url ) ) {
            
            $suffix = 'Large_640x1136.jpg';
            $namePrefix = 'Shoutout_Volley_Image-'.uniqid(true);
            $imgUrlPrefix = "https://d1fqnfrnudpaz6.cloudfront.net/$namePrefix";
            
            $imgUrl = $user->img_url;
            $imgUrl = preg_replace('@\.jpg|Large_640x1136|_o\.jpg@','', $imgUrl);
            $imgUrl = $imgUrl.$suffix;
            
            $name = "{$namePrefix}{$suffix}";
            BIM_Utils::copyImage( $imgUrl, $name );
            BIM_Utils::processImage($imgUrlPrefix);
            
            $hashTag = "#shoutout";
            
            $shoutout = BIM_Model_Volley::create( $creatorId, $hashTag, $imgUrlPrefix );
            BIM_Push::shoutoutPush( $creatorId, $user->id, $shoutout->id );
            self::logShoutout( $shoutout->id, 0, $user->id );
        }
        return $shoutout;
    }
    
    public static function logShoutout( $shoutoutId, $targetVolleyId, $targetId ){
        $dao = new BIM_DAO_Mysql_Volleys( BIM_Config::db() );
        $dao->logShoutout( $shoutoutId, $targetVolleyId, $targetId );
    }
}
