<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Base class for all media api's tests
 * @author Simon Kerr
 * @version 1.0
 */

namespace ThinkBack\MediaBundle\Tests\MediaAPI;
use ThinkBack\MediaBundle\Resources\MediaAPI\MediaAPI;

class MediaAPITests extends \PHPUnit_Framework_TestCase {
    private $params; 
    
    protected function setUp(){
        $this->params = array(
            'decade'    => '1980',
            'media'     => 'film',
            'genre'     => 'all',
        );
    }
    
    public function testExistingValidCachedListingsReturnedFromSameQuery(){
        
    }
    public function testNullCachedListingsCallsLiveAPI(){
        
    }
    public function testExistingExpiredTimestampCachedListingsCallsLiveAPI(){
        
    }
    public function testMediaTypeAndAPITypeParamsReturnsValidCachedListings(){
        
    }
    public function testMediaTypeAPITypeAndDecadeReturnsValidCachedListings(){
        
    }
    public function testValidParamsIncludingPageReturnsValidCachedListings(){
        
    }
    public function testValidParamsIncludingKeywordsReturnsValidCachedListings(){
        
    }

    
    
}

?>
