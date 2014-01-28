<?php 

class BIM_Model_Club{
    public static function create( $name, $users, $ownerId ) {
        $dao = new BIM_DAO_Mysql_Club( BIM_Config::db( ) );
        return $dao->create( $name, $users, $ownerId );
    }
}