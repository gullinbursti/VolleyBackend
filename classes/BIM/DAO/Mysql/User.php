<?php

class BIM_DAO_Mysql_User extends BIM_DAO_Mysql{

    public function getClubInvites( $userId ){
        $query = "
            SELECT club_id
            FROM `hotornot-dev`.club_member
            WHERE user_id = ?
                AND pending = 1
                AND blocked = 0
        ";
        $params = array( $userId );
        $stmt = $this->prepareAndExecute( $query, $params );
        $ids = $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
        return $ids;
    }

    public function getClubIds( $userId ){
        $query = "
            (
                SELECT club_id
                FROM `hotornot-dev`.club_member
                WHERE user_id = ?
                    AND blocked = 0
            )
            UNION
            (
                SELECT id
                FROM `hotornot-dev`.club
                WHERE owner_id = ?
            )
        ";
        $params = array( $userId, $userId );
        $stmt = $this->prepareAndExecute( $query, $params );
        $ids = $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
        return $ids;
    }

    public function getAllIds(){
        $sql = "select id from `hotornot-dev`.tblUsers";
        $stmt = $this->prepareAndExecute($sql);
        return $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
    }

    public function getLikedVolleys( $userId ){
        $sql = "
            select challenge_id
            from `hotornot-dev`.tblChallengeVotes
            where user_id = ?
        ";
        $params = array( $userId );
        $stmt = $this->prepareAndExecute($sql, $params);
        $data = $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
        return $data;
    }

    public function delete( $userId ){
        $sql = "
            delete from `hotornot-dev`.tblChallengeParticipants
            where challenge_id in (
                select id from `hotornot-dev`.tblChallenges where creator_id = ?
            )
            or user_id = ?
        ";
        $params = array( $userId, $userId );
        $this->prepareAndExecute( $sql, $params );

        $sql = "
            delete from `hotornot-dev`.tblFlaggedUserApprovals
            where challenge_id in (
                select id from `hotornot-dev`.tblChallenges where creator_id = ?
            ) or user_id = ?
        ";
        $params = array( $userId, $userId );
        $this->prepareAndExecute( $sql, $params );

        $sql = "
            delete from `hotornot-dev`.tblChallenges
            where creator_id = ?
        ";
        $params = array( $userId );
        $this->prepareAndExecute( $sql, $params );

        $sql = "
            delete from `hotornot-dev`.tblUsers
            where id = ?
        ";
        $params = array( $userId );
        $this->prepareAndExecute( $sql, $params );
    }

    public function removeLikes( $userId ){
        $sql = "
            delete from `hotornot-dev`.tblChallengeVotes
            where user_id = ?
        ";
        $params = array( $userId );
        $this->prepareAndExecute( $sql, $params );
    }

    public function block( $userId ){
        $sql = "
            update `hotornot-dev`.tblUsers
            set abuse_ct = 999999
            where id = ?
        ";
        $params = array( $userId );
        $this->prepareAndExecute( $sql, $params );
    }

    public function purgeContent( $userId ){
        $sql = "
            delete from `hotornot-dev`.tblChallengeParticipants
            where challenge_id in (
                select id from `hotornot-dev`.tblChallenges where creator_id = ?
            )
            or user_id = ?
        ";
        $params = array( $userId, $userId );
        $this->prepareAndExecute( $sql, $params );

        $sql = "
            delete from `hotornot-dev`.tblFlaggedUserApprovals
            where challenge_id in (
                select id from `hotornot-dev`.tblChallenges where creator_id = ?
            ) or user_id = ?
        ";
        $params = array( $userId, $userId );
        $this->prepareAndExecute( $sql, $params );

        $sql = "
            delete from `hotornot-dev`.tblChallenges
            where creator_id = ?
            and is_verify != 1
        ";
        $params = array( $userId );
        $this->prepareAndExecute( $sql, $params );

        $sql = "
            update `hotornot-dev`.tblUsers
            set total_challenges = 0;
            where creator_id = ?
        ";
        $params = array( $userId );
        $this->prepareAndExecute( $sql, $params );

    }

