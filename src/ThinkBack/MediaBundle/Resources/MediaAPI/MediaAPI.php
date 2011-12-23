<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Base class for all media api's
 * @author Simon Kerr
 * @version 1.0
 */

namespace ThinkBack\MediaBundle\Resources\MediaAPI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;


class MediaAPI implements IMediaAPI{
    protected $parameters;

    public function getRequest(array $params = null){}
    
    public function __construct($container = null){
        if($container != null){
            $this->parameters = $container->parameters;
        }      
    }
    
    /**
     *
     * @param string $keywords 
     * @return string $formattedKeywords
     * @method formatSearchString looks for irrelevant 
     * parts of the keyword search and removes them
     * @example
     * 'Trap Door Series 1 & 2 [DVD] [1984]' returns 'trap door'
     * 'Stig Of The Dump : Complete BBC Series [2002] [DVD]' returns 'stig of the dump'
     * 'The Chronicles Of Narnia 4 DVD Box Set' returns 'The Chronicles Of Narnia'
     * 'Matrix Trilogy 3-Disc Set: The Matrix, Matrix Reloaded and Matrix Revolutions [DVD]' returns 'Matrix Trilogy'
     */
    public function formatSearchString(array $params){
        $keywords = $params['keywords'];
        $decade = $params['decade'];
        $media = $params['media'];
        $genre = $params['genre'];
        
        //$keywordQuery =  trim(strtolower(preg_replace('/(\w*)(\d*)((\sseries.*)|(\s\-.*)|(\s*\:.*)|(\s\[.*\]+.*)|(\s*Box Set.*)|(\s*\d\s*DVD))/i', '$1$2', $keywords)));
        
        //this version removes any references to disc set, box set etc
        $keywordQuery =  trim(strtolower(preg_replace('/(\w*)(\d*)((\sseries.*)|(\s\-.*)|(\s*\:.*)|(\s\[.*\]+.*)|(\s*Box Set.*)|(\s*\d\s*DVD)|(\s*\d*\-*Disc Set)|(\s*\/.*)|(\s*\(.*\)))/i','$1$2', $keywords)));
        //older regexs
        //'/(\w*)(\d*)((\sseries.*)|(\s\-.*)|(\s*\:.*)|(\s\[.*\]+.*)|(\s*Box Set.*)|(\s*\d\s*DVD)|(\s*\d*\-*Disc Set))/i', '$1$2', $keywords)));
        //'/(\w*)(\d*)((\sseries.*)|(\s\-.*)|(\s*\:.*)|(\s\[.*\]+.*)|(\s*Box Set.*)|(\s*\d\s*DVD)|(\s*\d*\-*Disc Set)|(\s*\/.*))/i'
        
              
        //add the decade and media tags as keywords if the doing a tv search
        //only add decade to search if the searched for item is older than 20 years
        //will have to experiment and see if decade with 's' on end is better than just decade
        $year = date('Y');
        if($media == 'tv'){
            $decade < $year - 20 ? $keywordQuery .= ' ' . $decade . 's ' . $media : $keywordQuery .= ' '. $media;
        }
        
        //$keywordQuery = urlencode($keywordQuery);
        
        return $keywordQuery;
    }
    
    
}


?>
