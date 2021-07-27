<?php

namespace App\EventSubscriber;

use App\Controller\Admin\MessageCrudController;
use App\Entity\Message;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
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
    

    public function __construct(AdminUrlGenerator $adminUrlGenerator, MailerInterface $mailer)
    {
        
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->mailer = $mailer;
     
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterEntityUpdatedEvent::class => ['sendEmail'],
            
        ];
    }


    public function sendEmail(AfterEntityUpdatedEvent $event)
    {
        
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Message)) {
            return;
        }
        // $email = (new Email())
        //     ->to($entity->getAuthor()->getEmail())
        //     ->subject('KASU Admin : vous avez reçu un message')
        //     ->text($entity->getContent());

        // $this->mailer->send($email);
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