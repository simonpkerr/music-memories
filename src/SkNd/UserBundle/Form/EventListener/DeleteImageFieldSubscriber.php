<?php


/**
 * DeleteImageFieldSubscriber handles the removal
 * of images in UGC entities.
 * If the entity has already been persisted, when editing 
 * the UGC, users should be able to remove the existing image
 * if they want to without selecting another one
 *
 * @author Simon Kerr
 * @copyright (c) 2013, Simon Kerr
 */
namespace SkNd\UserBundle\Form\EventListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeleteImageFieldSubscriber implements EventSubscriberInterface {
    
    public static function getSubscribedEvents() {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }    
    
    public function preSetData(FormEvent $event){
        $data = $event->getData();
        $form = $event->getForm();
        
        //if data, id and image path exists, add the remove image checkbox
        if($data && $data->getId() && $data->getImagePath()){
            $form->add('removeImage', 'checkbox', array(
                'required' => false,
                'label' => 'form.memory_wall.ugc.remove_image',
                'translation_domain' => 'SkNdUserBundle',
            ));
        }
    }
}

?>
