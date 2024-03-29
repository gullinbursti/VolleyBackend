<?php

class BIM_Model_Club{

    const CLUB_IMAGE_DAFAULT_BASE_URL = 'https://d1fqnfrnudpaz6.cloudfront.net/defaultClubCover';

    public function __construct($clubId, $populateUserData = true, $submissionSort = NULL) {

        $club = null;
        if( is_object($clubId) ){
            $club = $clubId;
        } else {
            $dao = new BIM_DAO_Mysql_Club( BIM_Config::db() );
            $club = $dao->get( $clubId );
        }

        if( ! ($club && property_exists($club,'id')) ) {
            return;
        }

        foreach( $club as $prop => $value ){
            $this->$prop = $value;
        }

        $ownerUserId = $this->owner_id;
        unset( $this->owner_id );

        // Faking owner as a member to keep logic consistant for all
        $this->members[] = (object) array(
            'user_id' => $ownerUserId,
            'blocked' => 0,
            'pending' => 0
        );

        if( $populateUserData ){
            self::_fetchUserData( $this->members );
        }

        $this->processClubImage();

        $this->updated = $this->added;

        $this->pending = array();
        $this->blocked = array();
        $members = array();
        $memberCount = 0;
        foreach( $this->members as $member ){
            self::_cleanUpMember( $member );

            if( $member->blocked ){
                $this->blocked[] = $this->_convertBlockedMember( $member );
            } else if( $member->pending ){
                $this->pending[] = $this->_convertPendingMember( $member );
            } else if ( $member->user_id == $ownerUserId ) {
                $this->owner = $this->_convertOwnerMember( $member );
                $memberCount++;
            } else if( !empty($member->id) ){
                $members[] = $this->_convertJoinedMember( $member );
                $memberCount++;
            }
        }

        // The logic in BIM_DAO_Mysql_Club for total_members is beyond my
        // comprehension at this time.  The logic for total_members should not
        // even be in BIM_DAO_Mysql_Club IMHO!!!!
        //
        // Putting total_members counter in here!
        $this->total_members = $memberCount;
        $this->members = $members;

        $this->_populateSubmissions($submissionSort);
    }

    protected function processClubImage() {
        if ( empty($this->img) ) {
            $this->img = self::CLUB_IMAGE_DAFAULT_BASE_URL;
        }
    }

    private function _populateSubmissions($sort = NULL) {
        $volleys = BIM_Model_Volley::getClubVolleys( $this->id, $sort );

        $this->total_score = 0;
        $this->total_submissions = 0;
        $this->submissions = array();
        foreach ( $volleys as $volley ) {
            $newSubmission = $this->_convertSubmission( $volley );
            $this->submissions[] = $newSubmission;
            $this->total_score += $newSubmission->score;
            $this->total_submissions += 1 + $newSubmission->total_replies;
            unset( $newSubmission->total_replies );
        }
    }

    private function _convertSubmission( $volley ) {
        $submission = (object) array();
        $submission->challenge_id = $volley->id;
        $submission->parent_id = $volley->parent_id;
        $submission->user_id = $volley->creator->id;
        $submission->username = $volley->creator->username;
        $submission->avatar = $volley->creator->avatar;
        $submission->added = $volley->added;
        $submission->img = $volley->creator->img;
        $submission->text = ($volley->text ? $volley->text : "");
        $submission->subjects = $volley->creator->subjects;
        $this->_updateUpdatedIfNewer( $volley->updated );

        $submission->total_replies = 0;
        $submission->score = $volley->creator->score;
        foreach ( $volley->challengers as $challange ) {
            ++$submission->total_replies;
            $submission->score += $challange->score;
            $this->_updateUpdatedIfNewer( $challange->joined );
        }

        return $submission;
    }

    private static function _cleanUpMember( $member ) {
        if ( ! empty($member->user_id) ) {
            $member->id = $member->user_id;
        } else {
            $member->id = '';
            $member->user_id = '';
        }

        if( empty($member->email) ){
            $member->email = '';
        }

        if( empty($member->extern_name) ){
            $member->extern_name = '';
        }

        if( empty($member->mobile_number) ){
            $member->mobile_number = '';
        }
    }

    private function _convertOwnerMember( $member ) {
        $ownerMember = self::_convert(
            $member,
            array(
                'id' => 'id',
                'username' => 'username',
                'avatar' => 'avatar')
        );

        return $ownerMember;
    }

    private function _convertJoinedMember( $member ) {
        $joinedMember = self::_convert(
            $member,
            array(
                'id' => 'id',
                'username' => 'username',
                'avatar' => 'avatar',
                'invited' => 'invited',
                'joined' => 'joined')
        );

        $this->_updateUpdatedIfNewer( $joinedMember->joined );
        return $joinedMember;
    }

