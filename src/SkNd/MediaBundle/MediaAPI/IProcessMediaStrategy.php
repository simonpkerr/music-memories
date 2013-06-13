<?php

/*
 * Original code Copyright (c) 2012 Simon Kerr
 *
 * Interface for MediaDetails, looking up details for an item from the media api
 * functionality is either to look up a single item or a batch of items.
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\MediaBundle\MediaAPI;
use \SimpleXMLElement;

interface IProcessMediaStrategy {
    public function processMedia();
    public function cacheMedia();
    public function getAPIData();
    public function getMedia();
    public function persistMergeFlush($obj = null, $immediateFlush = true);
    public function createXMLRef(SimpleXMLElement $xmlData, $apiKey);
    
}

?>
