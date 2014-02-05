<?php
class BIM_Controller_Clubs extends BIM_Controller_Base {
    /**
     * name=<name>
     * users=name:::number:::email|||
     */
    public function create(){
        $created = false;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->userID ) && !empty( $input->name ) ){
            $users = !empty( $input->users ) ? self::extractUsers( $input->users ) : array();
            $description = !empty( $input->description ) ? $input->description : '';
            $img = !empty( $input->img ) ? $input->img : '';
            $input->userID = $this->resolveUserId( $input->userID );
            $created = BIM_App_Clubs::create($input->name, $users, $input->userID, $description, $img);
        }
        return $created;
    }
    
    private static function extractUsers( $users ){
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
    
    public function get(){
        $club = null;
        $input = (object) ($_POST ? $_POST : $_GET);
        if( !empty( $input->clubID ) ){
            $club = BIM_Model_Club::get($input->clubID);
        }
        return $club;
    }
}
