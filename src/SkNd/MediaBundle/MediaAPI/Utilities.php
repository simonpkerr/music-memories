<?php

/**
 * Utilities formats titles from amazon ready for searching by YouTube or other APIs
 * it also removes null entries from arrays
 * @author Simon Kerr
 * @copyright Simon Kerr 2012
 *
 */
namespace SkNd\MediaBundle\MediaAPI;

class Utilities {
    
    /**
     * this function is needed to optimize titles from amazon products
     * to search youtube and google images
     * 
     * @param array $params contains media, [decade name] and [genre name]
     *  
     * @return string $formattedKeywords
     * @method formatSearchString looks for irrelevant 
     * parts of the keyword search and removes them
     * @example
     * 'Trap Door Series 1 & 2 [DVD] [1984]' returns 'trap door 1984'
     * 'Stig Of The Dump : Complete BBC Series [2002] [DVD]' returns 'stig of the dump 2002'
     * 'The Chronicles Of Narnia 4 DVD Box Set' returns 'The Chronicles Of Narnia'
     */
    public static function formatSearchString(array $params){
        $keywords = $params['keywords'];
        $media = $params['media'];
        
        //this version removes any references to disc set, box set etc
        preg_match('/[\[|\(](\d{4})[\]|\)]/i', $keywords, $yearParts);
        $yearPart = isset($yearParts[1]) ? $yearParts[1] : null;
        $keywordQuery =  trim(strtolower(preg_replace('/(\w*)(\s?(\(|\[|\d?\sdvd|([\-|\:]\s?)((the\s)?complete|series|box set|bbc|special edition|blu-ray|double pack|Remastered|\d?(\s|\-)?disc set|3d|region free)).*|((the\s)?complete)\s?)/i','$1', $keywords)));
        
        //older regexs
        //'/(\w*)(\s?(\(|\[|\d?\sdvd|[\-|\:]?\s?((the\s)?complete|series|box set|bbc|special edition|blu-ray|double pack|Remastered|\d?(\s|\-)?disc set|3d|region free)).*)/i'
         
        if(!is_null($yearPart))
            $keywordQuery .= '|' . $yearPart;
        
        $year = date('Y');
        if(isset($params['decade']) && is_null($yearPart)){
            $decade = $params['decade'];
            if($year - $decade > 20)
                $keywordQuery .= '|' .$decade .'s';
        }
        
        //only add genre if applies to children
        /*if(isset($params['genre'])){
            $genre = $params['genre'];
            if($genre == 'Childrens')
                $keywordQuery .= '|' . $genre;
        }*/
                
       
        return $keywordQuery;
    }
    
    public static function is_NotNull($v){
        return !is_null($v);
    }
    
    public static function removeNullEntries($params){
         return array_filter($params, function($params){
             return !is_null($params);
         }); 
    }
    
}

?>
