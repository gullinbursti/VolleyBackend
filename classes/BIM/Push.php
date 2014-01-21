<?php 
class BIM_Push{
    
    public static function queuePush( $push ){
        $job = array(
        	'class' => 'BIM_Push',
        	'method' => 'sendQueuedPush',
        	'push' => $push
        );
        //BIM_Push_UrbanAirship_Iphone::sendPush( $push );
        return BIM_Jobs::queueBackground( $job, 'push' );
    }
    
    public function sendQueuedPush( $workload ){
        BIM_Push_UrbanAirship_Iphone::sendPush( $workload->push );
    }
    
    public static function createTimedPush( $time, $tokens, $msg, $jobId = null, $pushType = null, $volleyId = null, $userId = null, $disabled = 0 ){
        $time = new DateTime("@$time");
        $time = $time->format('Y-m-d H:i:s');
        
        $params = (object) array(
            'tokens' => $tokens, 
            'msg' => $msg, 
            'type' =>  $pushType, 
            'volley_id' => $volleyId,
            'user_id' =>  $userId
        );
        
        $job = (object) array(
            'nextRunTime' => $time,
            'class' => 'BIM_Push',
            'method' => 'sendTimedPush',
            'name' => 'push',
            'params' => $params,
            'is_temp' => true,
            'disabled' => $disabled
        );
        
        if( !empty( $jobId ) ){
            // create an id that we can use to remove and cancel the job later
            $job->id = $jobId;
        }
        
        $j = new BIM_Jobs_Gearman();
        $j->createJbb($job);
    }
    
    public function sendTimedPush( $workload ){
        $push = json_decode($workload->params);
        $tokens = $push->tokens ? $push->tokens : array();
        $msg = $push->msg ? $push->msg : '';
        $type = !is_null( $push->type ) ? $push->type : null;
        $volleyId = !is_null( $push->volley_id ) ? $push->volley_id : null;
        $userId = !is_null( $push->user_id ) ? $push->user_id : null;
        BIM_Push::send( $tokens, $msg, $type, $volleyId, $userId );
    }
    
    public static function send( $ids, $msg, $type = null, $volleyId = null, $userId = null, $queue = true ){
        if( !is_array($ids) ){
            $ids = array( $ids );
        }
        $push = (object) array(
            'device_tokens' => $ids,
            "aps" => array(
                "alert" => $msg,
                "sound" => "push_01.caf"
            )
        );
        
        if( $userId !== null ){
            $push->user = $userId;
        }
        
        if( $volleyId !== null ){
            $push->challenge = $volleyId;
        }
        
        if( $type !== null ){
            $push->type = $type;
        }
        if( $queue ){
            self::queuePush($push); 
        } else {
            BIM_Push_UrbanAirship_Iphone::sendPush($push);
        }
    }
        
    /**
     * 
     * we push the shouters friends and the shoutees friends
     * 
     * @param int $shouterId
     * @param int $shouteeId
     * 
     */
    public static function shoutoutPush( $shouterId, $shouteeId, $volleyId ){
        $conf = BIM_Config::app();
        $shouter = BIM_Model_User::get($shouterId);
        $shoutee = BIM_Model_User::get($shouteeId);
        if( empty( $conf->team_volley_id  ) || $shouter->id != $conf->team_volley_id ){
            $shouterNsg = "Selfieclub: @$shouter->username gave a shoutout to @$shoutee->username!";
            $params = (object) array('userID' => $shouterId);
            $shouterFollowers = BIM_App_Social::getFollowers( $params );
            $ids = array();
            foreach( $shouterFollowers as $follower ){
                $ids[] = $follower->user->id;
            }
            $shouterFollowers = BIM_Model_User::getMulti( $ids );
            $type = 1;
            foreach( $shouterFollowers as $follower ){
                if( $follower->canPush() && !empty( $follower->device_token ) ){
                    self::send($follower->device_token, $shouterNsg, $type, $volleyId ); 
                }
            }
        }
        
        $shouteeNsg = "Yo! Your Selfie got a shoutout from @$shouter->username!";
        $params->userID = $shouteeId;
        $shouteeFollowers = BIM_App_Social::getFollowers($params);
        $ids = array();
        foreach( $shouteeFollowers as $follower ){
            $ids[] = $follower->user->id;
        }
        $shouteeFollowers = BIM_Model_User::getMulti( $ids );
        $type = 1;
        foreach( $shouteeFollowers as $follower ){
            if( $follower->canPush() && !empty( $follower->device_token ) ){
                self::send($follower->device_token, $shouterNsg, $type, $volleyId ); 
            }
        }
    }
    
