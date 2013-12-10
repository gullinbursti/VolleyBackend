<?php 

class BIM_Growth_Push{
    public static function sendThankYou($filename){
        $tokens = file( $filename );
        foreach( $tokens as $token ){
            $selfieCt = mt_rand( 10,20 );
            $msg = "**SELFIE SHOUTOUT** - theJohnVo";
            $token = trim($token);
            BIM_Push::send( $token, $msg, null, null, null, false );
            error_log("sent $msg to $token");
        }
    }
}
