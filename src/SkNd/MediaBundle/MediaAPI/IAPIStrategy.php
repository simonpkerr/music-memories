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
use SkNd\MediaBundle\Entity\API;
use \SimpleXMLElement;

interface IAPIStrategy {
    
    public function getAPIEntity();
    public function setAPIEntity(API $entity);
    public function getListings(MediaSelection $mediaSelection);
    public function getDetails(array $params);
    public function getName();
    public function getBatch(array $ids);
    //each api implements its own method of getting the id
    public function getIdFromXML(SimpleXMLElement $xmlData);
    public function getXML(SimpleXMLElement $xmlData);
    public function getImageUrlFromXML(SimpleXMLElement $xmlData);
    public function getItemTitleFromXML(SimpleXMLElement $xmlData);
    public function getDecadeFromXML(SimpleXMLElement $xmlData);
    public function getValidCreationTime();
   
    
}

?>