    public static function shoutoutPushToAll( $userId, $volleyId ){
        $user = BIM_Model_User::get( $userId );
        if( $user->isExtant() ){
            $volley = BIM_Model_Volley::get( $volleyId );
            $tokens = BIM_Model_User::getAllPushTokens();
            foreach( $tokens as $token ){
                $token = trim($token);
                $msg = "**SELFIE SHOUTOUT** - $user->username";
                BIM_Push::send( $token, $msg, 1, $volley->id  );
            }
        }
    }    
    
    public static function pushCreators( $volleys ){
        if( !is_array($volleys)){
            $volleys = array( $volleys );
        }
        $creators = array();
        foreach ($volleys as $volley){
            $creators[] = $volley->creator->id;
        }
        $users = BIM_Model_User::getMulti($creators);
        $msg = "Your Selfie made it to the top of the Explore section! Tap or swipe to view.";
        foreach( $users as $user ){
            if( $user->canPush() && !empty( $user->device_token ) ){
                self::send($user->device_token, $msg ); 
            }
        }
    }
    
    public static function pokePush( $pokerId, $targetId ){
        $poker = BIM_Model_User::get($pokerId);
        $target = BIM_Model_User::get($targetId);
        $msg = "@$poker->username has poked you!";
        $type = 2;
        self::send($target->device_token, $msg, $type ); 
    }
    
	public static function sendFlaggedPush( $targetId ){
	    return true;
    	$target = BIM_Model_User::get( $targetId );
        if( $target->canPush() ){
            $msg = "Your Selfieclub profile has been flagged. Make sure you have a good selfie profile picture!";
            if( $target->isSuspended() ){
                $msg = "Your Selfieclub profile has been suspended!";
            }
            $type = 3;
            self::send($target->device_token, $msg, $type ); 
        }
	}
	
	public static function sendApprovePush( $targetId, $voterId ){
    	$target = BIM_Model_User::get( $targetId );
        if( $target->canPush() ){
            $voter = BIM_Model_User::get( $voterId );
            if( $target->isApproved() ){
            	$msg = "Your Selfieclub profile has been Verified by $voter->username";
            } else {
            	$msg = "Your Selfieclub profile has been Verified by $voter->username";
            }
            $type = 3;
            self::send($target->device_token, $msg, $type, null, $voter->id ); 
        }
	}
	/**
	 * 
	 * @param int $targetId usr bring flagged
	 * @param array[int] $userIds - list of users to push
	 */
	public static function sendFirstRunPush( $userIds, $targetId ){
	    
	    $userIds[] = $targetId;
        $users = BIM_Model_User::getMulti($userIds, true);
        $target = $users[ $targetId ];
        unset( $users[ $targetId ] );
        
        $deviceTokens = array();
        foreach( $users as $user ){
            if( $user->canPush() ){
                $deviceTokens[] = $user->device_token;
            }
        }
        
        if( $deviceTokens ){
            $msg = "A new user just joined Selfieclub, can you verify them? @$target->username";
            $type = 3;
            self::send($deviceTokens, $msg, $type ); 
        }
	}
	
    public static function emailVerifyPush( $userId ){
        $user = BIM_Model_User::get( $userId );
        if( $user->canPush() ){
            $msg = "Your Selfieclub account has been verified!";
            self::send( $user->device_token, $msg );
        }
    }
    
    public static function matchPush( $userId, $friendId ){
        $user = BIM_Model_User::get( $userId );
        $friend = BIM_Model_User::get( $friendId );
        if( $friend->canPush ){
            $msg = "Your friend $user->username joined Selfieclub!";
            self::send( $friend->device_token, $msg );
        }
    }
    
    public static function commentPush( $userId, $volleyId ){
        $volley = BIM_Model_Volley::get($volleyId);
        $commenter = BIM_Model_User::get($userId);
        $creator = BIM_Model_User::get( $volley->creator->id );

        $userIds = $volley->getUsers();
	    $users = BIM_Model_User::getMulti( $userIds );
	    
	    $deviceTokens = array();
	    foreach( $users as $user ){
	        $deviceTokens[] = $user->device_token;
	    }
        
		// send push if creator allows it
		if ($creator->canPush() && $creator->id != $userId){
            $msg = "$commenter->username has commented on your $volley->subject snap!";
		    $type = 3;
            self::send($creator->device_token, $msg, $type ); 
		}
    }
    
    public static function likePush( $likerId, $targetId, $volleyId ){
		$target = BIM_Model_User::get( $targetId );
		if( $target->canPush() ){
    	    $volley = BIM_Model_Volley::get($volleyId);
    		$liker = BIM_Model_User::get( $likerId );
    		$msg = "@$liker->username liked your Selfie $volley->subject";
    		if( $volley->subject == '#__verifyMe__' ){
    		    $msg = "Your profile selfie has been liked by @$liker->username";
    		}
    	    $type = 1;
            self::send($target->device_token, $msg, $type ); 
		}
    }
    
