<?php

/**
POST /zerver/API/getFirstHeyHeys HTTP/1.1
Host: kik.heyhey.us
Content-Length: 11
Origin: http://kik.heyhey.us
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36
Content-Type: text/plain;charset=UTF-8
Referer: http://kik.heyhey.us/
Accept-Encoding: gzip,deflate,sdch
Accept-Language: en-US,en;q=0.8
 *
 */
class BIM_Growth_Kik extends BIM_Growth{
    public static function getHeyHeys(){
        $self = new self();
        $url = "http://kik.heyhey.us/zerver/API/getFirstHeyHeys";
        $args = array('{"args":[]}');
        
        $headers = array(
            'Content-Type: text/plain;charset=UTF-8',
            'Referer: http://kik.heyhey.us/',
            'Host: kik.heyhey.us',
            'Origin: http://kik.heyhey.us',
            'Accept-Encoding: gzip,deflate,sdch',
            'Accept-Language: en-US,en;q=0.8'
        );
        
        while( $url ){
            $data = json_decode( $self->post( $url, $args, false, $headers, true ) );
            if( !empty( $data->data[0]->shouts ) ){
                foreach( $data->data[0]->shouts as $shout ){
                    $dao = new BIM_DAO_Mysql_Growth_Kik( BIM_Config::db() );
                    $dao->saveKikUser($shout);
                }
                if( $data->data[0]->has_more_results ){
                    $lastShout = end($data->data[0]->shouts);
                    $timeParam = $lastShout->created_at;
                    $url = 'http://kik.heyhey.us/zerver/API/getMoreHeyHeys';
                    $args = array('{"args":["'.$lastShout->created_at.'"]}');
                    self::sleep(2,"getting $url next");
                } else {
                    echo "no more kik ids.  starting over!\n";
                    $url = "http://kik.heyhey.us/zerver/API/getFirstHeyHeys";
                    $args = array('{"args":[]}');
                }
            }
        }
    }
}