    public function getRandomIds( $total = 1, $exclude = array(), $beforeDate = null ){
        $sql = "SELECT id FROM `hotornot-dev`.`tblUsers` ";

        $where = array();
        if( $exclude ){
            $placeHolders = trim(join('',array_fill(0, count( $exclude ), '?,') ),',' );
            $where[] = "id not in ($placeHolders)";
        }
        if( $beforeDate ){
            $where[] = " added <= '$beforeDate' ";
        }
        if( count( $where ) ){
            $where = join( ' and ', $where );
            $sql = "$sql where $where";
        }

        $total = (int) $total;
        $sql = "$sql ORDER BY RAND() limit $total";

        $stmt = $this->prepareAndExecute($sql,$exclude);
        return $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
    }

    public function getData( $ids ){
        $returnArray = true;
        if( !is_array( $ids ) ){
            $ids = array( $ids );
            $returnArray = false;
        }

        $placeHolders = trim(join('',array_fill(0, count( $ids ), '?,') ),',');
        $sql = "select * from `hotornot-dev`.tblUsers where id in ($placeHolders)";
        $stmt = $this->prepareAndExecute( $sql, $ids );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );

        if( !$returnArray ){
            if( !empty( $data ) ){
                $data = $data[0];
            } else {
                $data = (object) array();
            }
        } else {
            if( !$data ){
                $data = array();
            }
        }
        return $data;
    }

    public function archive( $id, $username, $data ){
        $sql = "
            insert into `hotornot-dev`.user_archive ( user_id, username, data )
            values ( ?, ?, ? )
        ";

        $params = array( $id, $username, $data);
        $this->prepareAndExecute( $sql, $params );
    }

    /**
     *
     * @param unknown_type $volleyId - the verify volley id
     * @param unknown_type $targetId - the creator iof the volley
     * @param unknown_type $userId - a participant in the vollry
     * @param unknown_type $count - the number of flag ticks to give to the target
     */
    public function flag( $volleyId, $targetId, $userId, $count ){
        // give the target the appropriate nu,ber of flags
        //$count = (int) $count;
        //$sql = "update `hotornot-dev`.tblUsers set abuse_ct = abuse_ct + ? where id = ?";
        //$params = array( $count, $targetId );
        //$this->prepareAndExecute($sql,$params);

        $sql = "update `hotornot-dev`.tblChallenges set updated = now() where id = ?";
        $params = array( $volleyId );
        $this->prepareAndExecute($sql,$params);

        // update the users participant record that they have voted
        $sql = "
            INSERT IGNORE INTO `hotornot-dev`.tblFlaggedUserApprovals
             (flag, user_id, challenge_id, added)
             VALUES (?,?,?,?)
        ";
        $params = array( $count, $userId, $volleyId, time() );
        $this->prepareAndExecute($sql,$params);
    }

    public function getTotalVotes( $userId ){
        $count = 0;
        $sql = "SELECT count(*) as count FROM `hotornot-dev`.`tblChallengeVotes` WHERE `challenger_id` = ?";
        $params = array( $userId );
        $stmt = $this->prepareAndExecute($sql,$params);
        return $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
    }

    public function getLikers( $userId ){
        $count = 0;
        $sql = "
            SELECT user_id as id, added, challenge_id
            FROM `hotornot-dev`.`tblChallengeVotes`
            WHERE `challenger_id` = ?
            ORDER by added desc
            LIMIT 50
        ";
        $params = array( $userId );
        $stmt = $this->prepareAndExecute($sql,$params);
        return $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
    }

    public function getVerifiers( $userId ){
        $count = 0;
        $sql = "
            SELECT a.user_id as id, added
            FROM `hotornot-dev`.`tblChallenges` as c
                JOIN `hotornot-dev`.`tblFlaggedUserApprovals` as a
                ON c.id = a.challenge_id
            WHERE c.creator_id = ?
                AND c.is_verify = 1
            ORDER by a.added desc
            LIMIT 50
        ";
        $params = array( $userId );
        $stmt = $this->prepareAndExecute($sql,$params);
        return $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
    }

    public function getShoutouts( $targetUserId ){
        $count = 0;
        $sql = "
            SELECT c.creator_id as id, c.added
            FROM  `hotornot-dev`.`tblShoutouts` as s
                JOIN `hotornot-dev`.`tblChallenges` as c
                ON s.challenge_id = c.id
            WHERE s.target_user_id = ?
            ORDER by c.added desc
            LIMIT 50
        ";
        $params = array( $targetUserId );
        $stmt = $this->prepareAndExecute($sql,$params);
        return $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
    }

    public function setTotalVotes( $userId, $count ){
        $sql = 'UPDATE `hotornot-dev`.tblUsers SET total_votes = ? where id = ?';
        $params = array( $count, $userId );
        $this->prepareAndExecute($sql, $params);
    }

    public function setSmsVerified( $userId, $smsVerified ){
        $sql = 'UPDATE `hotornot-dev`.tblUsers SET sms_verified = ? where id = ?';
        $params = array( $smsVerified, $userId );
        $this->prepareAndExecute($sql, $params);
    }

    public function setTotalVolleys( $userId, $count ){
        $sql = 'UPDATE `hotornot-dev`.tblUsers SET total_challenges = ? where id = ?';
        $params = array( $count, $userId );
        $this->prepareAndExecute($sql, $params);
    }

    public function getTotalPokes( $userId ){
        $count = 0;
        $sql = "SELECT count(*) as count FROM `hotornot-dev`.`tblUserPokes` WHERE `user_id` = ?";
        $params = array( $userId );
        $stmt = $this->prepareAndExecute($sql,$params);
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        if( $data ){
            $count = $data[0]->count;
        }
        return $count;
    }

    public function getTotalChallenges( $userId ){
        $count = 0;
        $sql = "
            (select count(*) as count
            from `hotornot-dev`.tblChallenges as tc
            where tc.creator_id = ? and is_verify != 1
            ) union all (
            select count(*) as count
            from `hotornot-dev`.tblChallengeParticipants as tcp
            where tcp.user_id = ?)
        ";
        /*
        $sql = "
            select count(*) as count
            from `hotornot-dev`.tblChallenges as tc
                join `hotornot-dev`.tblChallengeParticipants as tcp
                on tc.id = tcp.challenge_id
            where tc.creator_id = ? OR tcp.user_id = ?
        ";
        */


        $params = array( $userId, $userId );
        $stmt = $this->prepareAndExecute($sql,$params);
        $data = $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
        if( $data ){
            $count = array_sum($data);
        }
        return $count;
    }

    public function getIdByUsername( $username ){
        $id = null;
        $sql = "select id from `hotornot-dev`.tblUsers where username = ?";
        $params = array( $username );
        $stmt = $this->prepareAndExecute( $sql, $params );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        if( $data ){
            $id = $data[0]->id;
        }
        return $id;
    }

    public function getDataByUsername( $username ){
        $sql = "select * from `hotornot-dev`.tblUsers where username = ?";
        $params = array( $username );
        $stmt = $this->prepareAndExecute( $sql, $params );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        if( ! isset($data[0]) ){
            $data = new stdClass();
        } else {
            $data = $data[0];
        }
        return $data;
    }

    public function getIdByToken( $token ){
        $id = null;
        $sql = "select id from `hotornot-dev`.tblUsers where adid = ?";
        $params = array( $token );

        $stmt = $this->prepareAndExecute( $sql, $params );
        $id = $stmt->fetchColumn( 0 );
        if( !$id ){
            $sql = "select id from `hotornot-dev`.tblUsers where device_token = ?";
            $stmt = $this->prepareAndExecute( $sql, $params );
            $id = $stmt->fetchColumn( 0 );
        }
        return $id;
    }

    public function getDataByToken( $token ){
        $sql = "select * from `hotornot-dev`.tblUsers where device_token = ?";
        $params = array( $token );
        $stmt = $this->prepareAndExecute( $sql, $params );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        if( ! isset($data[0]) ){
            $data = new stdClass();
        } else {
            $data = $data[0];
        }
        return $data;
    }

    public function getRandomUserId( $exclude = array() ){
        if( !is_array( $exclude ) ){
            $exclude = array( $exclude );
        }
        $id = null;
        $sql = "select id from `hotornot-dev`.tblUsers";
        if( $exclude ){
            $placeHolders = join(',',array_fill(0, count( $exclude ), '?') );
            $sql = "$sql where id not in ($placeHolders)";
        }
        $stmt = $this->prepareAndExecute( $sql, $exclude );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        if( $data ){
            $len = count( $data );
            $idx = mt_rand(1, $len) - 1;
            $id = $data[ $idx ]->id;
        }
        return $id;
    }

    public function updateLastLogin( $userId ){
        $date = new DateTime();
        $date = $date->format( 'Y-m-d H:i:s' );
        $query = 'UPDATE `hotornot-dev`.tblUsers SET last_login = ? WHERE id = ?';
        $params = array( $date, $userId );
        $stmt = $this->prepareAndExecute( $query, $params );
        return $date;
    }

    public function updateUsernameAvatarFirstRun( $userId, $username, $imgUrl, $birthdate, $email = null, $deviceToken = '' ){
        $ageSql = $passSql = '';
        $email = $email?:'';
        $params = array( $username, $imgUrl, $userId );

        $notifications = 'N';
        if( $deviceToken ){
            $notifications = 'Y';
        }

        if( $birthdate ){
            $birthdate = new DateTime( $birthdate );
            $birthdate = $birthdate->format('U');
            $ageSql = ' age = ?,';
            $params = array( $username, $imgUrl, $birthdate, $userId );
        }

        if( $email ){
            $passSql = ' email = ?,';
            $params = array( $username, $imgUrl, $birthdate, $email );
        }

        $query = "
            UPDATE `hotornot-dev`.tblUsers
            SET username = ?,
                img_url = ?,
                $ageSql
                $passSql
                last_login = CURRENT_TIMESTAMP,
                notifications = ?,
                device_token = ?
            WHERE id = ?
        ";
        $params[] = $notifications;
        $params[] = $deviceToken;
        $params[] = $userId;
        $stmt = $this->prepareAndExecute( $query, $params );
    }

    public function updateUsernameAvatar( $userId, $username, $imgUrl, $birthdate, $email = null ){
        $ageSql = $passSql = '';
        $email = $email?:'';
        $params = array( $username, $imgUrl, $userId );

        if( $birthdate ){
            $birthdate = new DateTime( $birthdate );
            $birthdate = $birthdate->format('U');
            $ageSql = ' age = ?,';
            $params = array( $username, $imgUrl, $birthdate, $userId );
        }

        if( $email ){
            $passSql = ' email = ?,';
            $params = array( $username, $imgUrl, $birthdate, $email, $userId );
        }

        $query = "
            UPDATE `hotornot-dev`.tblUsers
            SET username = ?,
                img_url = ?,
                $ageSql
                $passSql
                last_login = CURRENT_TIMESTAMP
            WHERE id = ?
        ";
        $stmt = $this->prepareAndExecute( $query, $params );
    }

    public function updateUsername( $userId, $username ){
        $query = '
            UPDATE `hotornot-dev`.tblUsers
            SET username = ?
            WHERE id = ?';
        $params = array( $username, $userId );
        $stmt = $this->prepareAndExecute( $query, $params );
    }

    public function updatePaid( $userId, $isPaid ){
        $query = '
            UPDATE `hotornot-dev`.tblUsers
            SET paid = ?
            WHERE id = ?';
        $params = array( $isPaid, $userId );
        $stmt = $this->prepareAndExecute( $query, $params );
    }

    public function updateAbuseCount( $userId, $abuseCt ){
        $query = '
            UPDATE `hotornot-dev`.tblUsers
            SET abuse_ct = ?
            WHERE id = ?';
        $params = array( $abuseCt, $userId );
        $stmt = $this->prepareAndExecute( $query,$params );
    }

    public function updateNotifications( $userId, $isNotifications ){
        $query = '
            UPDATE `hotornot-dev`.tblUsers
            SET notifications = ?
            WHERE id = ?';
        $params = array( $isNotifications, $userId );
        $stmt = $this->prepareAndExecute( $query,$params );
    }

    public function updateFBUsername( $userId, $fbId, $username, $gender ){
        $query = "
            UPDATE `hotornot-dev`.tblUsers
            SET username = ?,
                fb_id = ?,
                gender = ?
             WHERE id = ?
        ";
        $params = array( $username, $fbId, $gender, $userId );
        $stmt = $this->prepareAndExecute( $query, $params );
    }

    public function updateFB( $userId, $fbId, $gender ){
        $query = "
            UPDATE `hotornot-dev`.tblUsers
            SET fb_id = ?,
                gender = ?
             WHERE id = ?
        ";
        $params = array( $fbId, $gender, $userId );
        $stmt = $this->prepareAndExecute( $query, $params );
    }

    public function poke( $pokerId, $pokeeId ){
        $query = 'INSERT IGNORE INTO `hotornot-dev`.tblUserPokes (user_id, poker_id, added) VALUES ( ?, ?, NOW() )';
        $params = array( $pokeeId, $pokerId );
        $stmt = $this->prepareAndExecute($query, $params);
        return $this->lastInsertId;
    }

    public function create( $username, $adId, $email = null ){
        // add new user
        $params = array( $username, $adId );
        $cols = "username, device_token, fb_id, gender, bio, website, paid, points, notifications, last_login, added, adid";
        $vals = "?, null, '', 'N', '', '', 'N', '0', 'Y', CURRENT_TIMESTAMP, NOW(), ?";
        if( $email ){
            $cols = "$cols, email";
            $vals = "$vals, ?";
            $params[] = $email;
        }
        $query = "
            INSERT INTO `hotornot-dev`.tblUsers
            ( $cols )
            VALUES ( $vals )
        ";
        $stmt = $this->prepareAndExecute($query, $params);

        return $this->lastInsertId;
    }

    public function getFbInviteId( $fbId ){
        $id = null;
        $query = "SELECT `id` FROM `hotornot-dev`.`tblInvitedUsers` WHERE `fb_id` = ?";
        $params = array( $fbId );
        $stmt = $this->prepareAndExecute($query, $params);
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        if( $data ){
            $id = $data[0]->id;
        }
        return $id;
    }

    public function getFbInvitesToVolley( $userId ){
        // get any pending challenges for this invited user
        $query = "
            SELECT tc.`id`
            from `hotornot-dev`.tblChallenges as tc
                JOIN `hotornot-dev`.tblChallengeParticipants as tcp
                ON tc.id = tcp.challenge_id
            WHERE tc.`status_id` = 7
                AND tcp.user_id = ?;
        ";
        $params = array( $userId );
        $stmt = $this->prepareAndExecute($query, $params);
        $ids = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        foreach( $ids as &$id ){
            $id = $id->id;
        }
        return $ids;
    }

    public function getUsersWithSimilarName( $username ){
        $query = '
            SELECT id from `hotornot-dev`.tblUsers
            WHERE username LIKE ?
                and username not regexp "[0-9]{10}"
            order by last_login desc
            limit 64
        ';
        $params = array( "%$username%" );
        $stmt = $this->prepareAndExecute($query, $params);
        $ids = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        foreach( $ids as &$id ){
            $id = $id->id;
        }
        return $ids;
    }

    public function getOpponentsWithSnaps( $userId ){
        $sql = "
            select tc.creator_id, tcp.user_id, max(tcp.img) as img
            from `hotornot-dev`.tblChallengeParticipants as tcp
                join `hotornot-dev`.tblChallenges as tc
                on tc.id = tcp.challenge_id
            where (tc.creator_id = ? OR tcp.user_id = ?)
                AND tcp.img != ''
                AND tcp.img is not null
            group by creator_id, user_id, img
        ";
        $params = array( $userId, $userId );
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
    }

    public function setAgeRange( $userId, $ageRange ){
        $sql = 'update `hotornot-dev`.tblUsers set age = ? where id = ? ';
        $params = array( $ageRange, $userId );
        $this->prepareAndExecute( $sql, $params );
    }

    public function setAdvertisingId( $userId, $adId ){
        $sql = 'update `hotornot-dev`.tblUsers set adid = ? where id = ? ';
        $params = array( $adId, $userId );
        $this->prepareAndExecute( $sql, $params );
    }

    public function setDeviceToken( $userId, $deviceToken ){
        $sql = 'update `hotornot-dev`.tblUsers set device_token = ? where id = ? ';
        $params = array( $deviceToken, $userId );
        $this->prepareAndExecute( $sql, $params );
    }

        /*
        $sql = "
            select c.creator_id as id, sum(flag) as sum
            from `hotornot-dev`.tblChallenges as c
                join `hotornot-dev`.tblFlaggedUserApprovals as u
                on c.id = u.challenge_id
            where c.is_verify = 1
            group by c.creator_id
            having sum >= 10
            order by c.updated desc, id
            limit $limit
        ";
        */
    public function getSuspendees( $limit = 50 ){
        $limit = mysql_escape_string($limit);
        $sql = "
            select u.id
            from `hotornot-dev`.tblUsers as u
                join  `hotornot-dev`.tblChallenges as c
                on u.id = c.creator_id
            where u.abuse_ct >= 10
            and c.is_verify = 1
            order by c.updated desc, id
            limit $limit
        ";
        $stmt = $this->prepareAndExecute( $sql );
        return $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
    }

    public function getPendingSuspendees( $limit = 50 ){
        $limit = mysql_escape_string($limit);
        $sql = "select id from `hotornot-dev`.tblUsers where abuse_ct < 10 and abuse_ct > 5 order by abuse_ct desc, id limit $limit";
        $stmt = $this->prepareAndExecute( $sql );
        return $stmt->fetchAll( PDO::FETCH_COLUMN, 0 );
    }

    /**
        select username from tblUsers where username = 'wertuigh'
        union all
        select email from tblUsers where email = 'fdggte'
     */
    public function usernameOrEmailExists( $input ){
        $sql = "
            (select 'username' as property, username as value from `hotornot-dev`.tblUsers where username = ? limit 1)
            union all
            (select 'email' as property, email as value from `hotornot-dev`.tblUsers where email = ? limit 1)
        ";
        $params = array( $input->username, $input->email );
        $stmt = $this->prepareAndExecute( $sql, $params );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        return $data;
    }

    private function chooseKikUser( ){
        $oneWeekAgo = time() - (86400 * 7);
        $randLimit = mt_rand(0,20);
        $sql = "
            select k.username
            from growth.kik_users as k
            where last_update <= $oneWeekAgo
            order by created_at desc
            limit $randLimit,1
        ";

        $stmt = $this->prepareAndExecute( $sql );
        return $stmt->fetchColumn(0);
    }

    public function getRandomKikUser( ){
        $kikUser = null;
        $sql = "
            insert into growth.kik_checkout
            (username)
            values
            (?)
        ";
        $maxAttempts = 2;
        while( !$kikUser && $maxAttempts-- > 0 ){
            $anyUser = $this->chooseKikUser();
            if( $anyUser ){
                $this->beginTransaction();
                try{
                    $params = array( $anyUser );
                    $stmt = $this->prepareAndExecute( $sql, $params );
                    $this->commit();
                    $time = time();
                    $sql = "
                        update growth.kik_users set last_update = $time
                        where username = ?
                    ";
                    $params = array( $anyUser );
                    $stmt = $this->prepareAndExecute( $sql, $params );
                    $kikUser = $anyUser;
                } catch( Exception $e ){
                    $this->rollback();
                }
            } else {
                // break;
            }
        }

        return $kikUser;
    }
