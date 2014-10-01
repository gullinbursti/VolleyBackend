<?php

class BIM_DAO_Mysql_Moji extends BIM_DAO_Mysql{

    public function invite( $memberId, $emoji, $users, $nonUsers ){
        $invited = false;
        // handle non users
        $insertSql = array();
        $params = array();
        foreach( $nonUsers as $user ){
            $params[] = $memberId;
            $params[] = $emoji;
            $params[] = $user;
            $insertSql[] = '(?,?,?)';
        }
        if( $insertSql ){
            $insertSql = join( ',', $insertSql );
            $sql = "
                INSERT IGNORE INTO `hotornot-dev`.tbl_moji_invite
                ( member_id, emoji, mobile_number )
                VALUES
                $insertSql
            ";
            $this->prepareAndExecute( $sql, $params );
            $invited = (bool) $this->rowCount;
        }
        // handle registered app users
        $params = array();
        $insertSql = array();
        foreach( $users as $userId ){
            $params[] = $memberId;
            $params[] = $emoji;
            $params[] = $userId;
            $insertSql[] = '(?, ?, ?, now())';
        }
        if( $insertSql ){
            $insertSql = join( ',', $insertSql );
            $sql = "
                INSERT IGNORE INTO `hotornot-dev`.club_member
                ( member_id, emoji, user_id, invited )
                VALUES
                $insertSql
            ";
            $this->prepareAndExecute( $sql, $params );
        }
        $invited = $invited ? true : (bool) $this->rowCount;
        return $invited;
    }

}
