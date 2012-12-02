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
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use \SimpleXMLElement;

interface IMediaDetails {
    public function getMediaResource();
    
    
}

?>
