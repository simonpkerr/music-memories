<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Base class for all media api's tests
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Tests\MediaAPI;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
//\PHPUnit_Framework_TestCase;

class UtilitiesTests extends PHPUnit_Framework_TestCase {
    private $params;
    private $searchKeywords;
    
    protected function setUp(){
        $this->params = array(
            'decade'    => '1980',
            'media'     => 'film',
            'genre'     => 'all',
        );
        $this->searchKeywords = array(
            'Trap Door Series 1 & 2 [DVD] [1984]'   => 'Trap Door 1984',
            'Stig Of The Dump : Complete BBC Series [1981] [DVD]' => 'Stig Of The Dump 1981',
            'The Chronicles Of Narnia 4 DVD Box Set' => 'The Chronicles Of Narnia',
            'Matrix Trilogy 3-Disc Set: The Matrix, Matrix Reloaded and Matrix Revolutions [DVD]' => 'Matrix Trilogy',
            'Ripping Yarns - The Complete Series[DVD] [1976]' => 'Ripping Yarns 1976',
            'Alien Anthology [Blu-ray] [1979] [6 Disc Set]' => 'Alien Anthology 1979',
            'Saturday Night Fever:25th Anniversary Se [DVD]' => 'Saturday Night Fever:25th Anniversary Se',
            'Prometheus - Special Edition (Blu-ray 3D + Blu-ray + Digital Copy)', 'Prometheus',
            'Chitty Chitty Bang Bang (2 Disc Special Edition) [1968] [DVD]', 'Chitty Chitty Bang Bang 1968',
            'Doctor Who: The Ambassadors of Death [DVD]', 'Doctor Who: The Ambassadors of Death',
        );
    }
    
    public function testKeywordStringProducesFriendlySearchString(){
        //$mediaAPI = new MediaAPI();
        foreach($this->searchKeywords as $originalString => $optimizedString){
            $result = Utilities::formatSearchString(array(
                'keywords'  => $originalString,
                'media'     => 'film',
            ));
            /*$this->assertFalse(strpos($result, 'dvd'), 'dvd string not found');
            $this->assertFalse(strpos($result, 'series'), 'series string not found');
            $this->assertFalse(strpos($result, 'box set'), 'box set string not found');
            $this->assertFalse(strpos($result, 'series'), 'series string not found');*/
            $this->assertEquals($result, strtolower($optimizedString));
            
        }
    }
    
    /*public function testShortKeywordFilmQuery(){
        $mediaAPI = new MediaAPI();
        $this->params['keywords'] = 'Avatar [DVD]';
        
        $result = $mediaAPI->formatSearchString($this->params);
        $this->assertEquals('avatar', $result);
    }
    
    public function testShortKeywordTVQueryForRecentDecade(){
        $mediaAPI = new MediaAPI();
        $this->params = array_merge($this->params, array(
            'keywords'  =>  'Avatar [DVD]',
            'media'     =>  'tv',
            'decade'    =>  date('Y')-10,
            ));
        
        $result = $mediaAPI->formatSearchString($this->params);
        $this->assertEquals('avatar tv', $result);
    }
    
    public function testShortKeywordOldTV(){
        $mediaAPI = new MediaAPI();
        $this->params = array_merge($this->params, array(
            'keywords'  =>  'Avatar [DVD]',
            'media'     =>  'tv',
            'decade'    =>  '1950'
            ));
        
        $result = $mediaAPI->formatSearchString($this->params);
        $this->assertEquals('avatar 1950s tv', $result);
    }
    
    public function testKeywordSearchRemovesSeriesWord(){
        $mediaAPI = new MediaAPI();
        $this->params = array_merge($this->params, array(
            'keywords'  =>  'Trap Door Series 1 & 2',
            'media'     =>  'tv',
            'decade'    =>  '1980'
            ));
        
        $result = $mediaAPI->formatSearchString($this->params);
        $this->assertEquals('trap door 1980s tv', $result);
    }
    
    public function testKeywordSearchRemovesHyphens(){
        $mediaAPI = new MediaAPI();
        $this->params = array_merge($this->params, array(
            'keywords'  =>  'Matrix Trilogy 3-Disc Set',
            'media'     =>  'film',
            'decade'    =>  '1980'
            ));
        
        $result = $mediaAPI->formatSearchString($this->params);
        $this->assertEquals('matrix trilogy', $result);
    }
    
    public function testKeywordSearchRemovesColons(){
        $mediaAPI = new MediaAPI();
        $this->params = array_merge($this->params, array(
            'keywords'  =>  'Stig Of The Dump : Complete BBC Series',
            'media'     =>  'tv',
            'decade'    =>  '1980'
            ));
        
        $result = $mediaAPI->formatSearchString($this->params);
        $this->assertEquals('stig of the dump 1980s tv', $result);
    }
    
    public function testKeywordSearchRemovesSquareBracketedParameters(){
        $mediaAPI = new MediaAPI();
        $this->params = array_merge($this->params, array(
            'keywords'  =>  'Trap Door [DVD] [1984]',
            'media'     =>  'tv',
            'decade'    =>  '1980'
            ));
        
        $result = $mediaAPI->formatSearchString($this->params);
        $this->assertEquals('trap door 1980s tv', $result);
    }
    
    public function testKeywordSearchRemovesBracketedParameters(){
        $mediaAPI = new MediaAPI();
        $this->params = array_merge($this->params, array(
            'keywords'  =>  'Trap Door (full edition)',
            'media'     =>  'tv',
            'decade'    =>  '1980'
            ));
        
        $result = $mediaAPI->formatSearchString($this->params);
        $this->assertEquals('trap door 1980s tv', $result);
    }
    
    public function testKeywordSearchRemovesBoxSetandDVDWords(){
        $mediaAPI = new MediaAPI();
        $this->params = array_merge($this->params, array(
            'keywords'  =>  'The Chronicles Of Narnia 4 DVD Box Set',
            'media'     =>  'tv',
            'decade'    =>  '1980'
            ));
        
        $result = $mediaAPI->formatSearchString($this->params);
        $this->assertEquals('the chronicles of narnia 1980s tv', $result);
    }*/
    

    
    
}

?>
