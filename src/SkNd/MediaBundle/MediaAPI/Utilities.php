<?php

/**
 * SearchStringFormatter is a simple class to format titles 
 * from Amazon products for use by YouTube and GDataImages
 *
 * @author meznsi
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
     * 'Trap Door Series 1 & 2 [DVD] [1984]' returns 'trap door'
     * 'Stig Of The Dump : Complete BBC Series [2002] [DVD]' returns 'stig of the dump'
     * 'The Chronicles Of Narnia 4 DVD Box Set' returns 'The Chronicles Of Narnia'
     * 'Matrix Trilogy 3-Disc Set: The Matrix, Matrix Reloaded and Matrix Revolutions [DVD]' returns 'Matrix Trilogy'
     */
    public static function formatSearchString(array $params){
        $keywords = $params['keywords'];
        $media = $params['media'];
        
        //this version removes any references to disc set, box set etc
        preg_match('/[\[|\(](\d{4})[\]|\)]/i', $keywords, $yearParts);
        $yearPart = isset($yearParts[1]) ? $yearParts[1] : null;
        $keywordQuery =  trim(strtolower(preg_replace('/(\w*)(\s?(\(|\[|\d?\sdvd|[\-|\:]?\s?((the\s)?complete|series|box set|bbc|special edition|blu-ray|double pack|Remastered|\d?(\s|\-)?disc set|3d|region free)).*)/i','$1', $keywords)));
        
        //older regexs
        //'/(\w*)(\s?(\(|double pack|(\-\s)?Remastered|\[|\:|\d?\sdvd|(\-\s)?(the\s)?complete|series|box set|bbc|special edition|blu-ray|\d?(\s|\-)?disc set|3d|region free).*)/i'
        //'/(\w*)(\d*)(\[\D*\]|\(\D*\)|(\d{1}\s+|dvd|complete|the complete|series|box set|bbc|special edition|blu-ray|disc set|3d)|(\[|\]|\(|\)|\+|\&|\d{1}\-))/i'
        //'/(\w*)(\d*)(\s{0}(the complete|series|box set|complete|bbc|dvd)\s{0}|(\s??\[(dvd|blu\-ray)\]\s??)|(\d{1}(\s??|\-??)disc set)|(\[)|(\])|(\s??\:\s+?)|(\s??(\&)\s??)|(\s+?\d{1}\s+?))/i'
        //'/(\w*)(\d*)(\s{0}(the complete|series|box set|complete|bbc|dvd)\s{0}|(\s??\[(dvd|blu\-ray)\]\s??)|(\d{1}(\s??|\-??)disc set)|(\[)|(\])|(\s??\:\s+?)|(\s??(\&)\s??)|(\s+?\d{1}\s+?))/i'
        //'/(\w*)(\d*)((\s??\[(dvd|blu\-ray)\]\s??)|(\s??the complete\s??)|(\s??series\s??)|(\s??box set\s??)|\s??(complete\s??)|\s??(\d{1}(\s??|\-??)disc set\s??)|(\s??bbc\s??)|(\s??dvd\s??)|(\[)|(\])|(\s??(\-|\:|\&)\s??)|(\s??\d{1}\s??))/i'
        //'/(\w*)(\d*)((\s??\[dvd\]\s??)|(\s??the complete\s??)|(\s??series\s??)|(\s??box set\s??)|\s??(complete\s??)|\s??(disc set\s??)|(\s??bbc\s??)|(\s??dvd\s??)|(\[)|(\])|(\s??(\-|\:|\&)\s??)|(\s??\d{1}\s??))/i'
        //'/(\w*)(\d*)((\[dvd\])|((\s??(the|complete|series)|box set|complete|disc set|bbc|dvd))|(\[)|(\])|((\-|\:|\&))|(\s?\d{1}\s?))/i'
        //'/(\w*)(\d*)((\sseries.*)|(\s\-)|(\s*\:.*)|(\[[A-Z]+\]\s?)|(\[)|(\])|(\s*Box Set.*)|(\s*\d\s*DVD)|(\s*\d*\-*Disc Set)|(\s*\/.*)|(\()|(\)\s?)|(\s*\([a-z]*\)\s?))/i
        //'/(\w*)(\d*)((\s*the complete)|(\s*series)|(\s*\-)|(\s*\:)|(\[[A-Z]+\])|(\[)|(\])|(\s*Box Set)|(\s*DVD)|(\s*\-*Disc Set)|(\s*\/)|(\()|(\)\s?)|(\s*\([a-z]*\)\s*))/i
        //'/(\w*)(\d*)((\sseries.*)|(\s\-)|(\s*\:.*)|(\[[A-Z]+\]\s?)|(\[)|(\])|(\s*Box Set.*)|(\s*\d\s*DVD)|(\s*\d*\-*Disc Set)|(\s*\/.*)|(\s*\([a-z]*\)))/i'
        //'/(\w*)(\d*)((\sseries.*)|(\s\-.*)|(\s*\:.*)|(\s\[.*\]+.*)|(\s*Box Set.*)|(\s*\d\s*DVD)|(\s*\d*\-*Disc Set)|(\s*\/.*)|(\s*\(.*\)))/i'
        //'/(\w*)(\d*)((\sseries.*)|(\s\-.*)|(\s*\:.*)|(\s\[.*\]+.*)|(\s*Box Set.*)|(\s*\d\s*DVD)|(\s*\d*\-*Disc Set))/i', '$1$2', $keywords)));
        //'/(\w*)(\d*)((\sseries.*)|(\s\-.*)|(\s*\:.*)|(\s\[.*\]+.*)|(\s*Box Set.*)|(\s*\d\s*DVD)|(\s*\d*\-*Disc Set)|(\s*\/.*))/i'
         
        //$keywordQuery .= ' '. $media; 
              
        //add the decade and media tags as keywords if the doing a tv search
        //only add decade to search if the searched for item is older than 20 years
        //will have to experiment and see if decade with 's' on end is better than just decade
        
        /*--------------------------
         * this doesn't appear to help for certain results
         * as videos aren't tagged with all the tags (title decade, media)
         */
        $keywordQuery .= ' ' . $yearPart;
        
        $year = date('Y');
        if(isset($params['decade']) && is_null($yearPart)){
            $decade = $params['decade'];
            if($year - $decade > 20)
                $keywordQuery .= '|' .$decade .'s';
            
            //this still doesn't bring relevant results for moomins (the moomins 1970s tv)
            //need a way to decide relevant results (maybe successive calls to youtube if no results found?)
        
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
    
    /*
     * for media listings and media resource cached records
     * they must be less than 24 hours old to be valid
     * or else be deleted
     */
    public static function getValidCreationTime(){
         $date = new \DateTime("now");
         $date = $date->sub(new \DateInterval('PT24H'))->format("Y-m-d H:i:s");

         return $date;
    }
}

?>
