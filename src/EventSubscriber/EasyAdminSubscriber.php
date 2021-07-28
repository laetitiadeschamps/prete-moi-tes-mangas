<?php

namespace App\EventSubscriber;

use App\Controller\Admin\MessageCrudController;

use App\Entity\Message;
use App\Repository\ChatRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EasyAdminSubscriber implements EventSubscriberInterface
{

    private $adminUrlGenerator;
    private $mailer;
    private $chatRepository;
    

    public function __construct(AdminUrlGenerator $adminUrlGenerator, MailerInterface $mailer, ChatRepository $chatRepository)
    {
        
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->mailer = $mailer;
        $this->chatRepository = $chatRepository;
     
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterEntityPersistedEvent::class => ['sendEmail'],
            BeforeEntityPersistedEvent::class =>['setChat']
        ];
    }

    public function setChat(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Message)) {
            return;
        }
        $chat = $this->chatRepository->findOneBy(["title" => "RESPONSE"]);

        $entity->setChat($chat);
       
    }

    public function sendEmail(AfterEntityPersistedEvent $event)
    {
        
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Message)) {
            return;
        }

    $email = (new TemplatedEmail())
    
    ->to(new Address($entity->getAuthor()->getEmail()))
    ->subject('KASU Admin : vous avez reçu un message')

    // path of the Twig template to render
    ->htmlTemplate('emails/admin_message.html.twig')

    // pass variables (name => value) to the template
    ->context([
        'message'=> $entity,
        'user' => $entity->getAuthor(),
    ])
;
    $this->mailer->send($email);

    }

}