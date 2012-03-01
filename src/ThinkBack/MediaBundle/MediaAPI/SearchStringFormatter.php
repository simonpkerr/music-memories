<?php

/**
 * SearchStringFormatter is a simple class to format titles 
 * from Amazon products for use by YouTube and GDataImages
 *
 * @author meznsi
 * @copyright Simon Kerr 2012
 *
 */
namespace ThinkBack\MediaBundle\MediaAPI;

class SearchStringFormatter {
    
    /**
     * this function is needed to optimize titles from amazon products
     * to search youtube and google images
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
        
        /*--------------------------
         * this doesn't appear to help for certain results
         * as videos aren't tagged with all the tags (title decade, media)
         */
        //$year = date('Y');
        //if($media == 'tv' && !isset($params['secondarySearch'])){
        //    $decade < $year - 20 ? $keywordQuery .= ' ' . $decade . 's ' . $media : $keywordQuery .= ' '. $media;
            
            //this still doesn't bring relevant results for moomins (the moomins 1970s tv)
            //need a way to decide relevant results (maybe successive calls to youtube if no results found?)
        //}
        $keywordQuery .= ' '. $media; 
        
        //---- MAYBE look at the bracketed part of a title and check to see if a year is supplied and use that in the search.
        
        //$keywordQuery = urlencode($keywordQuery);
        
        return $keywordQuery;
    }
}

?>
