<?php

namespace App\EventSubscriber;

use App\Controller\Admin\MessageCrudController;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class EasyAdminSubscriber implements EventSubscriberInterface
{

    private $entityManager;
    

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        
     
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeCrudActionEvent::class => ['getUser'],
            
        ];
    }

   

    /**
     * @param User $entity
     */
    public function getUser(BeforeCrudActionEvent $event): void
    {
        $entity = $event->getAdminContext();
        dd($entity);
  
    }

}