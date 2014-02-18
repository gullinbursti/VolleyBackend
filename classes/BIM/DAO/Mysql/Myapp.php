<?php
/**
 * 
 * we use PDO prepared statements
 * and a simple flow
 * 
 * create the query with '?' placeholders for the data
 * build the params array
 * pass them to prepareAndExecute( sql, params )
 * 
 * fetch the data if selecting, according to the PDO docs
 *
 */
class BIM_DAO_Mysql_Myapp extends BIM_DAO_Mysql{
	
	public function setData( $id  ){
	    $added = time();
		$sql = "
			insert into test.test
			(id, added)
			values (?,?)
		";
		$params = array( $id, $added );
		$this->prepareAndExecute($sql,$params);
	}
    
	public function getData( $id ){
		$sql = "
			select * from test.test where id = ?
		";
		$params = array( $id );
		$stmt = $this->prepareAndExecute($sql, $params);
		return $stmt->fetchAll( PDO::FETCH_CLASS, 'stdClass' );
	}
}
