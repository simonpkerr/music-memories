<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Class to test how the Utilities class transforms Amazon strings ready for searching on YouTube
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Tests\MediaAPI;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
use \PHPUnit_Framework_TestCase;

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
            'Trap Door Series 1 & 2 [DVD] [1984]'   => 'Trap Door',
            'Stig Of The Dump : Complete BBC Series [1981] [DVD]' => 'Stig Of The Dump',
            'The Chronicles Of Narnia 4 DVD Box Set' => 'The Chronicles Of Narnia',
            'Matrix Trilogy 3-Disc Set: The Matrix, Matrix Reloaded and Matrix Revolutions [DVD]' => 'Matrix Trilogy',
            'Ripping Yarns - The Complete Series[DVD] [1976]' => 'Ripping Yarns',
            'Alien Anthology [Blu-ray] [1979] [6 Disc Set]' => 'Alien Anthology',
            'Saturday Night Fever:25th Anniversary Se [DVD]' => 'Saturday Night Fever:25th Anniversary Se',
            'Prometheus - Special Edition (Blu-ray 3D + Blu-ray + Digital Copy)' => 'Prometheus',
            'Chitty Chitty Bang Bang (2 Disc Special Edition) [1968] [DVD]' => 'Chitty Chitty Bang Bang',
            'Doctor Who: The Ambassadors of Death [DVD]' => 'Doctor Who: The Ambassadors of Death',
            'The Complete Open All Hours - Series One-Four [1976]' => 'Open All Hours',
            'Only Fools and Horses Complete Series 1 - 7 Box Set [DVD] [1981]' => 'Only Fools and Horses',
	    'Willy Wonka & the Chocolate Factory (1971) [DVD]' => 'Willy Wonka & the Chocolate Factory',
            'Thundercats: Season Two Vol 2 [DVD] [Region 1] [US Import] [NTSC]' => 'Thundercats: Season Two Vol 2',
        );
    }
    
    public function testKeywordStringProducesFriendlySearchString(){
        foreach($this->searchKeywords as $originalString => $optimizedString){
            $result = Utilities::formatSearchString(array(
                'keywords'  => $originalString,
                'media'     => 'film',
            ));
            $this->assertEquals($result['keywords'], strtolower($optimizedString));
            
        }
    }
 

    
}

?>
