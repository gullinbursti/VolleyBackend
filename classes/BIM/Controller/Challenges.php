<?php

class BIM_Controller_Challenges extends BIM_Controller_Base {

    public function getChallengesForUserBeforeDate(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if (isset($input->userID) && isset($input->prevIDs) && isset($input->datetime)){
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            return $challenges->getChallengesForUserBeforeDate( $userId, $input->prevIDs, $input->datetime);
        }
    }

    public function submitChallengeWithChallenger(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if (isset($input->userID) && isset($input->subject) && isset($input->imgURL) && isset($input->challengerID)){
            $input->imgURL = $this->normalizeVolleyImgUrl($input->imgURL);
            $challengerIds = explode('|', $input->challengerID );
            $isPrivate = !empty( $input->isPrivate ) ? $input->isPrivate : 'N';
            $expires = $this->resolveExpires();
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges;
            return $challenges->submitChallengeWithChallenger( $userId, $input->subject, $input->imgURL, $challengerIds, $isPrivate, $expires );
        }
    }

    public function messageSeen(){
        return $this->updatePreviewed();
    }

    public function updatePreviewed(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if (!empty($input->challengeID) && !empty($input->userID) ){
            $challenges = new BIM_App_Challenges();
            $input->userID = $this->resolveUserId( $input->userID );
            $volley = $challenges->updatePreviewed($input->challengeID, $input->userID );
            return array(
                'id' => $volley->id
            );
        }
        return array();
    }

    public function getPreviewForSubject(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if (isset($input->subjectName)){
            $challenges = new BIM_App_Challenges();
            return $challenges->getPreviewForSubject($input->subjectName);
        }
    }

    public function getAllChallengesForUser(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if ( isset( $input->userID ) ){
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            return $challenges->getAllChallengesForUser( $userId );
        }
    }

    public function getChallengesForUser(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if ( !empty( $input->userID ) ){
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            return $challenges->getChallengesForUser( $userId );
        }
    }

    public function getPrivateChallengesForUser(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if (isset($input->userID)){
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            return $challenges->getChallengesForUser($userId, TRUE); // true means get private challenge only
        }
    }

    /*
     * returns all challeneges including those without an opponent
     */
    public function getPublicChallenges(){
        return $this->getPublic();
    }

    /*
     * returns all challeneges including those without an opponent
     */
    public function getPrivateChallenges(){
        return $this->getPrivate();
    }


    /*
     * returns all challeneges including those without an opponent
     */
    public function getPublic(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if ( !empty( $input->userID ) ){
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            return $challenges->getChallenges( $userId );
        }
    }

    /*
     * returns all challeneges including those without an opponent
     */
    public function getPrivate(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if ( !empty( $input->userID ) ){
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            return $challenges->getChallenges( $userId, true ); // true means get private challenge only
        }
    }

    public function getPrivateChallengesForUserBeforeDate(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if (isset($input->userID) && isset($input->prevIDs) && isset($input->datetime)){
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            return $challenges->getChallengesForUserBeforeDate($userId, $input->prevIDs, $input->datetime, TRUE); // true means get private challenges only
        }
    }

    public function submitMatchingChallenge(){
        $uv = null;
        $input = (object) ($_POST ? $_POST : $_GET);
        if (!empty($input->userID) && !empty($input->subject) && !empty($input->imgURL)){
            $input->imgURL = $this->normalizeVolleyImgUrl($input->imgURL);
            $userId = $this->resolveUserId( $input->userID );
            $expires = $this->resolveExpires();
            $challenges = new BIM_App_Challenges();
            $uv = $challenges->submitMatchingChallenge( $userId, $input->subject, $input->imgURL, $expires );
        }
        return $uv;
    }

    protected function resolveExpires(){
        $input = (object) ($_POST ? $_POST : $_GET);
        $expires = !empty( $input->expires ) ? $input->expires : 1;
        $expireTime = -1;
        $time = time();
        if( $expires == 2 ){
            $expireTime = $time + 600;
        } else if( $expires == 3 ){
            $expireTime = $time + 86400;
        }
        return $expireTime;
    }

    public function flagChallenge(){
        $uv = null;
        $input = (object) ($_POST ? $_POST : $_GET);
        if ( !empty($input->userID) && !empty($input->challengeID) ){
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            $uv = $challenges->flagChallenge( $userId, $input->challengeID );
        }
        return $uv;
    }

