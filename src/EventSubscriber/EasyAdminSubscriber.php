<?php

namespace App\EventSubscriber;

use App\Controller\Admin\MessageCrudController;
use App\Entity\Manga;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\ChatRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Translation\TranslatableMessage;

class EasyAdminSubscriber implements EventSubscriberInterface
{

    private $adminUrlGenerator;
    private $mailer;
    private $chatRepository;
    private $userRepository;
    private $flashBagInterface;
    

    public function __construct(AdminUrlGenerator $adminUrlGenerator, MailerInterface $mailer, ChatRepository $chatRepository, UserRepository $userRepository, FlashBagInterface $flashBagInterface)
    {
        
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->mailer = $mailer;
        $this->chatRepository = $chatRepository;
        $this->userRepository=$userRepository;
        $this->flashBagInterface = $flashBagInterface;
     
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterEntityPersistedEvent::class => ['sendEmailNew'],
            AfterEntityPersistedEvent::class => ['flashMessageAfterPersist'],
            AfterEntityUpdatedEvent::class => ['sendEmailEdit'],
            AfterEntityUpdatedEvent::class => ['flashMessageAfterUpdate'],
            AfterEntityDeletedEvent::class => ['flashMessageAfterDelete'],
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
    public function flashMessageAfterPersist(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if ($entity instanceof Message) {
            $message = "Votre message pour " . $entity->getAuthor()->getPseudo() ." a bien été envoyé !";
        }
        if ($entity instanceof User) {
            $message = "L'utilisateur " . $entity->getPseudo() ." a bien été créé !";
        }
        if ($entity instanceof Manga) {
            $message = "Le manga " . $entity->getTitle() . " et ses tomes ont bien été créés !";
        }
        $this->flashBagInterface->add('success', $message);
    }

    public function flashMessageAfterUpdate(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if ($entity instanceof Message) {
            $message = "Votre réponse pour l\'utilisateur " . $entity->getAuthor()->getPseudo() . " a bien été envoyée !";
        }
        if ($entity instanceof User) {
            $message = "L'utilisateur " . $entity->getPseudo() . " a bien été mis à jour !";
        }
        if ($entity instanceof Manga) {
            return;
        }
        $this->flashBagInterface->add('success', $message);
    }

    public function flashMessageAfterDelete(AfterEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if ($entity instanceof Message) {
            $message = "Le message a bien été supprimé !";
        }
        if ($entity instanceof User) {
            $message = "L'utilisateur " . $entity->getPseudo() . " a bien été supprimé !";
        }
        if ($entity instanceof Manga) {
            $message = "Le manga " . $entity->getTitle() . " a bien été supprimé !";
        }
        $this->flashBagInterface->add('success', $message);
    }

    }
    

