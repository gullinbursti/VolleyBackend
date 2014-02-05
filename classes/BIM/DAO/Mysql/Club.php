<?php

class BIM_DAO_Mysql_Club extends BIM_DAO_Mysql{
    
    public function get( $ids ){
        $returnArray = true;
        if( !is_array( $ids ) ){
            $ids = array( $ids );
            $returnArray = false;
        }
       
        $placeHolders = trim(join('',array_fill(0, count( $ids ), '?,') ),',');
        $sql = "
        	select * from 
        	`hotornot-dev`.club as c 
        		left join `hotornot-dev`.club_member as m
        		on c.id = m.club_id
        	where id in ($placeHolders)
        ";
        $stmt = $this->prepareAndExecute( $sql, $ids );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        
        $clubs = array();
        $memberCounts = array();
        if( $data ){
            foreach( $data as $row ){
                if( !isset( $memberCounts[ $row->id ] ) ){
                    $memberCounts[ $row->id ] = 0;
                }
                if( $memberCounts[ $row->id ] >= 50 ){
                    $memberCounts[$row->id]++;
                    $clubs[ $row->id ]->total_members++;
                    continue;
                }
                if( empty( $clubs[ $row->id ] ) ){
                    if( !empty( $row->user_id ) || !empty( $row->mobile_number ) || !empty( $row->email )  ){
                        $row->members = array( 
                            ( object ) array(
                        		'extern_name' => $row->extern_name, 
                        		'mobile_number' => $row->mobile_number,
                        		'email' => $row->email,
                        		'pending' => $row->pending, 
                        		'blocked' => $row->blocked,
                                'user_id' => $row->user_id,
                                'invited' => $row->invited
                            ) 
                        );
                        $memberCounts[$row->id]++;
                    } else {
                        $row->members = array();
                    }
                    unset( $row->extern_name );
                    unset( $row->mobile_number );
                    unset( $row->email );
                    unset( $row->pending );
                    unset( $row->blocked );
                    unset( $row->user_id );
                    unset( $row->invited );
                    unset( $row->club_id );
                    $row->total_members = 0;
                    if( !empty( $row->members ) ){
                        $row->total_members++;
                    }
                    $clubs[ $row->id ] = $row;
                } else {
                    $club = $clubs[ $row->id ];
                    $club->members[] = 
                        ( object ) array(
                		'extern_name' => $row->extern_name, 
                		'mobile_number' => $row->mobile_number,
                		'email' => $row->email,
                		'pending' => $row->pending, 
                		'blocked' => $row->blocked,
                        'user_id' => $row->user_id
                    );
                    $memberCounts[$row->id]++;
                    $club->total_members++;
                }
            }
        }
        
        if( !$returnArray ){
            if( !empty( $clubs ) ){
                $clubs = current($clubs);
            } else {
                $clubs = (object) array();
            }
        } else {
            if( !empty( $clubs ) ){
                $clubs = array_values($clubs);
            } else {
                $clubs = array();
            }
        }
        
        return $clubs;
    }
    
    public function create( $name, $users, $ownerId, $description = '', $img = '' ){
        $created = false;
        // add vote record
		$sql = '
			INSERT IGNORE INTO `hotornot-dev`.club 
			( name, owner_id, description, img ) 
			VALUES  
			(?,?,?,?)
		';
        $params = array( $name, $ownerId, $description, $img );
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
        			( club_id, extern_name, mobile_number, email ) 
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
