<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaController controls all aspects of connecting to and displaying media
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Form\Type\MediaSelectionType;
use SkNd\MediaBundle\MediaAPI\Utilities;
use SkNd\MediaBundle\MediaAPI\ProcessDetailsStrategy;
use SkNd\MediaBundle\MediaAPI\ProcessDetailsDecoratorStrategy;
use SkNd\MediaBundle\MediaAPI\ProcessListingsStrategy;
use \SimpleXMLElement;

class MediaController extends Controller {

    //handles all calls to the various APIs
    private $mediaapi;

    private function getEntityManager() {
        return $this->getDoctrine()->getEntityManager();
    }

    /*
     * gets the route for searching by
     */

    private function getSearchRoute() {
        $this->mediaapi = $this->get('sk_nd_media.mediaapi');
        $returnRoute = $this->generateUrl('search', $this->mediaapi->getMediaSelectionParams());
        return $returnRoute;
    }

    public function mediaSelectionAction(Request $request = null) {
        $this->mediaapi = $this->get('sk_nd_media.mediaapi');
        $em = $this->mediaapi->getEntityManager();

        /*
         * if the data was posted before and is now saved in the session
         * retrieve it, merge it back into the entity manager (otherwise it 
         * throws the error 'entities must be managed' and use it to populate
         * the form, otherwise just use the empty media selection object
         */
        $mediaSelection = new MediaSelection();
        $sessionFormData = $this->mediaapi->getMediaSelection();
        if ($sessionFormData != null) {

            $mediaType = $sessionFormData->getMediaType();
            $mediaType = $em->merge($mediaType);
            $mediaSelection->setMediaType($mediaType);

            if ($sessionFormData->getDecade() != null) {
                $decade = $sessionFormData->getDecade();
                $decade = $em->merge($decade);
                $mediaSelection->setDecade($decade);
            }

            if ($sessionFormData->getSelectedMediaGenre() != null) {
                $selectedMediaGenre = $sessionFormData->getSelectedMediaGenre();
                $selectedMediaGenre = $em->merge($selectedMediaGenre);
                $mediaSelection->setSelectedMediaGenre($selectedMediaGenre);
            }

            if ($sessionFormData->getKeywords() != null)
                $mediaSelection->setKeywords($sessionFormData->getKeywords());

            $mediaSelection->setGenres($sessionFormData->getGenres());
        }else {
            $genres = $em->getRepository('SkNdMediaBundle:Genre')->getAllGenres();
            $mediaSelection->setGenres($genres);
        }

        $form = $this->createForm(new MediaSelectionType(), $mediaSelection);
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            //$form->bindRequest($request);
            
            if ($form->isValid()) {

                $mediaSelection = $form->getData();
                $this->mediaapi->setMediaSelection(array('mediaSelection' => $mediaSelection));

                return $this->redirect($this->generateUrl('search', $this->mediaapi->getMediaSelectionParams()));
            } else {
                return $this->redirect($this->generateUrl('error'));
            }
        }

        //just returns a partial segment of code to show the form for selecting media
        return $this->render('SkNdMediaBundle:Media:mediaSelectionPartial.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    private function calculatePagingBounds($pagerCount, $currentPage) {
        $pagerUpperBound = $pagerCount * (floor($currentPage / $pagerCount) + 1);
        $pagerLowerBound = $pagerUpperBound - ($pagerCount * 2) <= 0 ? 1 : $pagerUpperBound - ($pagerCount * 2);

        return array(
            'pagerUpperBound' => $pagerUpperBound,
            'pagerLowerBound' => $pagerLowerBound,
        );
    }

    /*
     * perform the search, then redirect to the listings action to show the results
     * search should look up all relevant apis - 
     * film/tv: amazon, youtube, google images (?), wikipedia (?)
     * music: 7digital, youtube, google images (?), wikipedia (?)
     * will most likely be more than 1 api looked up,
     * needs to be able to process multiple apis based on config
     */

    public function searchAction(
    $media, $decade = "all-decades", $genre = "all-genres", $keywords = '-', $page = 1, $api = 'amazonapi') {

        $this->mediaapi = $this->get('sk_nd_media.mediaapi');
        $em = $this->mediaapi->getEntityManager();

        $mediaSelection = $this->mediaapi->setMediaSelection(array(
            'api' => $api,
            'media' => $media,
            'decade' => $decade,
            'genre' => $genre,
            'keywords' => $keywords,
            'page' => $page,
            'computedKeywords' => null,
        ));

        $responseParams = $this->mediaapi->getMediaSelectionParams();

        $processMediaStrategy = new ProcessListingsStrategy(array(
            'em' => $em,
            'mediaSelection' => $mediaSelection,
            'apiStrategy' => $this->mediaapi->getAPIStrategy($api),
        ));

        try {
            $listings = $this->mediaapi->getMedia($processMediaStrategy);
            $responseParams = array_merge($responseParams, $listings);
            $responseParams['pagerParams'] = $this->calculatePagerParams($listings['listings']->getXmlData());

            $responseParams = array_merge($responseParams, $listings);
        } catch (\RunTimeException $re) {
            $this->get('session')->getFlashBag()->add('notice', 'media.amazon.runtime_exception');
        } catch (\LengthException $le) {
            $this->get('session')->getFlashBag()->add('notice', 'media.amazon.length_exception');
        }
        
        return $this->render('SkNdMediaBundle:Media:searchResults.html.twig', $responseParams);
    }

