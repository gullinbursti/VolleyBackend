<?php 

class BIM_DAO_ElasticSearch_Myapp extends BIM_DAO_ElasticSearch {
    
    public function setData( $id, $data ){
        $added = false;
        $data = array(
            'id' => $id,
            'data' => $data
        );
        $urlSuffix = "test/testdocs/$id/_create";
        $added = $this->call('PUT', $urlSuffix, $data);
        
        $added = json_decode( $added );
        if( isset( $added->ok ) && $added->ok ){
            $added = true;
        } else {
            $added = false;
        }
        return $added;
    }
    
    public function getData( $id ){
        $urlSuffix = "test/testdocs/$id";
        $data = $this->call('GET', $urlSuffix);
        $data = json_decode( $data );
        return $data;
    }
    
}