<?php

/**
 * Utilities formats titles from amazon ready for searching by YouTube or other APIs
 * it also removes null entries from arrays
 * @author Simon Kerr
 * @copyright Simon Kerr 2012
 *
 */
namespace SkNd\MediaBundle\MediaAPI;
use Doctrine\ORM\EntityManager;

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
        $yearParts = array();
        //this version removes any references to disc set, box set etc
        preg_match('/[\[|\(](\d{4})[\]|\)]/i', $keywords, $yearParts);
        $yearPart = isset($yearParts[1]) ? $yearParts[1] : null;
        /*$keywordsRegex = '/
            (\w*)                                   # any number of words
            (^the complete\s)|                      # strings that start with the phrase "the complete" OR
            (\s?(\(|\[|\d?\sdvd                     # 0 or 1 space followed by brackets or square brackets or 0 or 1 digits followed by "dvd"
            |([\-|\:]\s?)?                          # or 0 or 1 "-" or ":" followed by 0 or 1 spaces
            ((the\s)?complete|series|box\sset|bbc   # followed by optional "the" followed by "complete" or "series" or "box set" or "bbc"
            |special\sedition|blu-ray|double\spack  # or "special edition" or "blu-ray" or "double pack"
            |Remastered|\d?(\s|\-)?disc\sset|3d     # or "remastered" or 0 or 1 digit followed by a space or "-" character followed by "disc set" or "3d"
            |region\sfree))                         # or "region free"
            .*)/i/x                                 # followed by any number of characters, globally case insensitive
            ';*/
        $keywordsRegex = '/(\w*)(^the complete\s)|(\s?(\(|\[|\d?\sdvd|([\-|\:]\s?)?((the\s)?complete|series|box\sset|bbc|special\sedition|blu-ray|double\spack|Remastered|\d?(\s|\-)?disc\sset|3d|region\sfree|season\s\d+)).*)/i';
        $keywordQuery =  trim(strtolower(preg_replace($keywordsRegex,'$1', $keywords)));
        
        //older regexs
        //'/(\w*)(\s?(\(|\[|\d?\sdvd|([\-|\:]\s?)((the\s)?complete|series|box set|bbc|special edition|blu-ray|double pack|Remastered|\d?(\s|\-)?disc set|3d|region free)).*|((the\s)?complete)\s?)/i'
        //'/(\w*)(\s?(\(|\[|\d?\sdvd|[\-|\:]?\s?((the\s)?complete|series|box set|bbc|special edition|blu-ray|double pack|Remastered|\d?(\s|\-)?disc set|3d|region free)).*)/i'
         
        //if(!is_null($yearPart))
        //    $keywordQuery .= '|' . $yearPart;
        
        /*$year = date('Y');
        if(isset($params['decade']) && is_null($yearPart)){
            $decade = $params['decade'];
            if($year - $decade > 20)
                $keywordQuery .= '|' .$decade .'s';
        }*/
        
        //only add genre if applies to children
        /*if(isset($params['genre'])){
            $genre = $params['genre'];
            if($genre == 'Childrens')
                $keywordQuery .= '|' . $genre;
        }*/
                
       
        //return $keywordQuery;
        return array(
            'keywords'  => $keywordQuery,
            'year'      => $yearPart,
        );
    }
        
    /*
     * this function expects a url in the slugified format (word-word-word)
     * it looks for a 4 digit number in the url and uses that if the first
     * 2 characters are either 19 or 20
     */
    public static function getDecadeSlugFromUrl($url){
        $yearParts = array();
        preg_match('/\-((19|20)(\d{2}))\-?/i', $url, $yearParts);
        $decade = isset($yearParts[1]) ? $yearParts[1] : null;
        if(!is_null($decade)){
            return substr($decade, 0, 3) . '0s';
        }
    }
    
    public static function is_NotNull($v){
        return !is_null($v);
    }
    
    public static function removeNullEntries($params){
         return array_filter($params, function($params){
             return !is_null($params);
         }); 
    }
    
    public function persistMergeFlush(EntityManager $em, $obj = null, $immediateFlush = true){
        if(!is_null($obj)){
            if($em->contains($obj))
                $em->merge($obj);
            else
                $em->persist($obj);
        }
        
        if($immediateFlush){
            $em->flush();
        }
    }

    
}

?>
