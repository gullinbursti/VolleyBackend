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
            //'DateSent<' => '2013-11-27 00:41:00',
            //'From' => '+17075551234', // **Optional** filter by 'From'...
            //'To' => '+18085559876', // ...or by 'To'
        ));
        
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
                //$client->account->sms_messages->create( $from, $to, $msg );
            } catch ( Exception $e ){
                print_r( $e );
            }
        }
    }
    
    public static function getMobileNumbers( ){
        
        $es = new BIM_DAO_ElasticSearch( BIM_Config::elasticSearch()  );
        $query = array(
            "from" => 0,
            "size" => 20000,
        );
        $urlSuffix = "contact_lists_bkp/phone/_search";
        $lists = json_decode( $es->call('POST', $urlSuffix, $query) );
        
        $caliCodes = array(209,213,310,323,408,415,424,442,510,530,559,562,619,626,650,657,661,707,714,747,760,805,818,831,858,909,916,925,949,951,905,204,514,506,709,226,519,613,705,819,306,204,289,226,250,289,306,403,416,418,450,506,514,519,587,604,613,647,705,709,778,780,807,819,867,902,905);
        //$caliCodes = array( 209,213,310,323,408,415,424,442,510,530,559,562,619,626,650,657,661,707,714,747,760,805,818,831,858,909,916,925,949,951);
        $count = 0;
        
        foreach( $lists->hits->hits as $hit ){
            if( !empty( $hit->_source->hashed_number ) ){
                $number = $hit->_source->hashed_number;
                $rawNumber = preg_replace('@\+1@','',$number);
                $areaCode = substr($rawNumber, 0, 3);
                if( !in_array( $areaCode, $caliCodes ) && strlen( $rawNumber ) == 10 ){
                    $count++;
                    echo "$number\n";
                }
            }
            if( !empty( $hit->_source->hashed_list ) ){
                foreach( $hit->_source->hashed_list as $number  ){
                    $rawNumber = preg_replace('@\+1@','',$number);
                    $areaCode = substr($rawNumber, 0, 3);
                    if( !in_array( $areaCode, $caliCodes ) && strlen( $rawNumber ) == 10 ){
                        $count++;
                        echo "$number\n";
                    }
                }
            }
        }
        echo $count."\n";
    }
    
    public static function getHashedMobileNumbers( ){
        
        $es = new BIM_DAO_ElasticSearch( BIM_Config::elasticSearch()  );
        // getting data fior ids 64514- 81259
        $query = json_decode(
            '
            {
              "from": 0,
              "size": 20000,
              "sort": [
                {
                  "id": {
                    "order": "desc"
                  }
                },
                "_score"
              ],
              "query": {
                "range": {
                  "id": {
                    "gte": 0
                  }
                }
              }
            }        
            '
        );
        
        $urlSuffix = "contact_lists/phone/_search";
        $lists = json_decode( $es->call('POST', $urlSuffix, $query) );

        $caliCodes = array( 209,213,310,323,408,415,424,442,510,530,559,562,619,626,650,657,661,707,714,747,760,805,818,831,858,909,916,925,949,951);
        $count = 0;
        
        foreach( $lists->hits->hits as $hit ){
            if( !empty( $hit->_source->hashed_number ) ){
                $number = $hit->_source->hashed_number;
                
                $number = trim(BIM_Utils::blowfishDecrypt($number));
                
                $rawNumber = preg_replace('@\+1@','',$number);
                //error_log( $rawNumber );
                //error_log( strlen( $rawNumber )  );
                
                $areaCode = substr($rawNumber, 0, 3);
                if( !in_array( $areaCode, $caliCodes ) && strlen( $rawNumber ) == 10 ){
                    $count++;
                    echo "$number\n";
                }
                
            }
            if( !empty( $hit->_source->hashed_list ) ){
                foreach( $hit->_source->hashed_list as $number  ){
                    $number = trim(BIM_Utils::blowfishDecrypt($number));
                    $rawNumber = preg_replace('@\+1@','',$number);
                    //error_log( $rawNumber );
                    //error_log( strlen( $rawNumber )  );
                    $areaCode = substr($rawNumber, 0, 3);
                    if( !in_array( $areaCode, $caliCodes ) && strlen( $rawNumber ) == 10 ){
                        $count++;
                        echo "$number\n";
                    }
                }
            }
        }
        echo $count."\n";
    }
    
    public static function sendMarketingBlast( $filename ){
        $self = new BIM_Growth();
        $client = $self->getTwilioClient();
        $conf = BIM_Config::twilio();
        
        $from = '2394313268';// $conf->api->number;
        $msg = 
'
A friend has invited you to Selfieclub (#1 Selfie App) Install to find out who! AppStore.com/Selfieclub
		';
        if( !$msg ){
            error_log("no msg!");
            exit;
        }
        $numbers = file( $filename );
        foreach( $numbers as &$number ){
            $number = trim( $number );
        }
	    array_unshift( $numbers, '+12133009127','+14152549391' );

        foreach( $numbers as $number ){
            try{
                $client->account->sms_messages->create( $from, $number, $msg );
                echo "$number\n";
            } catch( Exception $e ){
                echo $e->getMessage()."\n";
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
