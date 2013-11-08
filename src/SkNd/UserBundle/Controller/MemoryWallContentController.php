<?php

namespace SkNd\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use SkNd\MediaBundle\MediaAPI\ProcessDetailsStrategy;
use SkNd\UserBundle\Entity\MemoryWallUGC;
use Symfony\Component\HttpFoundation\JsonResponse;
use SkNd\UserBundle\Form\Type\MemoryWallUGCType;


/*
 * Original code Copyright (c) 2013 Simon Kerr
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
        if(is_null($params['mwc'])){
            throw new \RuntimeException("required parameters not supplied for showMemoryWallContentAction");
        }
        
        if($params['mwc'] instanceof MemoryWallUGC) {
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
    
    public function deleteContentAction($mwid, $slug, $id, $type, $confirmed = false){
        $this->mwAccessManager = $this->getMWAccessManager();
        $this->em = $this->getEntityManager();
        $mw = $this->mwAccessManager->getOwnWall($mwid, 'memoryWallContent.delete.flash.accessDenied');
        try{
            $mwc = $mw->getMWContentById($id, $type);
        } catch(\InvalidArgumentException $iae) {
            throw $this->createNotFoundException("memoryWallContent.delete.flash.contentNotFound");
        }
        
        //if the token is set, delete the media resource, else show the confirmation screen
        if($confirmed){ 
            $mw->deleteMWContentById($id, $type);
            $this->em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'memoryWallContent.delete.flash.success');
            return $this->redirect($this->generateUrl('memoryWallShow', array(
                'id'    => $mwid,
                'slug'  => $slug,
                )
            ));

        } else {
            
            /**
             * the likelihood is that delete resource has been selected from the show wall page,
             * in which case, all the media resources will have been cached, so there is no need
             * to look up the resource again
             */
            return $this->render('SkNdUserBundle:MemoryWallContent:deleteMWContent.html.twig', array(
                'mw'    => $mw,
                'mwc'   => $mwc,
                'type'  => $type,
            ));
        }
    }
        
    public function addUGCAction($mwid, $slug, Request $request = null){
        $this->mwAccessManager = $this->getMWAccessManager();
        $this->em = $this->getEntityManager();
        $session = $this->get('session');
        //$this->userManager = $this->mwAccessManager->getUserManager();
        //$this->currentUser = $this->mwAccessManager->getCurrentUser();
        $mw = $this->mwAccessManager->getOwnWall($mwid, 'memoryWall.ugc.add.flash.accessDenied');
        
        $mwugc = new MemoryWallUGC(array(
            'mw'    =>  $mw,
        ));
        $form = $this->createForm(new MemoryWallUGCType(), $mwugc); 
        $form->handleRequest($request);
        if($request->getMethod() == 'POST'){
            //$form->bind($request);
            //check form is valid 
            $response = new JsonResponse();
            $content = array();
            
            if($form->isValid()){
                $mwugc = $form->getData();
                $mw->addMemoryWallUGC($mwugc);
                $this->em->flush();                
                $session->getFlashBag()->add('notice', 'memoryWall.ugc.add.flash.success');
                
                //if come from ajax request return json, otherwise redirect to show wall
                if($request->isXmlHttpRequest()){
                    $flash = $session->getFlashBag()->get('notice');
                    $content = $this->render('SkNdUserBundle:MemoryWallContent:ugcStrategyPartial.html.twig', array(
                        'mwc' => $mwugc,
                        'wallBelongsToCurrentUser' => $this->mwAccessManager->memoryWallBelongsToUser($mw),
                    ))->getContent();

                    $response->setData(array(
                        'status'    => 'success',
                        'content'   => $content,
                        'flash'     => $flash,
                    ));
                    return $response;
                } else {
                    return $this->redirect($this->generateUrl('memoryWallShow', array(
                        'id'    => $mw->getId(),
                        'slug'  => $mw->getSlug(),
                        )
                    ));
                }
                
            } else {
                $session->getFlashBag()->add('notice', 'memoryWall.ugc.add.flash.fail');
                if($request->isXmlHttpRequest()){
                    foreach ($form->all() as $formElement) {
                        if(count($formElement->getErrors()) > 0){
                            $e = $formElement->getErrors();
                            $content[$formElement->getName()] = $e[0]->getMessage();

                        }
                    }
                    $response->setData(array(
                        'status'    =>  'fail',
                        'content'   =>  $content,
                        'flash'     => $flash,
                    ));
                    return $response;
                }
            }           
            
        }
        
        return $this->render('SkNdUserBundle:MemoryWallContent:addUGCPartial.html.twig', array(
            'mwid'   => $mwid,
            'slug'   => $slug,
            'form'   => $form->createView(),
        ));
    }
    
    public function editUGCAction($id, $mwid, $slug, Request $request = null){
        $this->mwAccessManager = $this->getMWAccessManager();
        $this->em = $this->getEntityManager();
        $session = $this->get('session');
        //$this->userManager = $this->mwAccessManager->getUserManager();
        //$this->currentUser = $this->mwAccessManager->getCurrentUser();
        $mw = $this->mwAccessManager->getOwnWall($mwid, 'memoryWall.ugc.edit.flash.accessDenied');
        
        $mwugc = $mw->getMWContentById($id, 'ugc');
        $form = $this->createForm(new MemoryWallUGCType(), $mwugc);
        $form->handleRequest($request);
        if($request->getMethod() == 'POST'){
            $response = new JsonResponse();
            $content = array();
            
            if($form->isValid()){
                $mwugc = $form->getData();
                //$mw->addMemoryWallUGC($mwugc);
                $this->em->persist($mwugc);
                $this->em->flush();                
                $session->getFlashBag()->add('notice', 'memoryWall.ugc.edit.flash.success');
                
                //if come from ajax request return json, otherwise redirect to show wall
                if($request->isXmlHttpRequest()){
                    $flash = $session->getFlashBag()->get('notice');
                    $content = $this->render('SkNdUserBundle:MemoryWallContent:ugcStrategyPartial.html.twig', array(
                        'mwc' => $mwugc,
                        'wallBelongsToCurrentUser' => $this->mwAccessManager->memoryWallBelongsToUser($mw),
                    ))->getContent();

                    $response->setData(array(
                        'status'    => 'success',
                        'content'   => $content,
                        'flash' => $flash,
                    ));
                    return $response;
                } else {
                    return $this->redirect($this->generateUrl('memoryWallShow', array(
                        'id'    => $mw->getId(),
                        'slug'  => $mw->getSlug(),
                        )
                    ));
                }
                
            } else {
                $session->getFlashBag()->add('notice', 'memoryWall.ugc.edit.flash.fail');
                if($request->isXmlHttpRequest()){
                    foreach ($form->all() as $formElement) {
                        if(count($formElement->getErrors()) > 0){
                            $e = $formElement->getErrors();
                            $content[$formElement->getName()] = $e[0]->getMessage();

                        }
                    }
                    $response->setData(array(
                        'status'    =>  'fail',
                        'content'   =>  $content,
                        'flash'     =>  $flash,
                    ));
                    return $response;
                }
            }           
            
        }
        
        $content = $this->render('SkNdUserBundle:MemoryWallContent:editUGCPartial.html.twig', array(
            'id'=> $id,
            'mwid'   => $mwid,
            'slug'   => $slug,
            'form'   => $form->createView(),
        ));
        
        //if was called from ajax request
        if($request->isXmlHttpRequest()){
            $response = new JsonResponse();
            $response->setData(array(
                'content' => $content,
            ));
            return $response;
        }
        
        return $content;
    }
}