/*
stdClass Object
(
     [username] => woggywoowoo
    [pic] =>
    [thumbnail] =>
    [fullName] => woogy Woowoo
    [firstName] => woogy
    [lastName] => Woowoo
)
*/
    public function createKikUser( $input ){
        $sql = "
            insert into growth.kik_reg_users
            ( username, pic, thumbnail, firstName, lastName, bim_id, last_login )
            values
            (?,?,?,?,?,?,now())
            on duplicate key update appOpens = appOpens + 1, last_login = now()
        ";
        $params = array( $input->username, $input->pic, $input->thumbnail, $input->firstName, $input->lastName, $input->bim_id );
        $this->prepareAndExecute( $sql, $params );
    }

    public function getKikUserId( $kikId ){
        $sql = "
            select bim_id
            from growth.kik_reg_users
            where username = ?
        ";
        $params = array( $kikId );
        $stmt = $this->prepareAndExecute( $sql, $params );
        return $stmt->fetchColumn(0);
    }

    public function logKikSend( $input ){
        $sql = "
            insert ignore into growth.kik_sends
            ( source, target )
            values
            (?,?)
        ";
        $params = array( $input->source, $input->target );
        $this->prepareAndExecute( $sql, $params );
    }

    public function logKikOpen( $input ){
        $sql = "
            insert ignore into growth.kik_opens
            ( source, target )
            values
            (?,?)
        ";
        $params = array( $input->source, $input->target );
        $this->prepareAndExecute( $sql, $params );
    }

    public function getLatestKikUsers( ){
        $sql = "
            select * from growth.kik_reg_users
            where bim_id is not null
            order by last_login desc
            limit 20;
        ";
        $stmt = $this->prepareAndExecute( $sql );
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass' );
    }

    public function getKikNames( $ids ){
        $placeHolders = join(',',array_fill(0, count( $ids ), '?') );

        $sql = "
            select bim_id as id, username as kik_id
            from growth.kik_reg_users
            where bim_id in ($placeHolders)
        ";
        $stmt = $this->prepareAndExecute( $sql, $ids );
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'stdClass' );
    }

    // getAllPushTokens
    public function getAllPushTokens( ){
        $sql = "
            select device_token
            from `hotornot-dev`.tblUsers
            where device_token is not null
                and device_token != ''
                and added > '2013-11-25'
                and notifications = 'Y'
            order by added desc
        ";
        $stmt = $this->prepareAndExecute( $sql );
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
}