    private function _convertBlockedMember( $member ) {
        $blockedMember = self::_convert(
            $member,
            array(
                'id' => 'id',
                'username' => 'username',
                'avatar' => 'avatar',
                'extern_name' => 'extern_name',
                'phone' => 'mobile_number',
                'added' => 'blocked_date')
        );

        return $blockedMember;
    }

    private function _convertPendingMember( $member ) {
        $pendingMember = self::_convert(
            $member,
            array(
                'id' => 'id',
                'username' => 'username',
                'avatar' => 'avatar',
                'extern_name' => 'extern_name',
                'phone' => 'mobile_number',
                'invited' => 'invited')
        );

        return $pendingMember;
    }

    private static function _convert( $oldObject, $map ) {
        $newObject = (object) array();
        foreach ( $map as $newProperty => $oldProperty ) {
            $newObject->$newProperty = property_exists( $oldObject, $oldProperty )
                ? $oldObject->$oldProperty
                : '';
        }

        return $newObject;
    }

    private function _fetchUserData( $members ){
        $userIds = self::_getUserIdsFromMembers( $members );
        $users = BIM_Model_User::getMulti($userIds, true);

        foreach ( $members as $member ){
            $member->username = '';
            $member->avatar = '';

            if( !empty( $member->user_id ) ) {
                $user = $users[ $member->user_id ];

                if ( !empty($user->username) ) {
                    $member->username = $user->username;
                }

                $avatarUrl = $user->getAvatarUrl();
                if ( !empty($avatarUrl) ) {
                    $member->avatar = $avatarUrl;
                }
            }
        }
    }

    private static function _getUserIdsFromMembers( $members ) {
        $userIds = array();

        foreach( $members as $member ){
            if( !empty( $member->user_id) ){
                $userIds[] = $member->user_id;
            }
        }

        return array_unique($userIds);
    }

    private function _updateUpdatedIfNewer( $newTime ) {
        $newTimeEpoc = strtotime( $newTime );
        $updatedEpoc = strtotime( $this->updated );

        if ( $newTimeEpoc > $updatedEpoc ) {
            $this->updated = $newTime;
        }
    }

    /**
     * return the list of users
     * in this club including the owner
     */
    public function getUsers(){
        $userIds = array();
        foreach( $this->members as $member ){
            if( !empty( $member->id) ){
                $userIds[] = $member->id;
            }
        }
        foreach( $this->pending as $member ){
            if( !empty( $member->id) ){
                $userIds[] = $member->id;
            }
        }
        foreach( $this->blocked as $member ){
            if( !empty( $member->id) ){
                $userIds[] = $member->id;
            }
        }
        $userIds[] = $this->owner->id;
        return array_unique($userIds);
    }

    public function invite( $users = array(), $nonUsers = array() ){
        $dao = new BIM_DAO_Mysql_Club( BIM_Config::db( ) );
        // now we figure out if any of the users have actually been invited
        $invited = $dao->invite( $this->id, $users, $nonUsers );
        if( $invited ){
            $this->purgeFromCache();
            BIM_Model_User::purgeById($users);
        }
        return $invited;
    }

    public static function create( $name, $ownerId, $description = '', $img = '', $clubType = 'USER_GENERATED', $coords = NULL ) {
        $dao = new BIM_DAO_Mysql_Club( BIM_Config::db( ) );
        $clubId = $dao->create( $name, $ownerId, $description, $img, $clubType, $coords );
        if( $clubId ){
            BIM_Model_User::purgeById($ownerId);
        }
        return $clubId;
    }