    public function cancelChallenge(){
        $uv = null;
        $input = (object) ($_POST ? $_POST : $_GET);
        if (isset($input->challengeID)) {
            $challenges = new BIM_App_Challenges();
            $uv = $challenges->cancelChallenge( $input->challengeID );
        }
        if( $uv ){
            return array(
                'id' => $uv->id
            );
        }
    }

    public function acceptChallenge(){
        $uv = null;
        $input = (object) ($_POST ? $_POST : $_GET);
        if (isset( $input->userID) && isset($input->challengeID) && isset($input->imgURL)) {
            $input->imgURL = $this->normalizeVolleyImgUrl($input->imgURL);
            $input->userID  = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            $uv = $challenges->acceptChallenge( $input->userID, $input->challengeID, $input->imgURL );
        }
        if( $uv ){
            return array(
                'id' => $uv->id
            );
        }
    }

    public function join(){
        $joinedVolley = null;
        $input = (object) ($_POST ? $_POST : $_GET);

        // this conditional is checking to see if we are receiving an
        // upload directly from the client as is the case with
        // the kik selfieclub messaging app
        if( !empty($input->imgData[0]) && empty($input->imgURL) ){
            $input->imgURL = BIM_Utils::processBase64Upload($input->imgData[0]);
        }

        if (!empty( $input->userID) && !empty($input->challengeID) && !empty($input->imgURL)) {
            $input->imgURL = $this->normalizeVolleyImgUrl($input->imgURL);
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            // get the response hashtag
            $hashTag = empty( $input->subject ) ? '' : $input->subject;
            $volley = BIM_Model_Volley::get( $input->challengeID );
            if( $volley->isExtant() ){
                $ptrn = "@$volley->subject\s*:@";
                $hashTag = trim(preg_replace( $ptrn, '', $hashTag, 1 ));
                $joinedVolley = $challenges->join( $userId, $input->challengeID, $input->imgURL, $hashTag );
                if( $joinedVolley ){
                    $joinedVolley = array(
                        'id' => $joinedVolley->id,
                        'img' => $input->imgURL
                    );
                }
            }
        }
        return $joinedVolley;
    }

    // TODO: Looks like this createprivate() is no longer in use.  Verify, then remove.
    public function createprivate(){
        return $this->submitChallengeWithUsernames();
    }

    public function create(){
        $response = $this->submitChallengeWithUsernames();

        // Clean out response
        self::_stripProperties( $response, array("comments", "viewed", "has_viewed", "started", "expires", "is_private",
                "total_likers", "recent_likes", "is_explore", "is_celeb") );
        self::_stripProperties( $response->creator, array( "age" ) );
        foreach ( $response->challengers as $challenger ) {
            self::_stripProperties( $challenger, array( "age", "joined_timestamp" ) );
        }

        return $response;
    }

    private static function _stripProperties( $object, $properties ) {
        foreach ( $properties as $property ) {
            if ( property_exists($object, $property) ) {
                unset( $object->$property );
            }
        }
    }

    /**
    imgURL = "https://hotornot-challenges.s3.amazonaws.com/54a0704e221c46c5b53b2ba3053f957f-27cfad206b4d4cf98a1aab...";
    isPrivate = Y;
    subject = "#catWhiskers";
    targets = 2394;
    userID = 55059;
     *
     * Enter description here ...
     */
    public function submitChallengeWithUsernames(){
        $uv = null;
        $input = (object) ($_POST ? $_POST : $_GET);
        if (isset($input->userID) && isset($input->subject) && isset($input->imgURL) ){
            $isPrivate = !empty( $input->isPrivate ) ? true : false ;
            if( !$isPrivate || ( $isPrivate  && !empty( $input->targets ) ) ){
                $targets = ( $isPrivate  && !empty( $input->targets ) )
                            ? array_unique(explode(',', $input->targets))
                            : array();
                $clubId = (!empty( $input->clubID ) && $input->clubID > 0)
                            ? $input->clubID
                            : 0;
                $input->imgURL = $this->normalizeVolleyImgUrl($input->imgURL);
                $userId = $this->resolveUserId( $input->userID );
                $expires = $this->resolveExpires();
                $challenges = new BIM_App_Challenges();

                // subject*s*, for backwards compatability
                $subjects = property_exists($input, "subjects")
                    ? $input->subjects : "";

                $uv = $challenges->submitChallengeWithUsername( $userId, $input->subject, $input->imgURL, $isPrivate,
                        $expires, $targets, $clubId, $subjects );
            }
        }

        return $uv;
    }

