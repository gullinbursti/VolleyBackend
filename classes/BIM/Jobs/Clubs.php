<?php 

class BIM_Jobs_Clubs extends BIM_Jobs{
    
    public static function queueNotifyInvitees( $clubId, $users, $nonUsers ){
        $job = array(
        	'class' => __CLASS__,
        	'method' => 'notifyInvitees',
        	'data' => array( 
        		'club_id' => $clubId, 
        		'invited' => array(
					'users' => $users, 
					'nonUsers' => $nonUsers 
                 )
             ),
        );
        return self::queueBackground( $job, 'club_invites' );
    }
	
    public function notifyInvitees( $workload ){
        if( $workload->data ){
            BIM_App_Clubs::notifyInvitees( $workload->data->club_id, $workload->data->invited->users, $workload->data->invited->nonUsers );
        }
    }
}
