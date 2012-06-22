<?php
namespace SkNd\MediaBundle\MediaAPI;
require_once 'Zend/Loader.php';
/*
 * empty class for the Zend_Gdata_YouTube class
 */
class TestYouTubeRequest {
    
    
    public function setMajorProtocolVersion($version){
        return 0;
    }
    
    public function newVideoQuery(){
        return array();
    }
    
    public function getVideoFeed($queryUrl){
        $feed = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\sampleYouTubeListings.xml');
        return $feed;
    }
    
}

?>