    public function submitChallengeWithUsername(){
        $input = (object) ($_POST ? $_POST : $_GET);
        $uv = null;
        if (isset($input->userID) && isset($input->subject) && isset($input->imgURL) ){
            $input->imgURL = $this->normalizeVolleyImgUrl($input->imgURL);
            $userId = $this->resolveUserId( $input->userID );
            $isPrivate = !empty( $input->isPrivate ) ? $input->isPrivate : 'N' ;
            $expires = $this->resolveExpires();
            $challenges = new BIM_App_Challenges();
            $uv = $challenges->submitChallengeWithUsername( $userId, $input->subject, $input->imgURL, $isPrivate, $expires  );
        }
        return $uv;
    }

    public function get(){
        $input = (object) ($_POST ? $_POST : $_GET);
        $challenge = array();
        if( !empty( $input->challengeID ) ){
            $challenge = BIM_Model_Volley::get( $input->challengeID );
        }
        return $challenge;
    }

    public function getSelfies(){
        $challenges = new BIM_App_Challenges();
        $ids = array();
        if( !empty( $_COOKIE['selfies_seen'] ) ){
            $ids = trim( $_COOKIE['selfies_seen'] );
            $ids = explode(',', $ids);
        }
        return $challenges->getSelfies( $ids );
    }

    /**
     * returns a list of verifyme volleys
     */
    public function getVerifyList(){
        $input = (object) ($_POST ? $_POST : $_GET);
        $verifyList = array();
        if( isset( $input->userID ) ){
            $userId = $this->resolveUserId( $input->userID );
            $challenges = new BIM_App_Challenges();
            $verifyList = $challenges->getVerifyList( $userId );
        }
        return $verifyList;
    }

    public function missingImage(){
        $input = (object) ($_POST ? $_POST : $_GET);
        $fixed = false;
        if( isset( $input->imgURL ) ){
            $challenges = new BIM_App_Challenges();
            $fixed = $challenges->missingImage( $input->imgURL );
        }
        return $fixed;
    }

    public function processImage(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->imgURL ) ){
            BIM_Jobs_Challenges::queueProcessImage( $input->imgURL);
        }
        return true;
    }

    public function deleteImage(){
        $input = (object) ($_POST ? $_POST : $_GET);
        $user = BIM_Utils::getSessionUser();
        if( $user && $user->isExtant() && !empty( $input->imgURL ) && !empty( $input->challengeID ) ){
            $volley = BIM_Model_Volley::get( $input->challengeID );
            if( $volley->isExtant() ){
                if( $volley->isCreator($user->id) ){
                    BIM_Model_Volley::deleteVolleys( array( $volley->id ), $user->id );
                } else {
                    $volley->deleteImageByUserIdAndImage( $user->id, $input->imgURL );
                }
                $volley->purgeFromCache();
                $user->purgeFromCache();
            }
        }
        return true;
    }

    public function shoutout(){
        $volley = null;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->challengeID ) && !empty( $input->userID ) ){
            $userId = $this->resolveUserId( $input->userID );
            $volley = BIM_Model_Volley::makeShoutoutVolley($input->challengeID, $userId);
        }
        return $volley;
    }

    public function selfieshoutout(){
        $volley = null;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->userID ) && !empty( $input->targetID ) ){
            try{
                $volley = BIM_Model_Volley::shoutoutVerifyVolley($input->userID, $input->targetID);
            } catch( ImagickException $e ){
                error_log($e->getMessage().' FILE:'.$e->getFile().' LINE:'.$e->getLine());
                $volley = BIM_Model_Volley::getVerifyVolley( $input->targetID );
            }
        }
        return $volley;
    }

    public function kikreply(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->userID ) && !empty( $input->targetID ) ){
            $verifyVolley = BIM_Model_Volley::getVerifyVolley( $input->targetID );
            if( $verifyVolley->isExtant() ){
                $var = 'challengeID';
                $value = $verifyVolley->id;
                if( $_POST ){
                    $_POST[$var] = $value;
                } else {
                    $_GET[$var] = $value;
                }
                return $this->join();
            }
        }
    }
}
