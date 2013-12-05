<?php 

class BIM_Growth_Push{
    public static function sendThankYou($filename){
        $tokens = file( $filename );
        foreach( $tokens as $token ){
            $selfieCt = mt_rand( 500, 1000 );
            $msg = "You have $selfieCt new Selfies to Verify in Selfieclub!";
            $token = trim($token);
            BIM_Push::send( $token, $msg );
            error_log("sent $msg to $token");
        }        
    }
}
