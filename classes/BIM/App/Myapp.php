<?php

class BIM_App_Myapp extends BIM_Controller_Base {
    /*
     * in this function we give an example of 
     * using the config
     * getting the selfieclub user
     * setting and getting data from mysql
     * setting and getting data from memcache
     * setting and getting data from elastic search
     */
    public function iRock( $userId ){
        $return = (object) array();
        
        // get the app config
        // check BIM_Config_Dynamic for the app function
        // we call BIM_Config but we use Dynamic.php to contasin the functions
        // this lets us have a config file that we do not check in
        // and that does not require that we push code to make a config change
        $appConf = BIM_Config::app();
        
        // get the sekfie club user
        // this is how we get all users
        // through the BIM_Model_User class
        // there is a also a BIM_Model_User::getMulti analogue to BIM_Model_User::get
        // that takes multiple ids and returns an array of user models
        $selfieClubUser = BIM_Model_User::get( $appConf->team_volley_id );
        
        // make sure the user actually exists
        if( $selfieClubUser->isExtant() ){
            $return->selfieclub_user = $selfieClubUser;
        } else {
            $return->selfieclub_user = "NO USER with ID: $userId";
        }
        
        // get some data from mysql
        // we use a DAO layer to house all of the SQL
        // 
        // most of the time the useage of a DAO is within a BIM_Model_* class
        // so the usage below is non standard and you should refer to the 
        // BIM_Model_Class code for real examples
        // 
        $dao = new BIM_DAO_Mysql_Myapp( BIM_Config::db() );
        $dao->setData($userId,'mysql -- some data');
        $return->mysql = $dao->getData($userId);
        
        // get some data from elastic search
        // we use a DAO layer to house all of the SQL
        $es = new BIM_DAO_ElasticSearch_Myapp( BIM_Config::elasticSearch() );
        $es->setData($userId,'elastic search  -- some data ');
        $return->elastic_search = $es->getData($userId);
                
        // get some data from mysql
        // we use a DAO layer to house all of the SQL
        $cache = new BIM_Cache( BIM_Config::cache() );
        $data = 'cache -- some data';
        $cache->set('foo', $data );
        $return->cache = $cache->get('foo');
        
        return $return;
    }
}

