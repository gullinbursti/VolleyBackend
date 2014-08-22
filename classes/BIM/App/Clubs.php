<?php
class BIM_App_Clubs extends BIM_App_Base{

    public static function create( $name, $ownerId, $description = '', $img = '', $clubType = 'USER_GENERATED' ) {
        $club = null;
        $clubId = BIM_Model_Club::create( $name, $ownerId, $description, $img, $clubType );
        if( $clubId ){
            $club = BIM_Model_Club::get( $clubId );
        }
        return $club;
    }

    public static function invite( $clubId, $ownerId, $users = array(), $nonUsers = array() ) {
        $invited = false;
        $club = BIM_Model_Club::get( $clubId );

        foreach ($nonUsers as $user) {
            if ( count($user) != 3 ) {
                // Data missing from user
                return false;
            } else {
                if ( empty($user[0]) && (empty($user[1]) || empty($user[2])) ) {
                    // Name AND (phone OR email) required!!
                    return false;
                }
            }
        }

        if( $club->isOwner($ownerId) ){
            $invited = $club->invite( $users, $nonUsers );
            if( $invited ){
                self::postInvitationEvents($clubId, $ownerId, $users, $nonUsers);
                //self::notifyInvitees($clubId, $users, $nonUsers);
                //BIM_Jobs_Clubs::queueNotifyInvitees($clubId, $users, $nonUsers);
            }
        }
        return $invited;
    }

    public static function postInvitationEvents( $clubId, $actorMemberId, $invitees, $nonUsers ) {
        if ( $clubId && $actorMemberId && (count($invitees) >= 1 || count($nonUsers) >= 1)) {
            $eventDispatcher = new BIM_EventDispatcher_Club();
            if ( is_object($eventDispatcher) ) {
                if (count($invitees) >= 1) {
                    $dao = new BIM_DAO_Mysql_UserPhone( BIM_Config::db() );
                }
                foreach ( $invitees as $inviteeMemberId ) {
                    $memberPhoneObject = $dao->readExistingByUserId( $inviteeMemberId );
                    if ($memberPhoneObject) {
                        $memberSMS = BIM_Utils::blowfishDecrypt($memberPhoneObject->phone_number_enc);
                    } else {
                        $memberSMS = null;
                    }
                    $eventDispatcher->invitationToMember($clubId, $actorMemberId, $inviteeMemberId, $memberSMS);
                }
                foreach ( $nonUsers as $nonUser ) {
                    if ($nonUser[1]) {
                        $eventDispatcher->invitationToNonMember($clubId, $actorMemberId, $nonUser[1]);
                    }
                }
            }
        }
    }

    /* public static function notifyInvitees( $clubId, $users, $nonUsers ) {
        $numbers = array();
        $emails = array();
        foreach( $nonUsers as $user ){
            if( !empty( $user[1] ) ){
                $numbers[] = $user[1];
            }

            if( !empty( $user[2] ) ){
                $emails[] = $user[2];
            }
        }

        foreach( $users as $userId ){
            BIM_Push::clubInvite( $userId, $clubId );
        }

        self::smsInvites( $numbers, $clubId );
        self::emailInvites( $emails, $clubId );
    } */

    /* public static function smsInvites( $numbers, $clubId ){
        if( !is_array( $numbers ) ){
            $numbers = array( $numbers );
        }

        $club = BIM_Model_Club::get( $clubId );

        foreach( $numbers as $number ){
            // Clean up number
            $number = preg_replace('@[^\d]@', '', $number);
            $number = preg_replace('@^1@', '', $number);
            $number = "+1$number";

            // Setup message
            $msg = BIM_Config::clubSmsInviteMsg();
            $msg = str_replace('[CLUBNAME]',$club->name, $msg);
            $msg = str_replace('[USERNAME]',$club->owner->username, $msg);

            // Send
            $smsSender = new BIM_Integration_Nexmo_SmsSender( BIM_Config::nexmo() );
            $status = $smsSender->send( $number, $msg );
            if ( !$status ) {
                // TODO - Add some kind of error handling
            }
        }
    } */

    public static function emailInvites($addys, $clubId){
        $emailData = BIM_Config::clubEmailInvite();
        $e = new BIM_Email_Swift( BIM_Config::smtp() );

        $club = BIM_Model_Club::get( $clubId );

        foreach( $addys as $addy ){
            $emailData->to_email = $addy;
            $msg = $emailData->text;
            $msg = str_replace('[CLUBNAME]',$club->name, $msg);
            $msg = str_replace('[USERNAME]',$club->owner->username, $msg);
            $emailData->text = $msg;
            $e->sendEmail( $emailData );
        }
    }

    public static function join( $clubId, $userId ){
        $club = BIM_Model_Club::get( $clubId );
        $joined = false;
        if( $club->isExtant() ){
            $joined = $club->join( $userId );
            if( $joined ){
                self::postJoinEvent($clubId, $userId);
            }
        }
        return $joined;
    }

    public static function postJoinEvent( $clubId, $joinerId) {
        if ( $clubId && $joinerId) {
            $eventDispatcher = new BIM_EventDispatcher_Club();
            if ( is_object($eventDispatcher) ) {
                $eventDispatcher->memberJoined($clubId, $joinerId);
            }
        }
    }

    public static function quit( $clubId, $userId ){
        $club = BIM_Model_Club::get( $clubId );
        $quit = false;
        if( $club->isExtant() ){
            $quit = $club->quit( $userId );
        }
        return $quit;
    }

    public static function block( $clubId, $ownerId, $userId ){
        $club = BIM_Model_Club::get( $clubId );
        $blocked = false;
        if( $club->isExtant() && $club->isOwner( $ownerId ) ){
            $blocked = $club->block( $userId );
        }
        return $blocked;
    }

    public static function unblock( $clubId, $ownerId, $userId ){
        $club = BIM_Model_Club::get( $clubId );
        $unblocked = false;
        if( $club->isExtant() && $club->isOwner( $ownerId ) ){
            $unblocked = $club->unblock( $userId );
        }
        return $unblocked;
    }

    public static function featured( ){
        $featured = array();
        $c = BIM_Config::app();
        if( !empty( $c->featured_clubs ) ){
            $featured = BIM_Model_Club::getMulti( $c->featured_clubs );
        }
        return $featured;
    }
}
