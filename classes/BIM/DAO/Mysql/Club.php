<?php
class BIM_DAO_Mysql_Club extends BIM_DAO_Mysql{
    public function create( $name, $users, $ownerId ){
        $created = false;
        // add vote record
		$sql = '
			INSERT IGNORE INTO `hotornot-dev`.club 
			( name, owner_id ) 
			VALUES  
			( ?, ? )
		';
        $params = array( $name, $ownerId );
        $this->prepareAndExecute( $sql, $params );
        
        if( $this->lastInsertId ){
            $insertSql = array();
            $params = array();
            
            foreach( $users as $user ){
                $params[] = $this->lastInsertId;
                foreach( $user as $value ){
                    if( !$value ){
                        $value = '';
                    }
                    $params[] = $value;
                }
                $insertSql[] = '(?,?,?,?)';
            }
            
            if( $insertSql ){
                $insertSql = join( ',', $insertSql );
        		$sql = "
        			INSERT IGNORE INTO `hotornot-dev`.club_member 
        			( club_id, name, mobile_number, email ) 
        			VALUES
        			$insertSql
        		";
        		$this->prepareAndExecute( $sql, $params );
            }
            $created = true;
        }
        return $created;
    }
}
