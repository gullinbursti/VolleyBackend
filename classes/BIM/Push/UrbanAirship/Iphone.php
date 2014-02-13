<?php

/**
 * we need a way to throttle pushes 
 * this is how:
 * 
 *  we receive the push
 *  
 *  get the token
 *  
 *  get the push throttle object from cache or the db using the token
 *  
 *  check the number of pushes for this type
 *  
 *  if we are below the threshold for this type of push
 *  and the next allowable time is passed or DNE
 *  	then we just send the push normally
 *  
 *  if we are above the threshold:
 *  	then we check to see if this type needs to be batched
 *  
 *  if the type needs to be batched:
 *  	then we do not send the push
 *  	create a timed push job
 *  	mark the type of push as having been batched
 *  	and set the next allowable push time to 11 am PST
 *  
 *	a timedPush job that will:
 *  		take all the events for this push type
 *  		generate a message for the type
 *  		send the push
 *  
 */

class BIM_Push_UrbanAirship_Iphone{
    
    public static function sendPushBatch( $push ){
        $conf = BIM_Config::urbanAirship();
        $pushStr = json_encode($push);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $conf->api->push_url);
        curl_setopt($ch, CURLOPT_USERPWD, $conf->api->pass_key );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $pushStr);
        $res = curl_exec($ch);
        $err_no = curl_errno($ch);
        $err_msg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);
    }
    
    public static function sendPush( $push ){
        $conf = BIM_Config::urbanAirship();
        if( !is_object($push->device_tokens) && !is_array($push->device_tokens) ){
            $push->device_tokens = array( $push->device_tokens );
        }
        $tokens = $push->device_tokens;
        foreach( $tokens as $token ){
            $push->device_tokens = array( $token );
            $pushStr = json_encode($push);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $conf->api->push_url);
            curl_setopt($ch, CURLOPT_USERPWD, $conf->api->pass_key );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $pushStr);
            $res = curl_exec($ch);
            $err_no = curl_errno($ch);
            $err_msg = curl_error($ch);
            $header = curl_getinfo($ch);
            curl_close($ch);
        }
    }
}