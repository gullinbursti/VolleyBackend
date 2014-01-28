<?php
class BIM_App_Clubs extends BIM_App_Base{
    public static function create( $name, $users, $ownerId ) {
        $created = BIM_Model_Club::create( $name, $users, $ownerId  );
        if( $created ){
            // BIM_Model_Club::notifyInvitees( $name, $users, $ownerId );
        }
        return $created;
	}
}