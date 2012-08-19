<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * TestAmazonSignedRequest simulates calls to the amazon api used for testing
 * checking for cached versions of details or listings
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\MediaAPI\AmazonSignedRequest;

class TestAmazonSignedRequest extends AmazonSignedRequest{

    public function aws_signed_request($region, $params, $public_key, $private_key, $associate_tag)
    {
        if($params["Operation"] == "ItemSearch"){
            //load sample listings
            $response = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\SampleResponses\sampleAmazonListings.xml');
        }else{
            //load sample details
            $response = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\SampleResponses\sampleAmazonDetails.xml');
        }
        
        return $response;

        
    }
    
    public function execCurl($request){
        
    }
    
    
}
?>
