<?php
class BIM_App_Clubs extends BIM_App_Base{
    public static function create( $name, $users, $ownerId, $description = '', $img = '' ) {
        $created = BIM_Model_Club::create( $name, $users, $ownerId, $description, $img  );
        if( $created ){
            //BIM_Jobs_Clubs::queueNotifyInvitees($name, $users, $ownerId);
        }
        return $created;
	}
	
    public static function notifyInvitees( $name, $users, $ownerId ) {
        $numbers = array();
        $emails = array();
        foreach( $users as $user ){
            if( !empty( $user[1] ) ){
                $numbers[] = $user[1];
            }
            
            if( !empty( $user[2] ) ){
                $emails[] = $user[2];
            }
        }
        
        self::smsInvites($numbers, $name, $ownerId);
        self::emailInvites($emails, $name, $ownerId);
	}
	
    public static function smsInvites( $numbers, $clubName, $ownerId ){
        if( !is_array( $numbers ) ){
            $numbers = array( $numbers );
        }
        
        $clubOwner = BIM_Model_User::get( $ownerId );
        
        foreach( $numbers as $number ){
            $client = BIM_Utils::getTwilioClient();
            $conf = BIM_Config::twilio();
            $number = preg_replace('/\.\s\-\+/', '', $number);
            $number = "+$number";
            $msg = BIM_Config_Dynamic::clubSmsInviteMsg();
            $msg = str_replace('[CLUBNAME]',$clubName, $msg);
            $msg = str_replace('[USERNAME]',$clubOwner->username, $msg);
            $sms = $client->account->sms_messages->create( $conf->api->number, $number, $msg );
        }
    }
    
    public static function emailInvites($addys, $clubName, $ownerId){
        $emailData = BIM_Config::clubEmailInvite();
        $e = new BIM_Email_Swift( BIM_Config::smtp() );
        
        $clubOwner = BIM_Model_User::get( $ownerId );
        
        foreach( $addys as $addy ){
            $emailData->to_email = $addy;
            $msg = $emailData->text;
            $msg = str_replace('[CLUBNAME]',$clubName, $msg);
            $msg = str_replace('[USERNAME]',$clubOwner->username, $msg);
            $e->sendEmail( $emailData );
        }
    }
}