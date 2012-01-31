<?php
namespace ThinkBack\MediaBundle\MediaAPI;
require_once 'Zend/Loader.php';
/*
 * empty class for the Zend_Gdata_YouTube class
 */
class Zend_Gdata_YouTube  {
    
    
    public function setMajorProtocolVersion($version){
        return 0;
    }
    
    public function newVideoQuery(){
        return array();
    }
    
    public function getVideoFeed($queryUrl){
        return array();
    }
    
}

?>
