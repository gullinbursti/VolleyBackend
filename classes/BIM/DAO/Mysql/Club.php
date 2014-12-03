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
            SELECT
                c.id AS id,
                c.name AS name,
                c.added AS added,
                c.owner_id AS owner_id,
                c.description AS description,
                c.img AS img,
                c.lat AS lat,
                c.lon AS lon,
                m.club_id AS club_id,
                m.extern_name AS extern_name,
                m.mobile_number AS mobile_number,
                m.email AS email,
                m.pending AS pending,
                m.blocked AS blocked,
                m.user_id AS user_id,
                m.invited AS invited,
                m.joined AS joined,
                m.blocked_date AS blocked_date,
                e.club_type AS club_type
            FROM 
                `hotornot-dev`.club AS c 
                    LEFT join `hotornot-dev`.club_member AS m ON c.id = m.club_id
                    JOIN `hotornot-dev`.tblClubTypeEnum AS e ON c.club_type_id = e.id
            WHERE c.id IN ($placeHolders)
        ";
        $stmt = $this->prepareAndExecute( $sql, $ids );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );

        // TODO - Rip the total_members counters out of this code.  Why is
        // business logic in the DAO?!?!?!
        $clubs = array();
        $memberCounts = array();
        if( $data ){
            foreach( $data as $row ){
                if( !isset( $memberCounts[ $row->id ] ) ){
                    $memberCounts[ $row->id ] = 0;
                }
                if( $memberCounts[ $row->id ] >= 500 ){
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
                                'invited' => !empty($row->invited) ? $row->invited : '',
                                'joined' => !empty($row->joined) ? $row->joined : '',
                                'blocked_date' => !empty($row->blocked_date) ? $row->blocked_date : ''
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
                    unset( $row->joined );
                    unset( $row->blocked_date );
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
                        'user_id' => $row->user_id,
                        'invited' => !empty($row->invited) ? $row->invited : '',
                        'joined' => !empty($row->joined) ? $row->joined : '',
                        'blocked_date' => !empty($row->blocked_date) ? $row->blocked_date : ''
                        )
                    ;
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

    public function create( $name, $ownerId, $description = '', $img = '', $clubType = 'USER_GENERATED', $coords = NULL ) {
        $clubId = 0;
        $lat = 0;
        $lon = 0;
        if (is_array($coords) && count($coords) == 2 && $coords[0] != "" && $coords[1] != "") {
            $lat = $coords[0];
            $lon = $coords[1];
        }
        $sql = '
            INSERT IGNORE INTO `hotornot-dev`.club
            ( name, owner_id, description, img, club_type_id, lat, lon )
            VALUES
            (?,?,?,?, (SELECT id FROM `hotornot-dev`.tblClubTypeEnum WHERE club_type = ?), ?, ?)
        ';
        $params = array( $name, $ownerId, $description, $img, $clubType, $lat, $lon );
        $this->prepareAndExecute( $sql, $params );

        if( $this->lastInsertId ) $clubId = $this->lastInsertId;

        return $clubId;
    }

    public function invite( $clubId, $users, $nonUsers ){
        $invited = false;
        // handle non users
        $insertSql = array();
        $params = array();
        foreach( $nonUsers as $user ){
            $params[] = $clubId;
            foreach( $user as $value ){
                if( !$value ){
                    $value = null;
                }
                $params[] = $value;
            }
            $insertSql[] = '(?,?,?,?)';
        }

        if( $insertSql ){
            $insertSql = join( ',', $insertSql );
            $sql = "
                INSERT INTO `hotornot-dev`.club_member
                ( club_id, extern_name, mobile_number, email )
                VALUES
                $insertSql
                ON DUPLICATE KEY UPDATE invited=NOW()
            ";
            $this->prepareAndExecute( $sql, $params );
            $invited = (bool) $this->rowCount;
        }

        // handle registered app users
        $params = array();
        $insertSql = array();
        foreach( $users as $userId ){
            $params[] = $clubId;
            $params[] = $userId;
            $insertSql[] = '(?, ?, now())';
        }

        if( $insertSql ){
            $insertSql = join( ',', $insertSql );
            $sql = "
                INSERT INTO `hotornot-dev`.club_member
                ( club_id, user_id, invited )
                VALUES
                $insertSql
                ON DUPLICATE KEY UPDATE invited=NOW()
            ";
            $this->prepareAndExecute( $sql, $params );
        }

        $invited = $invited ? true : (bool) $this->rowCount;
        return $invited;
    }

    /**
     * takes an array of keys and values
     * and updates the db for a club
     */
    public function update( $clubId, $update ){
        $params = array();
        $setSql = array();
        foreach( $update as $col => $val ){
            $setSql[] = " `$col` = ? ";
            $params[] = $val;
        }
        $params[] = $clubId;
        $sql = "
            update `hotornot-dev`.club
            set $setSql
            where id = ?
        ";
        $this->prepareAndExecute( $sql, $params );
    }

    public function delete( $clubId ){
        $sql = "
            delete from `hotornot-dev`.club
            where id = ?
        ";
        $params = array( $clubId );
        $this->prepareAndExecute( $sql, $params );
    }

    /**
     *
     * In the sql below we set the pending value to the value of the blocked column
     * if the user is already in the club as an invitee or blockee
     *
     * this means that if blocked = 0, then pending will be 0
     * and the user wull be joined to the club
     *
     * similarly if blocked = 1, then pending will be 1
     * and the user wull not be joined to the club
     *
     * @param int $clubId
     * @param int $userId
     *
     */
    public function join( $clubId, $userId ){
        $sql = "
            insert into `hotornot-dev`.club_member
            (club_id,user_id,blocked,pending,invited,joined)
            values (?,?,0,0,'0000-00-00 00:00:00',now())
            on duplicate key update
            pending = blocked;
        ";
        $params = array( $clubId, $userId );
        $this->prepareAndExecute( $sql, $params );
        return (bool) $this->rowCount;
    }

    public function quit( $clubId, $userId ){
        $sql = "
            delete from `hotornot-dev`.club_member
            where club_id = ? and user_id = ?
        ";
        $params = array( $clubId, $userId );
        $this->prepareAndExecute( $sql, $params );
        return (bool) $this->rowCount;
    }

    public function block( $clubId, $userId ){
        $sql = "
            insert into `hotornot-dev`.club_member
            (club_id,user_id,blocked,blocked_date,invited)
            values
            (?,?,1,now(),'0000-00-00 00:00:00')

            on duplicate key update
            blocked = 1, blocked_date = now()
        ";
        $params = array( $clubId, $userId );
        $this->prepareAndExecute( $sql, $params );
        return (bool) $this->rowCount;
    }

    public function unblock( $clubId, $userId ){
        $sql = "
            update `hotornot-dev`.club_member
            set blocked = 0
            where club_id = ? and user_id = ?
        ";
        $params = array( $clubId, $userId );
        $this->prepareAndExecute( $sql, $params );
        return (bool) $this->rowCount;
    }

    public function getUnsentInvites($beforeDate) {
        if ($beforeDate) {
            $sql = "
            SELECT club_id,
                   owner_id,
                   mobile_number
              FROM `hotornot-dev`.club_member, `hotornot-dev`.club
             WHERE club_id = club.id
               AND invited < ?
               AND mobile_number IS NOT NULL
               AND pending = 1
               AND user_id IS NULL
            ";
            $params = array($beforeDate);
            $stmt = $this->prepareAndExecute( $sql, $params );
            $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        } else {
            $data = null;
        }
        return $data;
    }

}
