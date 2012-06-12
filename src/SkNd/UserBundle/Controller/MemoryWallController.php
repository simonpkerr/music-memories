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

class MemoryWallController extends Controller
{
    protected $currentUser;
    protected $em;
    protected $userManager;
    
    private function getEntityManager(){
        return $this->getDoctrine()->getEntityManager();
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
        
        return $this->render('SkNdUserBundle:MemoryWall:index.html.twig', $viewParams);
    }
    
    public function personalIndexAction()
    {
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $this->get('session')->setFlash('notice', 'Naughty naughty. You have to log in first to see your own Memory Walls');
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        return $this->indexAction($this->currentUser->getUsernameCanonical());
    }
    
    public function createAction(Request $request = null){
        $this->userManager = $this->getUserManager();
        $this->currentUser = $this->getCurrentUser();
        if(!$this->currentUserIsAuthenticated($this->currentUser)){
            $this->get('session')->setFlash('notice', memoryWall.create.flash.accessDenied);
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
                $this->get('session')->setFlash('notice', 'Success! Now, get started adding loads of cool stuff to your new Memory Wall');
                return $this->redirect($this->generateUrl('memoryWallShow', array('slug' => $mw->getSlug())));
            }            
        }
        return $this->render('SkNdUserBundle:MemoryWall:createMemoryWall.html.twig', array(
            'form'   => $form->createView() 
        ));
    }
    
    public function showAction($slug){
        $this->em = $this->getEntityManager();
        $this->currentUser = $this->getCurrentUser();
        $mw = $this->em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug($slug);
        //if memory wall doesn't exist
        if(is_null($mw))
            throw $this->createNotFoundException("Memory wall not found");
        
        //if the memory wall is private and the selected wall doesn't belong to the current user throw exception
        $wallBelongsToCurrentUser = $this->currentUserIsAuthenticated($this->currentUser) && $mw->getUser() == $this->currentUser;        
        if(!$mw->getIsPublic() && !$wallBelongsToCurrentUser){
            $this->get('session')->setFlash('notice', memoryWall.show.flash.accessDenied);
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        return $this->render('SkNdUserBundle:MemoryWall:showMemoryWall.html.twig', array (
            'memoryWall'                => $mw,
            'wallBelongsToCurrentUser'  => $wallBelongsToCurrentUser,
        ));
    }
    
    private function currentUserIsAuthenticated($user){
        return (is_object($user) && $user instanceof UserInterface);
    }
    
    private function getCurrentUser(){
        return $this->container->get('security.context')->getToken()->getUser();
    }
}
