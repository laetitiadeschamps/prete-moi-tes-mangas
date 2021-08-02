<?php

namespace App\EventSubscriber;

use App\Controller\Admin\MessageCrudController;

use App\Entity\Message;
use App\Repository\ChatRepository;
use App\Repository\UserRepository;
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
    private $userRepository;
    

    public function __construct(AdminUrlGenerator $adminUrlGenerator, MailerInterface $mailer, ChatRepository $chatRepository, UserRepository $userRepository)
    {
        
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->mailer = $mailer;
        $this->chatRepository = $chatRepository;
        $this->userRepository=$userRepository;
     
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterEntityPersistedEvent::class => ['sendEmailNew'],
            AfterEntityUpdatedEvent::class => ['sendEmailEdit'],
            BeforeEntityPersistedEvent::class =>['setChat']
        ];
    }

    /**
     * method to set chat Response if you create a message from easyAdmin
     *
     * @param BeforeEntityPersistedEvent $event
     * @return void
     */
    public function setChat(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Message)) {
            return;
        }
        $chat = $this->chatRepository->findOneBy(["title" => "RESPONSE"]);

        $entity->setChat($chat);
       
    }

    /**
     * method to send email when you create a new message admin to a member
     *
     * @param AfterEntityPersistedEvent $event
     * @return void
     */
    public function sendEmailNew(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Message)) {
            return;
        }

        if(isset($_GET['id'])) {
            $recipient = $this->userRepository->find($_GET['id']);
        } else {
            $recipient = $entity->getAuthor();
        }

        
        $email = (new TemplatedEmail())
        
        ->to(new Address($recipient->getEmail()))
        ->subject('KASU Admin : vous avez reçu un message')

        // path of the Twig template to render
        ->htmlTemplate('emails/admin_message.html.twig')

        // pass variables (name => value) to the template
        ->context([
            'message'=> $entity,
            'user' => $recipient,
        ]);
    
        $this->mailer->send($email);

    }

    /**
     * method to send email when admin answer to a member
     *
     * @param AfterEntityUpdatedEvent $event
     * @return void
     */
    public function sendEmailEdit(AfterEntityUpdatedEvent $event): void
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
        ]);

        $this->mailer->send($email);

    }

    }
    

