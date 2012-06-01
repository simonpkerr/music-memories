<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 *
 * Interface for setting strategy of the api to use by the MediaAPI service
 * 7Digital, Amazon, YouTube and GDataImages implement this interface.
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;

interface IAPIStrategy {
    
    public function getListings(MediaSelection $mediaSelection);
    public function getDetails(array $params);
    public function getName();
    //each api implements its own method of getting the id
    public function getId(\SimpleXMLElement $xmlData);
    public function getImageUrlFromXML(\SimpleXMLElement $xmlData);
    public function getItemTitleFromXML(\SimpleXMLElement $xmlData);
   
    
}

?>