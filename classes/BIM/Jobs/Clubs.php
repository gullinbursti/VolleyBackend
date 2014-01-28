<?php 

class BIM_Jobs_Clubs extends BIM_Jobs{
    
    /*
     * SUBMIT MATCHING CHALLENGE JOBS
     */
    public static function queueNotifyInvitees( $clubName, $users, $ownerId ){
        $users = json_encode( $users );
        $job = array(
        	'class' => __CLASS__,
        	'method' => 'notifyInvitees',
        	'data' => array( 'ownerId' => $ownerId, 'clubName' => $clubName, 'users' => $users ),
        );
        return self::queueBackground( $job, __CLASS__ );
    }
	
    public function notifyInvitees( $workload ){
        $data = json_decode($workload->data->users);
        if( $data ){
            BIM_App_Clubs::notifyInvitees( $data->clubName, $data->users, $data->ownerId );
        }
    }
}