    public static function makeCacheKeys( $ids ){
        return BIM_Utils::makeCacheKeys('club', $ids);
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
    public static function getMulti( $ids, $assoc = false, $sort = NULL ) {
        $keys = self::makeCacheKeys( $ids );
        $cache = new BIM_Cache( BIM_Config::cache() );
        $objs = $cache->getMulti( $keys );
        // now we determine which things were not in memcache and get those
        $retrievedKeys = array_keys( $objs );
        $missedKeys = array_diff( $keys, $retrievedKeys );
        if( $missedKeys ){
            $missingObs = array();
            foreach( $missedKeys as $objKey ){
                $objId = explode('_',$objKey);
                $missingObs[] = end($objId);
            }
            $dao = new BIM_DAO_Mysql_Club( BIM_Config::db() );
            $missingObjData = $dao->get($missingObs);
            foreach( $missingObjData as $objData ){
                $obj = new BIM_Model_Club( $objData, true, $sort );
                if( $obj->isExtant() ){
                    $objs[ $obj->id ] = $obj;
                }
            }
            // TODO seriously fix caching:
            //self::populateClubMembers( $objs );
            foreach( $objs as $obj ){
                $key = self::makeCacheKeys($obj->id);
                $cache->set( $key, $obj );
            }
        }

        // TODO - Fix this sorting crap.  Should only be sorting by last updated
        // now reorder according to passed ids
        $objArray = array();
        foreach( $objs as $id => $obj ){
            $objArray[ $obj->id ] = $obj;
        }
        $objs = array();
        foreach( $ids as $id ){
            if( !empty( $objArray[ $id ] ) ){
                $objs[ $id ] = $objArray[ $id ];
            }
        }

        if ( !$assoc ) {
            $clubs = array_values($objs);
            self::sortClubs( $clubs );
        } else {
            $clubs = $objs;
        }

        return $clubs;
    }

    protected static function sortClubs( &$clubs ) {
        $comparator = function($a, $b){
            if ($a->updated == $b->updated) {
                return 0;
            }
            return ($a->updated > $b->updated ) ? 1 : -1;
        };

        usort( $clubs, $comparator );
    }

    public static function get( $clubId, $forceDb = false ){
        $cacheKey = self::makeCacheKeys($clubId);
        $club = null;
        $cache = new BIM_Cache( BIM_Config::cache() );
        if( !$forceDb ){
            $club = $cache->get( $cacheKey );
        }
        if( !$club ){
            $club = new BIM_Model_Club($clubId);
            if( $club->isExtant() ){
                $cache->set( $cacheKey, $club );
            }
        }
        return $club;
    }

    private static function populateClubMembers( $clubs ){
        $userIds = array();
        foreach( $clubs as $club ){
            $ids = $club->getUsers();
            array_splice( $userIds, count( $userIds ), 0, $ids );
        }
        $userIds = array_unique($userIds);
        $users = BIM_Model_User::getMulti($userIds);
        foreach( $users as $user ){
            foreach( $clubs as $club ){
                $updated = $club->updateUser( $user );
            }
        }
    }

    public function updateUser( $userObj ){
        if( $this->owner->id == $userObj->id ){
            self::_updateUser($this->owner, $userObj);
        }
        if( !empty( $this->members ) ){
            foreach( $this->members as $member ){
                if( $member->id == $userObj->id ){
                    self::_updateUser($member, $userObj);
                }
            }
        }
    }

    public function isExtant(){
        return !empty( $this->id );
    }

    public function isNotExtant(){
        return (!$this->isExtant());
    }

    public function purgeFromCache(){
        $cache = new BIM_Cache( BIM_Config::cache() );
        $key = self::makeCacheKeys($this->id);
        $cache->delete( $key );
    }

    public function delete(){
        $dao = new BIM_DAO_Mysql_Club( BIM_Config::db() );
        $dao->delete( $this->id );
        $this->purgeFromCache();
    }

    /**
     * takes an object with property names that the property names of a club and
     * compares the values to this objects properties
     * if the values are different then the db will be updated
     * otherwise no action is taken
     */
    public function update( $data ){
        foreach( $data as $prop => $value ){
            $update = array();
            if( $this->$prop != $value ){
                $update[$prop] = $value;
            }
        }
        if( $update ){
            $dao = new BIM_DAO_Mysql_Club( BIM_Config::db() );
            $dao->update( $this->id, $update );
            $this->purgeFromCache();
        }
    }

    public function join( $userId ){
        $joined = false;
        $dao = new BIM_DAO_Mysql_Club( BIM_Config::db() );
        $joined = $dao->join( $this->id, $userId );
        if( $joined ){
            $this->purgeFromCache();
            // clear user from cache
            BIM_Model_User::purgeById($userId);
        }
        return $joined;
    }

    public function quit( $userId ){
        $quit = false;
        $dao = new BIM_DAO_Mysql_Club( BIM_Config::db() );
        $quit = $dao->quit( $this->id, $userId );
        if( $quit ){
            $this->purgeFromCache();
            // clear user from cache
            BIM_Model_User::purgeById($userId);
        }
        return $quit;
    }

    public function block( $userId ){
        $dao = new BIM_DAO_Mysql_Club( BIM_Config::db() );
        $blocked = $dao->block( $this->id, $userId );
        if( $blocked ){
            $this->purgeFromCache();
            // clear user from cache
            BIM_Model_User::purgeById($userId);
        }
        return $blocked;
    }

    public function unblock( $userId ){
        $dao = new BIM_DAO_Mysql_Club( BIM_Config::db() );
        $unblocked = $dao->unblock( $this->id, $userId );
        if( $unblocked ){
            $this->purgeFromCache();
            // clear user from cache
            BIM_Model_User::purgeById($userId);
        }
        return $unblocked;
    }

    public function getMemberIds(){
        $ids = array();
        foreach( $this->members as $member ){
            $ids[] = $member->id;
        }
        return $ids;
    }

    public function isMember( $userId ){
        $isMember = false;
        foreach( $this->members as $member ){
            if( $member->id == $userId ){
                $isMember = true;
                break;
            }
        }
        return $isMember;
    }

    public function isPending( $userId ){
        $isPending = false;
        foreach( $this->pending as $pending ){
            if( $pending->id == $userId ){
                $isPending = true;
                break;
            }
        }
        return $isPending;
    }

    public function isOwner( $userId ){
        return ($this->owner->id == $userId);
    }
}
