<?php

namespace SkNd\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use SkNd\MediaBundle\MediaAPI\ProcessDetailsStrategy;
use SkNd\UserBundle\Entity\MemoryWallContent;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MemoryWallContentController controls also aspects of showing, editing, deleting memory wall content
 * @author Simon Kerr
 * @version 2.0
 */

class MemoryWallContentController extends Controller
{
    protected $currentUser;
    protected $em;
    protected $mwAccessManager;
    
    private function getMWAccessManager(){
        return $this->container->get('sk_nd_user.mw_access_manager');
    }
    
    private function getEntityManager(){
        return $this->container->get('sk_nd_media.mediaapi')->getEntityManager();
    }
    
    /**
     * 
     * @param type $params (array - mwc (MemoryWallContent), wallBelongsToThisUser (bool)
     * @return type view
     */
    public function showMemoryWallContentAction($params){
        //ugc strategy
        if(is_null($params['mwc']->getMediaResource())){
            return $this->render('SkNdUserBundle:MemoryWallContent:ugcStrategyPartial.html.twig', $params);
        }
        
        switch ($params['mwc']->getMediaResource()->getAPI()->getName()){
            case 'amazonapi' :
                return $this->render('SkNdUserBundle:MemoryWallContent:amazonStrategyPartial.html.twig', $params);
                break;
            case 'youtubeapi' :
                return $this->render('SkNdUserBundle:MemoryWallContent:youTubeStrategyPartial.html.twig', $params);
                break;
        }
        
    }
        
    /*
     * if there is only 1 wall, add it if ok, else prompt to select a wall to add it to
     * to add a resource, make sure the wall it's to be added to is valid and belongs to the current user
     * then look up the details based on the api and id and add it to the wall
     */
    public function addMediaResourceAction($api, $id, $title, $slug = 'personal', $mwid = '-'){
        $this->mwAccessManager = $this->getMWAccessManager();
        $session = $this->get('session');
        $this->currentUser = $this->mwAccessManager->getCurrentUser();
        $this->em = $this->getEntityManager();
        
        if(!$this->mwAccessManager->currentUserIsAuthenticated()){
            $session->getFlashBag()->add('notice', 'mediaResource.add.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        //if the user clicked 'add to wall' when not logged in, is redirected back after log in
        if($slug == 'personal'){
            if($this->currentUser->getMemoryWalls()->count() == 1){
                $mw = $this->currentUser->getMemoryWalls()->first();
            }else{
                $session->getFlashBag()->add('notice', 'mediaResource.add.flash.selectWall');
                $viewParams = $this->mwAccessManager->getViewParams($this->currentUser->getUsernameCanonical());
                $viewParams = array_merge($viewParams, array(
                    'api'   => $api,
                    'id'    => $id,
                    'title' => $title,
                ));
                return $this->render('SkNdUserBundle:MemoryWall:selectMemoryWall.html.twig', $viewParams);
            }
        }else{
            $mw = $this->mwAccessManager->getOwnWall($mwid, 'mediaResource.add.flash.accessDenied');
        }
        
        //look up the detail and add the resource, then show the memory wall
        $mediaapi = $this->get('sk_nd_media.mediaapi');
        //$mediaapi->setAPIStrategy($api);        
        
        $processStrategy = new ProcessDetailsStrategy(array(
            'em'                => $this->em,
            'mediaSelection'    => clone $mediaapi->getMediaSelection(),
            'apiStrategy'       => $mediaapi->getAPIStrategy($api),
            'itemId'            => $id,
            'title'             => $title,
        ));
        
        $mediaResource = $mediaapi->getMedia($processStrategy);
        
        //add the resource to the selected wall
        try{
            $mw->addMediaResource($mediaResource);
            $this->em->flush();
        }catch(\InvalidArgumentException $ex){
            $session->getFlashBag()->add('notice', 'mediaResource.add.flash.identicalResourceError');
            return $this->redirect($this->getRequest()->headers->get('referer'));
        }catch(\RuntimeException $ex){
            $session->getFlashBag()->add('notice', 'mediaResource.add.flash.amazonResourcesThresholdError');
            return $this->redirect($this->getRequest()->headers->get('referer'));
        }
        $session->getFlashBag()->add('notice', 'mediaResource.add.flash.success');
        $session->set('tokens/SkNd-added-resource', true);
        
        return $this->redirect($this->generateUrl('memoryWallShow', array(
            'id'    => $mw->getId(),
            'slug'  => $mw->getSlug(),
            )
        ));
    }
    
    public function deleteMediaResourceAction($mwid, $slug, $id, $confirmed = false){
        $this->mwAccessManager = $this->getMWAccessManager();
        $this->em = $this->getEntityManager();
        $mw = $this->mwAccessManager->getOwnWall($mwid, 'mediaResource.delete.flash.accessDenied');
        try{
            $mr = $mw->getMediaResourceById($id);
        } catch(\InvalidArgumentException $iae) {
            throw $this->createNotFoundException("Media Resource cannot be deleted");
        }
        
        //if the token is set, delete the media resource, else show the confirmation screen
        if($confirmed){ 
            $mw->deleteMediaResourceById($id);
            $this->em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'mediaResource.delete.flash.success');
            return $this->redirect($this->generateUrl('memoryWallShow', array(
                'id'    => $mw->getId(),
                'slug'  => $mw->getSlug(),
                )
            ));

        } else {
            
            /**
             * the likelihood is that delete resource has been selected from the show wall page,
             * in which case, all the media resources will have been cached, so there is no need
             * to look up the resource again
             */
            return $this->render('SkNdUserBundle:MemoryWall:deleteMediaResource.html.twig', array(
                'mw'    => $mw,
                'mr'    => $mr,
            ));
        }
    }
    
}
