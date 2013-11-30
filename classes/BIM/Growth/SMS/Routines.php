<?php

class BIM_Growth_SMS_Routines extends BIM_Growth_SMS{
    
    public function __construct( $persona ){
        $this->persona = $persona;
    }
    
    public function smsInvites(){
        $numbers = explode('|', $this->persona->sms->numbers );
        foreach( $numbers as $number ){
            $this->sendSMSInvite( $number );
        }
    }
    
    public static function getLogs(){
        $self = new BIM_Growth();
        $client = $self->getTwilioClient();
        
        $messages = $client->account->sms_messages->getIterator(0, 1000, array(
            'DateSent>' => '2013-11-26', // Wed, 27 Nov 2013 02:29:25 +0000
            'DateSent<' => '2013-11-27 00:41:00',
            //'From' => '+17075551234', // **Optional** filter by 'From'...
            //'To' => '+18085559876', // ...or by 'To'
        ));
        
        // Write rows
        $conf = BIM_Config::twilio();
        $from = $conf->api->number;
        $msg = 'Selfieclub is LIVE on the App Store! GET THE APP NOW!!!! Get the app to #1!!http://taps.io/JZ5Q';
        foreach ($messages as $sms) {
            /*
            $row = (object) array(
                'sid' => $sms->sid, 'from' => $sms->from, 'to' => $sms->to, 'date_sent' => $sms->date_sent,
                'status' => $sms->status, 'direction' => $sms->direction, 'price' => $sms->price, 'body' => $sms->body
            );
            */
            $to = $sms->to;
            // $to = '+14152549391';
            echo "sending $from, $to, $msg\n";
            try{
                $client->account->sms_messages->create( $from, $to, $msg );
            } catch ( Exception $e ){
                print_r( $e );
            }
        }
    }    
    
    public function sendSMSInvite( $number ){
        $client = $this->getTwilioClient();
        $conf = BIM_Config::twilio();
        
        $number = preg_replace('/\.\s\-\+/', '', $number);
        $number = "+$number";
     
        $msg = $this->getTxtMsg();
        $user = new BIM_Model_User( $this->persona->sms->userId );
        $msg = preg_replace('@\[\[USERNAME\]\]@', $user->username, $msg);
        $sms = $client->account->sms_messages->create( $conf->api->number, $number, $msg );
    }
    
    public function getTxtMsg(){
        $msgs = BIM_Config::inviteMsgs();
        return !empty($msgs['sms']) ? $msgs['sms'] : '';
    }
    
    // we take the list of phone numbers and
    // return all matches to numbers in our db
    public function matchNumbers( $numbers ){
        $dao = new BIM_DAO_Mysql_Growth( BIM_Config::db() );
        return $dao->matchNumbers( $numbers );
    }
    
}
