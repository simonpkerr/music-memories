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
        $feed = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\SampleResponses\sampleYouTubeListings.xml');
        return $feed;
    }
    
    public function post($data){
        $feed = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\SampleResponses\ytBatchProcess.xml');
        $response = new \Zend_Http_Response();
        $response->fromString($feed->__toString());
        return $response;
    }
    
    public function getVideoEntry($id){
        $data = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\SampleResponses\validYouTubeDetails.xml');
        $ve = new \Zend_Gdata_YouTube_VideoEntry();
        $ve->transferFromXML($data->asXML());
        
        return $ve;
    }
    
}

?>
