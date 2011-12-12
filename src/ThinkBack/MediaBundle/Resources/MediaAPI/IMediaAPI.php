<?php

namespace ThinkBack\MediaBundle\Resources\MediaAPI;
/*
 * Interface for connecting to and loading data from API's
 * 7Digital, Amazon, YouTube and Flickr.
 */

/**
 *
 * @author simon kerr
 */
interface IMediaAPI {
    public function getRequest(array $params);
    public function __construct($container = null);
    //public function formatData();
    //public function showData();
    //public function getHost();
    //public function setTags(array $tags);
    
}

?>
