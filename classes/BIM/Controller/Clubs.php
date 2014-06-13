<?php
/*
    Each user will have ability to create one club per profile (for security & admin reasons only one per user will be allowed)
    Each club under that user will have the following data around the club
        name
        caption (160 characters)
        avatar
        join list (list of people who have joined)
        invite list (list of people pending to join from SMS/Email)
        block list (list of people who have been blocked by the club owner)

    Each user that joins a club will begin to receive Selfies that are sent
    directly into that club. Joining a club is a little like following a user,
    except targeting a club with an Selfie doesn't show up to all your followers
    just the members of that club.
    Followers == primary; Clubs == secondary (like sub-reddits)

    Users can join as many clubs as they want but may only own a single club per profile.

    Submitting Selfies into a club will be similar to sending a direct message but
    instead of replying to X number of people you will be selecting the club during
    the last camera step.

    Note that unlike direct messages the submitted selfie and emotion will be sent
    to all the people who are a part of that specific club.
    The selfie and emotion will appear as a new selfie on their main home timeline.
    You will be able to reply to and or like club selfies that appear in your feed.

    Calls/functionality we will need...
    creating a new club
    editing a new club's details (name, caption, avatar)
    editing a old club's details (name, caption, avatar)
    joining a new club
    removing a club I have joined
    viewing all of the members from my club
    viewing all of the invited (pending) members from my club
    viewing all of blocked members from my club
    removing members from my club
    viewing featured clubs (driven by config file + club ID)
    Note we will be using TapStreams deeplinking URLs to direct invited users to the club's landing page where the user will be able to join.
 *
 */
class BIM_Controller_Clubs extends BIM_Controller_Base {

    public function create(){
        $club = false;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->userID ) && !empty( $input->name ) ){
            $description = !empty( $input->description ) ? $input->description : '';
            $img = !empty( $input->imgURL ) ? $input->imgURL : '';
            $img = $this->normalizeVolleyImgUrl($img);
            $input->userID = $this->resolveUserId( $input->userID );
            $clubType = !empty( $input->clubType ) ? $input->clubType : 'USER_GENERATED';
            $club = BIM_App_Clubs::create($input->name, $input->userID, $description, $img, $clubType);
        }
        return $club;
    }

    public function invite(){
        $invited = false;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->userID ) && !empty( $input->clubID ) ){
            $inviterId = $this->resolveUserId($input->userID);
            $nonUsers = !empty( $input->nonUsers ) ? self::extractUsers( $input->nonUsers ) : array();
            $users = !empty( $input->users ) ? explode(',', $input->users ) : array();
            $invited = BIM_App_Clubs::invite( $input->clubID, $inviterId, $users, $nonUsers );
        }
        return $invited;
    }

    private static function extractUsers( $users ){
        $users = explode( '|||', $users );
        $toRemove = array();
        foreach( $users as $idx => &$user ){
            $user = explode( ':::', $user );
            if( count( $user ) <= 1 ){
                $toRemove[] = $idx;
            } else if( count( $user ) == 2 ){
                $user[] = '';
            }
        }
        foreach( $toRemove as $idx ){
            unset( $users[ $idx ] );
        }
        return $users;
    }

    public function get(){
        $club = null;
        $input = (object) ($_POST ? $_POST : $_GET);

        if( empty( $input->clubID ) ) {
            # TODO: Add propper logging!
            error_log("BIM_Controller_Clubs->get(): clubID must be provided");
            return null;
        }

        if ( empty( $input->userID ) ){
            # TODO: Add propper logging!
            error_log("BIM_Controller_Clubs->get(): userID must be provided");
            return null;
        }

        $club = BIM_Model_Club::get( $input->clubID );
        $input->userID = $this->resolveUserId( $input->userID );
        if( $club->isExtant() && !$club->isOwner($input->userID) ){
            unset( $club->members );
            unset( $club->pending );
            unset( $club->blocked );
        }

        return $club;
    }

    private static function stripProperty( $property, $objects ) {
        foreach ( $objects as $object ) {
            if (property_exists($object, $property)) {
                unset($object->$property);
            }
        }
    }

    public function join(){
        $joined = false;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->userID ) && !empty( $input->ownerID ) && !empty( $input->clubID ) ){
            $club = BIM_Model_Club::get( $input->clubID );
            // now we make sure that the requesting user is
            // the same as the user that is joining
            // or the requesting user is the owner
            $requestingUserId = $this->resolveUserId( $input->ownerID );
            if( $club->isExtant() && ( $club->isOwner( $requestingUserId ) || $requestingUserId == $input->userID ) ){
                $joined = BIM_App_Clubs::join($input->clubID, $input->userID);
            }
        }
        return $joined;
    }

    //remove user from a club
    public function quit(){
        $quit = false;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->memberID ) && !empty( $input->clubID ) && !empty( $input->ownerID ) ){
            $club = BIM_Model_Club::get( $input->clubID );
            // now we make sure that the requesting user is
            // the same as the user that is quitting
            // or the requesting user is the owner
            $requestingUserId = $this->resolveUserId( $input->ownerID );
            if( $club->isExtant() && ( $club->isOwner( $requestingUserId ) || $requestingUserId == $input->memberID ) ){
                $quit = BIM_App_Clubs::quit($input->clubID, $input->memberID);
            }
        }
        return $quit;
    }

    //block a user from a club
    public function block(){
        $blocked = false;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->userID ) && !empty( $input->clubID ) && !empty( $input->ownerID ) ){
            $input->ownerID = $this->resolveUserId( $input->ownerID );
            $blocked = BIM_App_Clubs::block($input->clubID, $input->ownerID, $input->userID);
        }
        return $blocked;
    }

    //unblock a user from a club
    public function unblock(){
        $unblocked = false;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->userID ) && !empty( $input->clubID ) && !empty( $input->ownerID ) ){
            $input->ownerID = $this->resolveUserId( $input->ownerID );
            $unblocked = BIM_App_Clubs::unblock($input->clubID, $input->ownerID, $input->userID);
        }
        return $unblocked;
    }

    // get featured clubs
    public function featured(){
        return BIM_App_Clubs::featured();
    }

    public function processImage(){
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->imgURL ) ){
            BIM_Jobs_Challenges::queueProcessImage( $input->imgURL);
        }
        return true;
    }

}
