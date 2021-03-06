<?php

namespace SkNd\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FOS\UserBundle\Model\UserInterface;
use SkNd\UserBundle\Entity\MemoryWall;
use SkNd\UserBundle\Form\Type\MemoryWallType;
use SkNd\MediaBundle\MediaAPI\ProcessDetailsStrategy;
use SkNd\MediaBundle\MediaAPI\ProcessBatchStrategy;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MemoryWallController controls also aspects of showing, editing, deleting memory walls and their content
 * @author Simon Kerr
 * @version 1.0
 */

class MemoryWallController extends Controller
{
    protected $currentUser;
    protected $em;
    protected $userManager;
    
    private function getEntityManager(){
        return $this->container->get('sk_nd_media.mediaapi')->getEntityManager();
    }
        
    protected function getUserManager(){
        return $this->container->get('fos_user.user_manager');
    }
    /**
     * @method indexAction is used to show all public memory walls,
     * public memory walls for a given user, or all private and public 
     * memory walls for an authenticated user
     * @param $scope can either be 'all-public' to show all public walls, or a username to show a specific users walls
     * @return type view
     */
    public function indexAction($scope = 'public')
    {
        $viewParams = $this->getViewParams($scope);
        
        return $this->render('SkNdUserBundle:MemoryWall:index.html.twig', $viewParams);
    }
    
    private function getViewParams($scope){
        $this->userManager = $this->getUserManager();
        $this->em = $this->getEntityManager();
        
        $pageTitle = "";
        
        if($scope == 'public'){
            $pageTitle = "All Public";
            //get all the public memory walls
            $mws = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getPublicMemoryWalls();
        }else{
            $this->currentUser = $this->getCurrentUser();
            if(is_object($this->currentUser) && ($this->currentUser->getUsernameCanonical() == $scope)){
                $pageTitle = 'My';
                //get all public and private walls for this user
                $mws = $this->currentUser->getMemoryWalls();
            }else{
                //get all public walls for the given user
                $user = $this->userManager->findUserByUsername($scope);
                if($user == null || !is_object($user) || !$user instanceof UserInterface)
                    throw $this->createNotFoundException("User not found");
                
                $pageTitle = $scope . htmlentities("'s");
                //call to getMemoryWalls with false indicates only get public walls
                $mws = $user->getMemoryWalls(false);
            }
        }
        
        $viewParams = array(
            'pageTitle'     => $pageTitle,
            'mws'           => $mws,
        );
        
        return $viewParams;
    }
    
