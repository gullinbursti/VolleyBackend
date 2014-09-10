<?php

class BIM_Model_Moji{

    public static function invite( $memberId, $emoji, $users = array(), $nonUsers = array() ){
        $dao = new BIM_DAO_Mysql_Moji( BIM_Config::db( ) );
        // now we figure out if any of the users have actually been invited
        $invited = $dao->invite( $memberId, $emoji, $users, $nonUsers );
        return $invited;
    }

}
