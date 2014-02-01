<?php 

class BIM_Report{
    // get the top 50 users that have been suspended
    // and get the top 50 that are about to be suspended
    
    public static function usersThatFlag(){
        $sql = "
        	select u.id, u.img_url, count(*) as flags 
        	from tblUsers as u join tblFlaggedUserApprovals as f on u.id = f.user_id 
        	where f.added > unix_timestamp('2013-09-05') 
        	group by u.id 
        	order by flags desc
        	limit 200
        ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $stmt = $dao->prepareAndExecute( $sql );
        $userData = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        echo("
        <html>
        <head>
        </head>
        <body>
        <table border=1 cellpadding=10>
        <tr>
        <th>Image</th>
        <th>Username</th>
        <th>Age</th>
        <th>Flags</th>
        </tr>
        ");
        // now get the flag counts for each user
        foreach( $userData as $data){
            $user = BIM_Model_User::get( $data['id'] );
            $datetime1 = new DateTime();
            $datetime2 = new DateTime($user->age);
            $interval = $datetime1->diff($datetime2);
            $age = $interval->y;
            echo "
            <tr>
            <td><img src='$user->avatar_url'></td>
            <td>$user->username</td>
            <td>$age</td>
            <td>".$userData['flags']."</td>
            </tr>
            ";
        }
        echo("
        </table>
        </body>
        </html>
        ");
    }
    
    
    public static function carlosDanger(){
        echo("
        <html>
        <head>
        </head>
        <body>
        ");
        
        $users = BIM_Model_User::getSuspendees();
        echo "<hr>Suspended - ".count( $users )."<hr>\n";
        
        echo("
        <table border=1 cellpadding=10>
        <tr>
        <th>Image</th>
        <th>Username</th>
        <th>Age</th>
        <th>Flags</th>
        <th>Approvals</th>
        <th>Abuse Count</th>
        </tr>
        ");
        // now get the flag counts for each user
        foreach( $users as $user ){
            $vv = BIM_Model_Volley::getVerifyVolley($user->id);
            if( $vv->isExtant() ){
                $datetime1 = new DateTime();
                $datetime2 = new DateTime($user->age);
                $interval = $datetime1->diff($datetime2);
                $age = $interval->y;
                $flagCounts = $vv->getFlagCounts();
                echo "
                <tr>
                <td><img src='$user->avatar_url'></td>
                <td>$user->username</td>
                <td>$age</td>
                <td>$flagCounts->flags</td>
                <td>$flagCounts->approves</td>
                <td>$user->abuse_ct</td>
                </tr>
                ";
            }
        }
        echo("
        </table>
        ");
        
        $users = BIM_Model_User::getPendingSuspendees();
        echo "<hr>Pending - ".count( $users )."<hr>\n";
        
        echo("
        <table border=1 cellpadding=10>
        <tr>
        <th>Image</th>
        <th>Username</th>
        <th>Age</th>
        <th>Flags</th>
        <th>Approvals</th>
        <th>Abuse Count</th>
        </tr>
        ");
        // now get the flag counts for each user
        foreach( $users as $user ){
            $vv = BIM_Model_Volley::getVerifyVolley($user->id);
            if( $vv->isExtant() ){
                $datetime1 = new DateTime();
                $datetime2 = new DateTime($user->age);
                $interval = $datetime1->diff($datetime2);
                $age = $interval->y;
                $flagCounts = $vv->getFlagCounts();
                echo "
                <tr>
                <td><img src='$user->avatar_url'></td>
                <td>$user->username</td>
                <td>$age</td>
                <td>$flagCounts->flags</td>
                <td>$flagCounts->approves</td>
                <td>$user->abuse_ct</td>
                </tr>
                ";
            }
        }
        echo("
        </table>
        ");
        
        echo("
        </body>
        </html>
        ");
    }
    
    public static function printStats(){
        $startDate = '2013-09-10 00:00:00';
        $endDate = '2013-09-17 00:00:00';
        $totalUsers = self::getTotalUsers( $startDate, $endDate );
        $totalVolleys = self::getTotalVolleys($startDate, $endDate);
        $totalVolleyJoins = self::getTotalVolleyJoins($startDate, $endDate);
        $totalFlagged = self::getTotalFlagged($startDate, $endDate);
        $totalVerified = self::getTotalVerified($startDate, $endDate);
        $avgVolleys = self::getVolleyAverages($startDate, $endDate);
        $avgFlags = self::getFlagAverages($startDate, $endDate);
        $avgVerifies = self::getVerifyAverages($startDate, $endDate);
        $avgLikes = self::getLikeAverages($startDate, $endDate);
        $totalActiveUsers = self::getActiveUsers($type);
    }
    
    public static function getStats( ){
        $time = time() - (86400 * 7);
        $date = new DateTime( "@$time" );
        $date = $date->format('Y-m-d H:i:s');
        
        /*
            (select count(distinct creator_id) as 'unique_users',  count(*) as count, (count/unique_users) as 'per_user', 'Selfie creates' as type from `hotornot-dev`.tblChallenges where added > '2013-11-25' and is_verify != 1 )
                union 
            (select count(distinct user_id) as 'unique_users', count(*) as count, (count/unique_users) as 'per_user','Selfie joins' as type from `hotornot-dev`.tblChallengeParticipants where from_unixtime(joined) > '2013-11-25' )
                union 
            (select count(distinct id) as 'unique_users' , count(*) as count, (count/unique_users) as 'per_user','FR Completes' as type from `hotornot-dev`.tblUsers where added > '2013-11-25' and username not regexp '[0-9]{10}' )
                union 
            (select count(distinct id) as 'unique_users' , count(*) as count, (count/unique_users) as 'per_user','FR Begins' as type from `hotornot-dev`.tblUsers where added > '2013-11-25'  )
                union 
            (select count(distinct user_id) as 'unique_users',  count(*) as count, (count/unique_users) as 'per_user','Verifies' as type from `hotornot-dev`.tblFlaggedUserApprovals where added > unix_timestamp('2013-11-25') )
                union
            (select count(distinct user_id) as 'unique_users',  count(*) as count, (count/unique_users) as 'per_user','Likes' as type from `hotornot-dev`.tblChallengeVotes where added > '2013-11-25' )
                union
            (select count(distinct user_id) as 'unique_users',  count(*) as count, (count/unique_users) as 'per_user','Deactivates' as type from `hotornot-dev`.user_archive where added > '2013-11-25' )
            order by type;
         */
        
        $sql = "
            (select count(distinct creator_id) as 'unique_users', date_format(convert_tz(added,'+00:00','-08:00'),'%Y-%m-%d, %W') as day , count(*) as count, 'Selfie creates' as type from `hotornot-dev`.tblChallenges where added > '$date' and is_verify != 1 group by day)
                union 
            (select count(distinct user_id) as 'unique_users', date_format(convert_tz(from_unixtime(joined),'+00:00','-08:00'),'%Y-%m-%d, %W') as day , count(*) as count, 'Selfie joins' as type from `hotornot-dev`.tblChallengeParticipants where from_unixtime(joined) > '$date' group by day)
                union 
            (select count(distinct id) as 'unique_users' , date_format(convert_tz(added,'+00:00','-08:00'),'%Y-%m-%d, %W') as day, count(*) as count, 'FR Completes' as type from `hotornot-dev`.tblUsers where added > '$date' and username not regexp '[0-9]{10}' group by day)
                union 
            (select count(distinct id) as 'unique_users' , date_format(convert_tz(added,'+00:00','-08:00'),'%Y-%m-%d, %W') as day, count(*) as count, 'FR Begins' as type from `hotornot-dev`.tblUsers where added > '$date' group by day )
                union 
            (select count(distinct user_id) as 'unique_users', date_format(convert_tz(from_unixtime(added),'+00:00','-08:00'),'%Y-%m-%d, %W') as day , count(*) as count, 'Verifies' as type from `hotornot-dev`.tblFlaggedUserApprovals where added > unix_timestamp('$date') group by day)
            	union
			(select count(distinct user_id) as 'unique_users', date_format(convert_tz(added,'+00:00','-08:00'),'%Y-%m-%d, %W') as day , count(*) as count, 'Likes' as type from `hotornot-dev`.tblChallengeVotes where added > '$date' group by day)
				union
			(select count(distinct user_id) as 'unique_users', date_format(convert_tz(added,'+00:00','-08:00'),'%Y-%m-%d, %W') as day , count(*) as count, 'Deactivates' as type from `hotornot-dev`.user_archive where added > '$date' group by day)
            order by day desc,type;
        ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $stmt = $dao->prepareAndExecute( $sql );
        $data = $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
        
        echo("
        <html>
        <head>
		<script src='http://code.jquery.com/jquery-1.10.1.min.js'></script>
        </head>
        <body>
        
		<hr>Stats each day midnight -> midnight PST<hr>\n
		
        <table border=1 cellpadding=10>
        <tr>
        <th>Unique Users</th>
        <th>Day</th>
        <th>Count</th>
        <th>Type</th>
        </tr>
        ");
        // now get the flag counts for each user
        $n = 1;
        foreach( $data as $info ){
            echo "
                <tr>
                <td>
                    $info->unique_users
                </td>
                <td>
                	$info->day
            	</td>
                <td>
                	$info->count
            	</td>
            	<td>
                	$info->type
            	</td>
            	</tr>
            ";
            if( ($n % 7) == 0 ){
                echo "\n<tr><td colspan=4></tr>\n";
            }
            $n++;
        }
        echo("
        </table>
        </body>
        </html>
        ");
    }
    
/**
Total number of users
*/

    public static function getTotalVolleys( $startDate, $endDate ){
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblChallenges 
        	where added >= ? and added <= ? and is_verify != 1
        ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $params = array( $startDate, $endDate );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        return (int) $stmt->fetchColumn(0);
    }
    
    public static function getTotalVolleyJoins( $startDate, $endDate ){
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblChallengeParticipants 
        	where joined >= unix_timestamp( ? )  and  joined <= unix_timestamp( ? ) ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $params = array( $startDate, $endDate );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        return (int) $stmt->fetchColumn(0);
    }
    
    public static function getTotalUsers( $startDate, $endDate ){
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblUsers 
        	where last_login >= ? and last_login <= ?
        ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $params = array( $startDate, $endDate );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        return (int) $stmt->fetchColumn(0);
    }
    
    public static function getTotalFlagged( $startDate, $endDate ){
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblUsers 
        	where added >= ? and added <= ? and abuse_ct >= 10
        ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $params = array( $startDate, $endDate );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        return (int) $stmt->fetchColumn(0);
    }
    
    public static function getTotalVerified( $startDate, $endDate ){
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblUsers 
        	where added >= ? and added <= ? and abuse_ct <= -10
        ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $params = array( $startDate, $endDate );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        return (int) $stmt->fetchColumn(0);
    }
    
    public static function getVolleyAverages( $startDate, $endDate ){
        $totalUsers = self::getTotalUsers($startDate, $endDate);
        $totalVolleys = self::getTotalVolleys($startDate, $endDate);
        $totalVolleys += self::getTotalVolleyJoins($startDate, $endDate);
        return $totalVolleys / $totalUsers;
    }
    
    public static function getFlagAverages( $startDate, $endDate ){
        $totalUsers = self::getTotalUsers($startDate, $endDate);
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblFlaggedUserApprovals 
        	where added >= unix_timestamp(?) 
        		and added <= unix_timestamp(?) 
        		and flag < 0
        ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $params = array( $startDate, $endDate );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $totalFlags = (int) $stmt->fetchColumn(0);
        return $totalFlags / $totalUsers;
    }
    
    public static function getVerifyAverages( $startDate, $endDate ){
        $totalUsers = self::getTotalUsers($startDate, $endDate);
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblFlaggedUserApprovals 
        	where added >= unix_timestamp(?) 
        		and added <= unix_timestamp(?) 
        		and flag > 0
        ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $params = array( $startDate, $endDate );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $totalFlags = (int) $stmt->fetchColumn(0);
        return $totalFlags / $totalUsers;
    }
        
    public static function getLikeAverages( $startDate, $endDate ){
        $totalUsers = self::getTotalUsers($startDate, $endDate);
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblChallengeVotes 
        	where added >= ? and added <= ?
        ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $params = array( $startDate, $endDate );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $totalVotes = (int) $stmt->fetchColumn(0);
        return $totalVotes / $totalUsers;
    }

/**
Total number of unique daily actives
*/
    public static function getActiveUsers( $type ){
        $sql = "select count(*) from `hotornot-dev`.tblUsers";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $stmt = $dao->prepareAndExecute( $sql );
        return (int) $stmt->fetchColumn(0);
    }

    public static function getCohortCounts(){
        
        $totals = (object) array(
            'selfies_day_1' => 0,
            'joins_day_1' => 0,
            'shouts_day_1' => 0,
            'verifies_up_day_1' => 0,
            'verifies_down_day_1' => 0,
        
            'selfies_week_1' => 0,
            'joins_week_1' => 0,
            'shouts_week_1' => 0,
            'verifies_up_week_1' => 0,
            'verifies_down_week_1' => 0,
        );
        
        $sql = "
        	select u.id, count(*) 
        	from `hotornot-dev`.tblUsers as u 
        		join `hotornot-dev`.tblChallenges as c 
        		on u.id = c.creator_id 
        	where c.is_verify != 1 
        	group by u.id 
        	order by count(*) 
        	desc limit 100
        ";
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        foreach( $ids as $id ){
            $counts = self::getCohortCountForId($id);
            foreach( $counts as $prop => $total ){
                $totals->$prop += $total;
            }
        }
        
        foreach( $totals as $prop => &$count ){
            $count = ( $count / 100 );
        }
        
        echo "
Avg selfies per user during the first day : $totals->selfies_day_1
Avg selfies per user during the first 7 days : $totals->selfies_week_1

Avg joins per user during the first day : $totals->joins_day_1
Avg joins per user during the first 7 days : $totals->joins_week_1

Avg shoutouts per user during the first day : $totals->shouts_day_1
Avg shoutouts per user during the first 7 days : $totals->shouts_week_1

Avg verifies up per user during the first day : $totals->verifies_up_day_1
Avg verifies up per user during the first 7 days : $totals->verifies_up_week_1

Avg verifies down per user during the first day : $totals->verifies_down_day_1
Avg verifies down per user during the first 7 days : $totals->verifies_down_week_1
";
        
    }
    
    public static function getCohortCountForId( $id ){
        $dao = new BIM_DAO_Mysql( BIM_Config::db() );
        
        // get the users reg date
        $sql = "select added from `hotornot-dev`.tblUsers where id = ?";
        $params = array( $id );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $added = $stmt->fetchColumn(0);
        
        // get the total challenges on first day
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblChallenges 
        	where is_verify != 1 
        		and creator_id = ? 
        		and added < DATE_ADD(?,INTERVAL 1 DAY)
        ";
        $params = array( $id, $added );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $challengeCtDay = $stmt->fetchColumn(0);
        
        // get the total challenges in first week
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblChallenges 
        	where is_verify != 1 
        		and creator_id = ? 
        		and added < DATE_ADD(?,INTERVAL 7 DAY)";
        $params = array( $id, $added );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $challengeCtWeek = $stmt->fetchColumn(0);
        
        // get the total joins in first day
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblChallengeParticipants 
        	where user_id = ? 
        		and joined < unix_timestamp(DATE_ADD(?,INTERVAL 1 DAY))
        ";
        $params = array( $id, $added );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $joinsCtDay = $stmt->fetchColumn(0);
        
        // get the total joins in first week
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblChallengeParticipants 
        	where user_id = ? 
        		and joined < unix_timestamp(DATE_ADD(?,INTERVAL 7 DAY))
        ";
        $params = array( $id, $added );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $joinsCtWeek = $stmt->fetchColumn(0);
        
                
        // get the totalshouts in first day
        $sql = "
            select count(*)
            from `hotornot-dev`.tblChallenges as c
                join `hotornot-dev`.tblShoutouts as s
                on c.id = s.challenge_id 
            where c.creator_id = ? 
                and c.added < DATE_ADD(?, INTERVAL 1 DAY)
        ";
        $params = array( $id, $added );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $shoutsCtDay = $stmt->fetchColumn(0);
        
        // get the totalshouts in first day
        $sql = "
        	select count(*)
        	from `hotornot-dev`.tblChallenges as c
        		join `hotornot-dev`.tblShoutouts as s
    	    	on c.id = s.challenge_id 
        	where c.creator_id = ? 
        		and c.added < DATE_ADD(?, INTERVAL 7 DAY)
        ";
        $params = array( $id, $added );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $shoutsCtWeek = $stmt->fetchColumn(0);
        
        // get the total up flags in first day
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblFlaggedUserApprovals 
        	where user_id = ? 
        		and flag = -1
        		and added < unix_timestamp(DATE_ADD(?,INTERVAL 1 DAY))
        ";
        $params = array( $id, $added );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $flagsUpDay = $stmt->fetchColumn(0);
        
        // get the total up flags in first week
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblFlaggedUserApprovals 
        	where user_id = ? 
        		and flag = -1
        		and added < unix_timestamp(DATE_ADD(?,INTERVAL 7 DAY))
        ";
        $params = array( $id, $added );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $flagsUpWeek = $stmt->fetchColumn(0);
        
        // get the total down flags in first day
        $sql = "
        	select count(*) 
        	from `hotornot-dev`.tblFlaggedUserApprovals 
        	where user_id = ? 
        		and flag = 1
        		and added < unix_timestamp(DATE_ADD(?,INTERVAL 1 DAY))
        ";
        $params = array( $id, $added );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $flagsDownDay = $stmt->fetchColumn(0);
        
        // get the total down flags in first week
        $sql = "
            select count(*) 
            from `hotornot-dev`.tblFlaggedUserApprovals 
            where user_id = ? 
                and flag = 1
                and added < unix_timestamp(DATE_ADD(?,INTERVAL 7 DAY))
        ";
        $params = array( $id, $added );
        $stmt = $dao->prepareAndExecute( $sql, $params );
        $flagsDownWeek = $stmt->fetchColumn(0);
        
        return (object) array(
            'selfies_day_1' => $challengeCtDay,
            'joins_day_1' => $joinsCtDay,
            'shouts_day_1' => $shoutsCtDay,
            'verifies_up_day_1' => $flagsUpDay,
            'verifies_down_day_1' => $flagsDownDay,
        
            'selfies_week_1' => $challengeCtWeek,
            'joins_week_1' => $joinsCtWeek,
            'shouts_week_1' => $shoutsCtWeek,
            'verifies_up_week_1' => $flagsUpWeek,
            'verifies_down_week_1' => $flagsDownWeek,
        );
    }
/*
Network wide totals:

    How many users?
    How many Volleys created?
    How many users have been flagged out?
    How many users have been Verified?
    How many Volleys created?
    How many Volley replies?

Engagement:

    What % come back more than once?
    What % came back 3 out of the first 7 days?

Per user averages:

    Avg # of Volleys
    Avg # of people flags
    Avg # of Verifies
    Avg # of Likes

 */

/**
Avg. number of mins per user per day
*/

/**
Number of Volleys create per day total
*/

/**
Number of Volleys per day per user avg.
*/
/**
Number of Joins per day total
*/
/**
Number of Joins per day per user avg.
*/
/**
Number of Verified users total
*/
/**
Number of Verified users per day
*/
/**
Number of Not Verified users total
*/
/**
Number of Not Verified users per day
*/
/**
Numbers of Verify Suspensions total
*/
/**
Numbers of Verify Suspensions per day
*/
/**
Number of flags per day
*/
/**
Number of likes per day total
*/
/**
Number of likes per day per user avg.
*/
/**
Number of subscribes per day total
*/
/**
Number of subscribers per user avg.
*/
/**
Top subscribed user (user with the most subscribed users)
*/
/**
Top subscriptions user (user with the most subscriptions)
 */
    
}