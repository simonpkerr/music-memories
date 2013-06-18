<?php

/**
 * handles xml file manipulation, deletion and retrieval
 */
namespace SkNd\MediaBundle\MediaAPI;
use \SimpleXMLElement;

class XMLFileManager {
    private $cache_path;
    
    public function __construct($cache_path){
        $this->cache_path = $cache_path;
    }
    
    public function xmlRefExists($xmlRef){
        return file_exists($this->cache_path . $xmlRef . '.xml');
    }
    
    public function createXmlRef(SimpleXMLElement $xmlData, $apiKey){
        //create the xml file and create a reference to it
        $apiRef = substr($apiKey,0,1);
        $timeStamp = new \DateTime("now");
        $timeStamp = $timeStamp->format("Y-m-d_H-i-s");
        $xmlRef = uniqid('d' . $apiRef . '-' . $timeStamp);
        try {
            $xmlData->asXML($this->cache_path . $xmlRef . '.xml');
        } catch (\Exception $ex) {
            throw new \Exception("error creating cache file for " . $xmlRef);
        }
        
        return $xmlRef;
    }
    
    public function deleteXmlData($xmlRef){
        if($this->xmlRefExists($xmlRef)){
            $f = $this->cache_path . $xmlRef . '.xml';
            try 
            {
                unlink($f);
            } catch(\Exception $e){
                throw new \Exception("error deleting old cached file for " . $xmlRef);
            }
        }
    }
    
    public function getXmlData($xmlRef){
        if(!$this->xmlRefExists($xmlRef))
            throw new \RuntimeException ("error loading cache file, it does not exist");
        
        $f = $this->cache_path . $xmlRef . '.xml';
        try{
            $xmlData = simplexml_load_file($f);
        }catch(\Exception $e) {
            throw new \Exception("error loading xml for " . $xmlRef);
        }
        
        return $xmlData;
    }
    
    
}

?>
