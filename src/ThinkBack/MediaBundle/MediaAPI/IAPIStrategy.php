<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 *
 * Interface for setting strategy of the api to use by the MediaAPI service
 * 7Digital, Amazon, YouTube and GDataImages implement this interface.
 * @author Simon Kerr
 * @version 1.0
 */

namespace ThinkBack\MediaBundle\MediaAPI;
use ThinkBack\MediaBundle\Entity\MediaSelection;

interface IAPIStrategy {
    
    public function getListings(MediaSelection $mediaSelection);
    public function getDetails(array $params, array $searchParams);
    public function getName();
}

?>