    private function calculatePagerParams(SimpleXMLElement $xmlData) {
        return array(
            'pagerUpperBound' => $xmlData->TotalPages > 10 ? 10 : $xmlData->TotalPages,
            'pagerLowerBound' => 1,
            'totalPages' => $xmlData->TotalPages > 10 ? 10 : $xmlData->TotalPages,
            'pagerRouteParams' => $this->mediaapi->getMediaSelectionParams());
    }

    /* media selection is not set from the details action, only looked up,
     * details looks up api data from all relevant apis for the given item
     * (should this use partials or just process all from the main details action?)
     * 
     */

    public function mediaDetailsAction($id, $api, $title) {
        /*
         * set the mediaSelection object if it doesn't exist - user may have gone straight to the page
         * without going through the selection process
         * 
         * this is unnecessary since pages that are directly linked to should have a record in the db
         * first and if they don't they can be looked up but not inserted, or looked up but without recommendations
         */
        $this->mediaapi = $this->get('sk_nd_media.mediaapi');
        $em = $this->mediaapi->getEntityManager();
        $apiStrategy = $this->mediaapi->getAPIStrategy($api); //entity in class
        $mediaSelection = clone $this->mediaapi->getMediaSelection();
        $mediaSelection->setAPI($apiStrategy->getAPIEntity());
        $referrer = $this->getRequest()->headers->get('referer');

        $responseParams = array_merge(
                $this->mediaapi->getMediaSelectionParams(), array('referrer' => $referrer));

        $processDetailsStrategy = new ProcessDetailsStrategy(array(
            'em' => $em,
            'apiStrategy' => $apiStrategy,
            'mediaSelection' => $mediaSelection,
            'itemId' => $id,
            'title' => $title,
            'referrer' => $referrer,
        ));

        //create the decorator strategy and pass the original strategy to it
        $processDetailsStrategy = new ProcessDetailsDecoratorStrategy(array(
            'processDetailsStrategy' => $processDetailsStrategy,
            'em' => $em,
            'apis' => $this->mediaapi->getAPIs()));

        try {
            $responseParams['mediaResource'] = $this->mediaapi->getMedia($processDetailsStrategy);
        } catch (\RunTimeException $re) {
            $this->get('session')->getFlashBag()->add('notice', 'media.amazon.runtime_exception');
        } catch (\LengthException $le) {
            $this->get('session')->getFlashBag()->add('notice', 'media.amazon.length_exception');
        }

        return $this->render('SkNdMediaBundle:Media:mediaDetails.html.twig', $responseParams);
    }

    /*
     * pass the id of the related item so that a media selection object can be
     * created. 
     */

    public function youTubeRequestAction($title, $mrid) {
        $responseParams = array();

        //get the youtube service
        $this->mediaapi = $this->get('sk_nd_media.mediaapi');
        $em = $this->mediaapi->getEntityManager();

        $apiStrategy = $this->mediaapi->getAPIStrategy('youtubeapi'); //entity in class
        //get media resource id
        $mr = $em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceById($mrid);

        /* this mediaselection is based on the mediaapi one but modified 
         * based on the referenced mediaresource
         */
        $mediaSelection = clone $this->mediaapi->getMediaSelection();
        $mediaSelection->setAPI($apiStrategy->getAPIEntity());
        $mediaSelection->setMediaType($mr->getMediaType());
        $mediaSelection->setDecade($mr->getDecade());
        $mediaSelection->setSelectedMediaGenre($mr->getGenre());
        $mediaSelection->setKeywords(urldecode($title));

        $listings = null;

        $processMediaStrategy = new ProcessListingsStrategy(array(
            'em' => $em,
            'mediaSelection' => $mediaSelection,
            'apiStrategy' => $apiStrategy,
        ));
        try {
            $listings = $this->mediaapi->getMedia($processMediaStrategy);
            //merge the listings and responseParams and remove null entries
            $responseParams = Utilities::removeNullEntries(array_merge($responseParams, $listings));
        } catch (\RuntimeException $re) {
            $this->get('session')->getFlashBag()->add('notice', 'media.youtube.runtime_exception');
        } catch (\LengthException $le) {
            $this->get('session')->getFlashBag()->add('notice', 'media.youtube.length_exception');
        }

        return $this->render('SkNdMediaBundle:Media:youTubePartial.html.twig', $responseParams);
    }

    public function convertMediaAction($media = 'listings', $api = 'amazonapi') {
        $this->mediaapi = $this->get('sk_nd_media.mediaapi');
        $em = $this->mediaapi->getEntityManager();
        $apiStrategy = $this->mediaapi->getAPIStrategy($api);
        $mediaSelection = $this->mediaapi->getMediaSelection();
        if ($media == 'listings') {
            $processMediaStrategy = new ProcessListingsStrategy(array(
                'em' => $em,
                'mediaSelection' => $mediaSelection,
                'apiStrategy' => $apiStrategy,
            ));
        } else {
            $processMediaStrategy = new ProcessDetailsStrategy(array(
                'em' => $em,
                'apiStrategy' => $apiStrategy,
                'mediaSelection' => $mediaSelection,
                'itemId' => 'na',
                'title' => 'na',
            ));
        }

        $this->mediaapi->convertMedia($processMediaStrategy);
    }

}

?>