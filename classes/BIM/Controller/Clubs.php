<?php
class BIM_Controller_Clubs extends BIM_Controller_Base {
    /**
     * name=<name>
     * users=name:::number:::email|||
     */
    public function create(){
        $reserved = false;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->userID ) && !empty( $input->name ) && !empty( $input->users ) ){
            $users = $this->extractUsers( $input->users );
            $input->userID = $this->resolveUserId( $input->userID );
            $reserved = BIM_App_Clubs::create($input->name, $users, $input->userID);
        }
        return $reserved;
    }
    
    private function extractUsers( $users ){
        $users = explode( '|||', $users );
        $toRemove = array();
        foreach( $users as $idx => &$user ){
            $user = explode( ':::', $user );
            if( count( $user ) <= 1 ){
                $toRemove[] = $idx;
            } else if( count( $user ) == 2 ){
                $user[] = '';
            }
        }
        foreach( $toRemove as $idx ){
            unset( $users[ $idx ] );
        }
        return $users;
    }
}