    public static function doVolleyAcceptNotification( $volleyId, $targetId ){
        $targetUser = BIM_Model_User::get($targetId);
        $volleyObject = BIM_Model_Volley::get($volleyId);
        
        $time = time() + 86400;
        $time = $time - ( $time % 86400 );
        $secondPushTime = $time + (3600 * 17);
        $thirdPushTime = $secondPushTime + (3600 * 9);
        
        $msg = "@$targetUser->username has replied to your Selfie: $volleyObject->subject";
        $pushType = 6;
        
        $users = BIM_Model_User::getMulti( $volleyObject->getUsers() );
        foreach( $users as $user ){
            if( $user->canPush() && ($targetUser->id != $user->id) ){
                self::send( $user->device_token, $msg, $pushType, $volleyObject->id );
                
                //$jobId = join( '_', array('v', $user->id, $volleyObject->id, uniqid(true) ) );
                //self::createTimedPush( $secondPushTime, $jobId, $user->device_token, $msg, $pushType, $volleyObject->id );
                
                //$jobId = join( '_', array('v', $user->id, $volleyObject->id, uniqid(true) ) );
                //self::createTimedPush( $thirdPushTime, $jobId, $user->device_token, $msg, $pushType, $volleyObject->id );
            }
        }
    }
    
    public static function sendVolleyNotifications( $volleyId, $targetIds = array() ){
        $volley = BIM_Model_Volley::get( $volleyId );
        $creator = BIM_Model_User::get($volley->creator->id);
        if( !$targetIds || !is_array($targetIds) ){
            $followers = BIM_App_Social::getFollowers( $creator->id, true );
            $targetIds = array_keys($followers);
        }
        $targets = BIM_Model_User::getMulti($targetIds);
        foreach( $targets as $target ){
            if ( $target->isExtant() && $target->canPush() ){
                $msg = "@$creator->username has just created the Selfieclub conversation: $volley->subject";
                $type = 1;
                self::send($target->device_token, $msg, $type, $volleyId ); 
            }
        }
    }
    
    public static function reVolleyPush( $volleyId, $challengerId ){
        $challenger = BIM_Model_User::get($challengerId);
        // send push if allowed
        if ( $challenger->canPush() ){
            $volley = BIM_Model_Volley::get($volleyId);
            $creator = BIM_Model_User::get($volley->creator->id);
            $msg = "@$creator->username has sent you a Selfie: $volley->subject";
            $type = 1;
            self::send($challenger->device_token, $msg, $type, $volley->id ); 
        }
    }
    
    public static function friendNotification( $userId, $friendId ){
        $friend = BIM_Model_User::get( $friendId );
        if( $friend->canPush() ){
            $user = BIM_Model_User::get( $userId );
            $msg = "@$user->username is following your Selfie updates!";
            $type = 3;
            self::send($friend->device_token, $msg, $type, null, $user->id ); 
        }
    }
    
    public static function friendAcceptedNotification( $userId, $friendId ){
        $friend = BIM_Model_User::get( $friendId );
        if( $friend->canPush() ){
            $user = BIM_Model_User::get( $userId );
            $msg = "$user->username accepted your friend request on Selfieclub!";
            self::send( $friend->device_token, $msg );
        }
    }
    
    public static function volleySignupVerificationPush( $userId ){
        $userIds = BIM_Model_User::getRandomIds( 50, array( $userId ) );
        $users = BIM_Model_User::getMulti($userIds);
        $deviceTokens = array();
        foreach( $users as $user ){
            if( $user->canPush() ){
                $deviceTokens[] = $user->device_token;
            }
        }
        $msg = "$user->username has joined Selfieclub and needs to be checked out";
        self::send($deviceTokens, $msg);
    }
    
    public static function introPush( $userId, $targetId, $pushTime ){
        $target = BIM_Model_User::get($targetId);
        if( $target->canPush() ){
            $user = BIM_Model_User::get($userId);
            $msg = "@$user->username has subscribed to your Selfie updates!";
            self::createTimedPush( $pushTime, null, $target->device_token, $msg, null );
        }
    }
    
    public static function selfieReminder( $userId ){
        $user = BIM_Model_User::get($userId);
        if( $user->canPush() ){
            $msg = "Selfieclub reminder! Please update your selfie to get verfied. No adults allowed!";
            self::send($user->device_token, $msg);
        }
    }
}
