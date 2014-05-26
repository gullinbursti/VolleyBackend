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
        if( $club->isOwner($ownerId) ){
            $invited = $club->invite( $users, $nonUsers );
            if( $invited ){
                self::notifyInvitees($clubId, $users, $nonUsers);
                //BIM_Jobs_Clubs::queueNotifyInvitees($clubId, $users, $nonUsers);
            }
        }
        return $invited;
    }

    public static function notifyInvitees( $clubId, $users, $nonUsers ) {
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
    }

    public static function smsInvites( $numbers, $clubId ){
        if( !is_array( $numbers ) ){
            $numbers = array( $numbers );
        }

        $club = BIM_Model_Club::get( $clubId );

        foreach( $numbers as $number ){
            $client = BIM_Utils::getTwilioClient();
            $conf = BIM_Config::twilio();
            $number = preg_replace('@[^\d]@', '', $number);
            $number = preg_replace('@^1@', '', $number);
            $number = "+1$number";
            $msg = BIM_Config::clubSmsInviteMsg();
            $msg = str_replace('[CLUBNAME]',$club->name, $msg);
            $msg = str_replace('[USERNAME]',$club->owner->username, $msg);
            $sms = $client->account->sms_messages->create( $conf->api->number, $number, $msg );
        }
    }

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
        }
        return $joined;
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
