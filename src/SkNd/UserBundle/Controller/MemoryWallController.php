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
        return $this->get('sk_nd_media.mediaapi')->getEntityManager();
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
            if(is_object($this->currentUser) && ($this->currentUser->getUsername() == $scope)){
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
            $this->get('session')->setFlash('notice', 'memoryWall.showOwnWalls.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        return $this->indexAction($this->currentUser->getUsernameCanonical());
    }
    
    public function createAction(Request $request = null){
        $this->em = $this->getEntityManager();
        $this->userManager = $this->getUserManager();
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $this->get('session')->setFlash('notice', 'memoryWall.create.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        $mw = new MemoryWall();
        $form = $this->createForm(new MemoryWallType(), $mw); 
        if($request->getMethod() == 'POST'){
            $form->bindRequest($request);
            //check form is valid 
            if($form->isValid()){
                $mw = $form->getData();
                $this->currentUser->addMemoryWall($mw);
                $this->userManager->updateUser($this->currentUser);
                
                //persist data and redirect to new memory wall
                $this->get('session')->setFlash('notice', 'memoryWall.create.flash.success');
                return $this->redirect($this->generateUrl('memoryWallShow', array('slug' => $mw->getSlug())));
            }            
        }
        return $this->render('SkNdUserBundle:MemoryWall:createMemoryWall.html.twig', array(
            'form'   => $form->createView() 
        ));
    }
    
    public function showAction($slug, $page = 1){
        $this->em = $this->getEntityManager();
        $mw = $this->getMemoryWall($slug);
        $wallBelongsToCurrentUser = $this->memoryWallBelongsToUser($mw);
        if(!$mw->getIsPublic() && !$wallBelongsToCurrentUser){
            $this->get('session')->setFlash('notice', 'memoryWall.show.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        //check the mediaresources related to this wall and refresh from api if necessary
        $mediaResources = $mw->getMediaResources();
        if(!$mediaResources->isEmpty()){
            $this->get('sk_nd_media.mediaapi')->processMediaResources($mediaResources, $page);
        }
        
        return $this->render('SkNdUserBundle:MemoryWall:showMemoryWall.html.twig', array (
            'mw'                        => $mw,
            //'mediaResources'            => $mediaResources,
            'wallBelongsToCurrentUser'  => $wallBelongsToCurrentUser,
        ));
    }
    
    public function editAction($slug, Request $request = null){
        $this->em = $this->getEntityManager();
        $mw = $this->getMemoryWall($slug);
        $form = $this->createForm(new MemoryWallType(), $mw); 
        
        //if the wall belongs to this user allow edit
        if(!$this->memoryWallBelongsToUser($mw)){
            $this->get('session')->setFlash('notice', 'memoryWall.edit.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }else{
            $this->get('session')->setFlash('notice', '');
        }
        
        if("POST" === $request->getMethod()){
            $form->bindRequest($request);
            //check form is valid 
            if($form->isValid()){
                $mw = $form->getData();
                $this->em->persist($mw);
                $this->em->flush();
                $this->get('session')->setFlash('notice', 'memoryWall.edit.flash.success');
                return $this->redirect($this->generateUrl('memoryWallShow', array('slug' => $mw->getSlug())));
            }    
        }
        
        return $this->render('SkNdUserBundle:MemoryWall:editMemoryWall.html.twig', array(
            'form'          => $form->createView(),
            'slug'          => $mw->getSlug(),
        ));
       
    }
    
    public function deleteAction($slug){
        $this->em = $this->getEntityManager();
        $mw = $this->getOwnWall($slug);
        $this->get('session')->set('tokens/SkNd-delete-token', true);
        return $this->render('SkNdUserBundle:MemoryWall:deleteMemoryWall.html.twig', array(
            'mw'    =>  $mw,
        ));
       
    }
    
    public function deleteConfirmAction($slug){
        $this->em = $this->getEntityManager();        
        //if token wasn't set in delete action, throw exception
        if(!$this->get('session')->get('tokens/SkNd-delete-token'))
            throw $this->createNotFoundException("Memory wall cannot be deleted");
        else{
            $this->get('session')->remove('tokens/SkNd-delete-token');
        }
        $mw = $this->getOwnWall($slug);
        $this->em->remove($mw);
        
        //if this was the last wall, delete but create a new default one
        if($this->getCurrentUser()->getMemoryWalls()->count() == 1){
            $this->get('session')->setFlash('notice', 'memoryWall.delete.flash.successCreatedDefault');
            $this->getCurrentUser()->createDefaultMemoryWall();
        }else{
            $this->get('session')->setFlash('notice', 'memoryWall.delete.flash.success');
        }
        
        $this->em->flush();
                
        return $this->redirect($this->generateUrl('memoryWallsPersonalIndex'));
    }
    
    /*
     * if there is only 1 wall, add it if ok, else prompt to select a wall to add it to
     * to add a resource, make sure the wall it's to be added to is valid and belongs to the current user
     * then look up the details based on the api and id and add it to the wall
     */
    public function addMediaResourceAction($api, $id, $slug = 'personal'){
        $this->em = $this->getEntityManager();
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $this->get('session')->setFlash('notice', 'memoryWall.resources.add.flash.accessDenied');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        //if the user clicked 'add to wall' when not logged in, is redirected back after log in
        if($slug == 'personal'){
            if($this->currentUser->getMemoryWalls()->count() == 1){
                $mw = $this->currentUser->getMemoryWalls()->first();
            }else{
                $this->get('session')->setFlash('notice', 'memoryWall.resources.add.flash.selectWall');
                $viewParams = $this->getViewParams($this->currentUser->getUsernameCanonical());
                $viewParams = array_merge($viewParams, array(
                    'api'   => $api,
                    'id'    => $id,
                ));
                return $this->render('SkNdUserBundle:MemoryWall:selectMemoryWall.html.twig', $viewParams);
            }
        }else{
            $mw = $this->getOwnWall($slug);
        }
        
        //look up the detail and add the resource, then show the memory wall
        $mediaapi = $this->get('sk_nd_media.mediaapi');
        $mediaapi->setAPIStrategy($api);
        $response = $mediaapi->getDetails(array('ItemId'   =>  $id));
        $mediaResource = $mediaapi->getCurrentMediaResource();
        
        //add the resource to the selected wall
        $mw->addMediaResource($mediaResource);
        $this->em->flush();
        
        return $this->redirect($this->generateUrl('memoryWallShow', array('slug' => $mw->getSlug())));
              
    }
    
    private function getOwnWall($slug){
        $mw = $this->getMemoryWall($slug);
        if(!$this->memoryWallBelongsToUser($mw)){
            $this->get('session')->setFlash('notice', 'memoryWall.delete.flash.accessDenied');
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
    
    private function getMemoryWall($slug){
        if(!isset($this->em))
            $this->em = $this->getEntityManager();
        
        $mw = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug($slug);
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
