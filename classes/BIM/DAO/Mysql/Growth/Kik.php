<?php

class BIM_DAO_Mysql_Growth_Kik extends BIM_DAO_Mysql_Growth{
	
    /*
            [id] => 11449762
            [created_at] => 2013-12-04T22:12:44.697Z
            [username] => blugar
            [name] => Brandon  
            [avatar] => //d33vud085sp3wg.cloudfront.net/k3Ked34SXVNPf7QZc608Zzaim4w/thumb.jpg
            [shout_pic] => http://uploads.heyhey.koa.la/1386195163865blugar.jpg
            [heys] => 0
            [thumb] => http://uploads.heyhey.koa.la/1386195163865blugar_thumb.jpg
            [deleted] => 
            [inappropriate] => 
            [locked] => 1
            [cleared] => 1
            [locked_by] => 28
            [seconds_left] => 3496
     */
    
    public function saveKikUser( $data ){
        $sql = "
        	INSERT IGNORE INTO growth.kik_users
        	(id,username,avatar,shout_pic,created_at,network)
        	values
        	(?,?,?,?,?,?)
        ";
        $params = array( $data->id, $data->username, $data->avatar, $data->shout_pic, $data->created_at, $data->network );
        $this->prepareAndExecute( $sql, $params );
    }
    
}