    public function personalIndexAction()
    {
        $this->em = $this->getEntityManager();
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $this->get('session')->getFlashBag()->add('notice', 'memoryWall.showOwnWalls.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        return $this->indexAction($this->currentUser->getUsernameCanonical());
    }
    
    public function createAction(Request $request = null){
        $this->em = $this->getEntityManager();
        $session = $this->get('session');
        $this->userManager = $this->getUserManager();
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $session->getFlashBag()->add('notice', 'memoryWall.create.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        $mw = new MemoryWall($this->currentUser);
        $form = $this->createForm(new MemoryWallType(), $mw); 
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            //check form is valid 
            if($form->isValid()){
                $mw = $form->getData();
                $this->currentUser->addMemoryWall($mw);
                $this->userManager->updateUser($this->currentUser);
                
                //persist data and redirect to new memory wall
                $session->getFlashBag()->add('notice', 'memoryWall.create.flash.success');
                return $this->redirect($this->generateUrl('memoryWallShow', array(
                    'id'    => $mw->getId(),
                    'slug'  => $mw->getSlug(),
                    )
                ));
            }            
        }
        return $this->render('SkNdUserBundle:MemoryWall:createMemoryWall.html.twig', array(
            'form'   => $form->createView() 
        ));
    }
    
    public function showAction($id, $slug, $page = 1){
        $this->em = $this->getEntityManager();
        $session = $this->get('session');
        
        $mediaapi = $this->get('sk_nd_media.mediaapi');
        $mw = $this->getMemoryWall($id);
        $wallBelongsToCurrentUser = $this->memoryWallBelongsToUser($mw);
        if(!$mw->getIsPublic() && !$wallBelongsToCurrentUser){
            $session->getFlashBag()->add('notice', 'memoryWall.show.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        //check the mediaresources related to this wall and refresh from api if necessary
        $mediaResources = $mw->getMediaResources();
        if(count($mediaResources) > 0){
            $processStrategy = new ProcessBatchStrategy(array(
                'em'                => $this->em,
                'apis'              => $mediaapi->getAPIs(),
                'mediaResources'    => $mediaResources,
            ));
            $mediaapi->getMedia($processStrategy);
        }
        
        $returnUrl = $this->getRequest()->headers->get('referer');
        if(strpos($returnUrl, 'details') === false && strpos($returnUrl, 'search') === false){
            $returnUrl = null;
        }
                
        return $this->render('SkNdUserBundle:MemoryWall:showMemoryWall.html.twig', array (
            'mw'                        => $mw,
            'wallBelongsToCurrentUser'  => $wallBelongsToCurrentUser,
            'apis'                      => $this->em->getRepository('SkNdMediaBundle:API')->findAll(),
            'returnUrl'                 => $returnUrl,
        ));
    }
    
    public function editAction($id, $slug, Request $request = null){
        $this->em = $this->getEntityManager();
        $session = $this->get('session');
        $mw = $this->getMemoryWall($id);
        
        //if the wall belongs to this user allow edit
        if(!$this->memoryWallBelongsToUser($mw)){
            $session->getFlashBag()->add('notice', 'memoryWall.edit.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        $form = $this->createForm(new MemoryWallType(), $mw); 
        
        if("POST" === $request->getMethod()){
            $form->bindRequest($request);
            //check form is valid 
            if($form->isValid()){
                $mw = $form->getData();
                $this->em->persist($mw);
                $this->em->flush();
                $session->getFlashBag()->add('notice', 'memoryWall.edit.flash.success');
                return $this->redirect($this->generateUrl('memoryWallShow', array(
                    'id'    => $mw->getId(),
                    'slug'  => $mw->getSlug(),
                    )
                ));
            }    
        }
        
        return $this->render('SkNdUserBundle:MemoryWall:editMemoryWall.html.twig', array(
            'form'          => $form->createView(),
            'mw'            => $mw,
        ));
       
    }
    
    public function deleteAction($id, $slug){
        $this->em = $this->getEntityManager();
        $mw = $this->getOwnWall($id);
        $this->get('session')->set('tokens/SkNd-delete-token', true);
        return $this->render('SkNdUserBundle:MemoryWall:deleteMemoryWall.html.twig', array(
            'mw'    =>  $mw,
        ));
       
    }
    
    public function deleteConfirmAction($id, $slug){
        $this->em = $this->getEntityManager();        
        $session = $this->get('session');
        
        //if token wasn't set in delete action, throw exception
        if(!$session->get('tokens/SkNd-delete-token'))
            throw $this->createNotFoundException("Memory wall cannot be deleted");
        else{
            $session->remove('tokens/SkNd-delete-token');
        }
        $mw = $this->getOwnWall($id);
        $this->em->remove($mw);
        
        //if this was the last wall, delete but create a new default one
        if($this->getCurrentUser()->getMemoryWalls()->count() == 1){
            $session->getFlashBag()->add('notice', 'memoryWall.delete.flash.successCreatedDefault');
            $this->getCurrentUser()->createDefaultMemoryWall();
        }else{
            $session->getFlashBag()->add('notice', 'memoryWall.delete.flash.success');
        }
        
        $this->em->flush();
                
        return $this->redirect($this->generateUrl('memoryWallsPersonalIndex'));
    }
    
    /*
     * if there is only 1 wall, add it if ok, else prompt to select a wall to add it to
     * to add a resource, make sure the wall it's to be added to is valid and belongs to the current user
     * then look up the details based on the api and id and add it to the wall
     */
    public function addMediaResourceAction($api, $id, $title, $slug = 'personal', $mwid = '-'){
        $session = $this->get('session');
        $this->em = $this->getEntityManager();
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $session->getFlashBag()->add('notice', 'mediaResource.add.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        //if the user clicked 'add to wall' when not logged in, is redirected back after log in
        if($slug == 'personal'){
            if($this->currentUser->getMemoryWalls()->count() == 1){
                $mw = $this->currentUser->getMemoryWalls()->first();
            }else{
                $session->getFlashBag()->add('notice', 'mediaResource.add.flash.selectWall');
                $viewParams = $this->getViewParams($this->currentUser->getUsernameCanonical());
                $viewParams = array_merge($viewParams, array(
                    'api'   => $api,
                    'id'    => $id,
                    'title' => $title,
                ));
                return $this->render('SkNdUserBundle:MemoryWall:selectMemoryWall.html.twig', $viewParams);
            }
        }else{
            $mw = $this->getOwnWall($mwid, 'mediaResource.add.flash.accessDenied');
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
        $this->em = $this->getEntityManager();
        $mw = $this->getOwnWall($mwid, 'mediaResource.delete.flash.accessDenied');
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
    
    
    private function getOwnWall($id, $exceptionMessage = 'memoryWall.delete.flash.accessDenied'){
        $mw = $this->getMemoryWall($id);
        if(!$this->memoryWallBelongsToUser($mw)){
            $this->get('session')->getFlashBag()->add('notice', $exceptionMessage);
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        return $mw;
    }
    
    private function currentUserIsAuthenticated($user){
        return (is_object($user) && $user instanceof UserInterface);
    }
    
    private function getCurrentUser(){
        return $this->container->get('security.context')->getToken()->getUser();
    }
    
    private function getMemoryWall($id){
        if(!isset($this->em))
            $this->em = $this->getEntityManager();
        
        $mw = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallById($id);
        //if memory wall doesn't exist
        if(is_null($mw))
            throw $this->createNotFoundException("Memory wall not found");
        
        return $mw;
    }
    
    private function memoryWallBelongsToUser(MemoryWall $mw){
        $this->currentUser = $this->getCurrentUser();
        //if the memory wall is private and the selected wall doesn't belong to the current user throw exception
        return $this->currentUserIsAuthenticated($this->currentUser) && $mw->getUser() == $this->currentUser;        
        
    }
}
