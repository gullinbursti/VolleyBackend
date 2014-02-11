<?php 

class BIM_Jobs_Clubs extends BIM_Jobs{
    
    /*
     * SUBMIT MATCHING CHALLENGE JOBS
     */
    public static function queueNotifyInvitees( $clubId, $invited ){
        $users = json_encode( $users );
        $job = array(
        	'class' => __CLASS__,
        	'method' => 'notifyInvitees',
        	'data' => array( 'club_id' => $clubId, 'invited' => $invited ),
        );
        return self::queueBackground( $job, __CLASS__ );
    }
	
    public function notifyInvitees( $workload ){
        $data = json_decode($workload->data);
        if( $data ){
            BIM_App_Clubs::notifyInvitees( $data->club_id, $data->invited );
        }
    }
}
