<?php 

class BIM_Controller{
    
    public $user = null;
    
    public function handleReq(){
        BIM_Utils::startProfiling();
                
        $res = null;
        if( $this->sessionOK() ){
            $request = BIM_Utils::getRequest();
            $method = $request->method;
            $controllerClass = $request->controllerClass;
            $r = new $controllerClass();
            if( $method && method_exists( $r, $method ) ){
                $res = $this->getAccountSuspendedVolley();
                if( !$res || $controllerClass == 'BIM_Controller_Users' ){
                    $r->user = BIM_Utils::getSessionUser();
                    if( $r->user ){
                        $method = self::resolveGetChallengesWithFriends($method);
                    }
                    $res = $r->$method();
                    if( is_bool( $res ) ){
                        $res = array( 'result' => $res );
                    }
                }
            }
        }
        BIM_Utils::endProfiling();
        $this->sendResponse( 200, $res );
    }
    
    public static function resolveGetChallengesWithFriends( $method ){
        $cache = new BIM_Cache( BIM_Config::cache() );
        $user = BIM_Utils::getSessionUser();
        $key = "last_call_$user->id";
        $method = strtolower($method);
        
        if( $method == 'submitnewuser' ){
            $cache->set( $key, $method, 5 );
        } else if( $method == 'getsubscribees' ){
            $lastMethod = $cache->get( $key );
            if( $lastMethod != 'submitnewuser' ){
                $cache->set( $key, $method, 5 );
            }
        } else if( $method == 'getchallengeswithfriends' ){
            $lastMethod = $cache->get( $key );
            if( $lastMethod == 'getsubscribees' ){
                $method = 'getchallengesforusername';
                if( $_POST ){
                    $data = &$_POST;
                } else if( $_GET ){
                    $data = &$_GET;
                }
                $data['username'] = $user->username;
                $data['p'] = 1;
            }
            $cache->delete( $key );
        }
        return $method;
    }
    
    // returns an empty array or
    // a verify volley if the user is suspended
    public function getAccountSuspendedVolley(){
        $volley = null;
        $input = (object) ($_POST ? $_POST : $_GET);
        $user = BIM_Utils::getSessionUser();
        if( !$user && !empty( $input->userID ) ){
            $user = BIM_Model_User::get( $input->userID );
        }
        if( $user && $user->isSuspended() ){
            $volley =  array( BIM_Model_Volley::getAccountSuspendedVolley($user->id) );
        }
        return $volley;
    }
        
	public function getStatusCodeMessage($status) {			
		$codes = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => '(Unused)',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported');

		return ((isset($codes[$status])) ? $codes[$status] : '');
	}
			
	public function sendResponse($status=200, $body=null, $content_type='application/json') {			
		$status_header = "HTTP/1.1 ". $status ." ". $this->getStatusCodeMessage($status);
		
		header($status_header);
		header("Content-type: ". $content_type);
		
		echo isset($body->data) ? json_encode( $body->data ) : json_encode( $body );
	}
	
	/*
	 * session is ok if 
	 * 		we are calling the function to create a new user
	 * 		OR we have turned off sessions
	 * 		OR we find a valid session user 
	 */
	protected function sessionOK(){
	    $OK = true;
        $sessionConf = BIM_Config::session();
	    if( !empty( $sessionConf->use ) ){
            $OK = false;	        
            $request = BIM_Utils::getRequest();
            
            $createUser = (strtolower( $request->controllerClass ) == 'bim_controller_users') 
                            && ( strtolower( $request->method ) == 'create' );
                            
            $newUser = (strtolower( $request->controllerClass ) == 'bim_controller_users') 
                            && ( strtolower( $request->method ) == 'submitnewuser' );

            $getSelfies = (strtolower( $request->controllerClass ) == 'bim_controller_challenges') 
                            && ( strtolower( $request->method ) == 'getselfies' );

            $twilio = (strtolower( $request->controllerClass ) == 'bim_controller_users') 
                            && ( strtolower( $request->method ) == 'twiliocallback' );
                            
            if( $createUser || $twilio || $getSelfies || $newUser || BIM_Utils::getSessionUser(true) ){
                $OK = true;
            }
	    }
        return $OK;
	}

}