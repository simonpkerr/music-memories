<?php

namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\MediaAPI\AmazonSignedRequest;

class TestAmazonSignedRequest extends AmazonSignedRequest{

    public function aws_signed_request($region, $params, $public_key, $private_key, $associate_tag)
    {
        if($params["Operation"] == "ItemSearch"){
            //load sample listings
            $response = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\sampleAmazonListings.xml');
        }else{
            //load sample details
            $response = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\sampleAmazonDetails.xml');
        }
        
        return $response;

        
    }
    
    public function execCurl($request){
        
    }
    
    
}
?>
