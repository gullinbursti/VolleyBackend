<?php
class BIM_App_Clubs extends BIM_App_Base{
    public static function create( $name, $users, $ownerId ) {
        $created = BIM_Model_Club::create( $name, $users, $ownerId  );
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
        foreach( $numbers as $number ){
            $client = BIM_Utils::getTwilioClient();
            $conf = BIM_Config::twilio();
            $number = preg_replace('/\.\s\-\+/', '', $number);
            $number = "+$number";
            $msg = BIM_Config_Dynamic::clubSmsInviteMsg();
            $sms = $client->account->sms_messages->create( $conf->api->number, $number, $msg );
        }
    }
    
    public static function emailInvites($addys, $clubName, $ownerId){
        $emailData = BIM_Config::clubEmailInvite();
        $e = new BIM_Email_Swift( BIM_Config::smtp() );
        foreach( $addys as $addy ){
            $emailData->to_email = $addy;
            $emailData->text = 'foo goo foo goo';
            $e->sendEmail( $emailData );
        }
    }
}