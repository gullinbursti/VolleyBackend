<?php

class BIM_Controller_Myapp extends BIM_Controller_Base {
    
    public function iRock(){
        /*
         we use the below convention to get the input.
         this means bot POST and GET will both work.
         */
        $input = (object) ($_POST ? $_POST : $_GET);
        
        // ensure we have passed the userID
        // this is just an example
        // you can make whatever requirements 
        // you need in application code
		if (!empty($input->userID)){
		    
		    // now we resolve the userID that was passed, again optional
		    // but you will be using this idiom ALOT
		    // what this does is make sure that the passed userID is set to the users ID
		    // this is used to protect a call from being hacked
		    // and it provides ease of debugging and developing 
		    // because we can disable the sessions
		    // and they will allow us to "hack" the api and test different users 
            $input->userID = $this->resolveUserId( $input->userID );
            
            // this is another standard where we call
            // the similarly named, associated and correlated method on an BIM_App_* class
            // this allows for clean sepaartion between the input layer and the ap layer
            // which makes it very easy to call BIM_App_* methods 
            // from worker scripts and command line
            // this also leads to good reuse and maintainability
		    $radApp = new BIM_App_Myapp();
		    
		    // return the value from the BIM_App_* call
		    // and it will be jsonified and returned to the caller
		    return $radApp->iRock( $input->userID );
		    
		}
		return null;
    }
}

