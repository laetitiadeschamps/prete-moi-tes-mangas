<?php

namespace App\Controller\Admin;

use App\Entity\Chat;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MessageCrudController extends AbstractCrudController
{

    private $adminUrlGenerator;



    public function __construct(AdminUrlGenerator $adminUrlGenerator, MessageRepository $messageRepository, MailerInterface $mailer )
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Message::class;
    }
    
    /**
     * method to override creationIndexQueryBuilder to get only ADMIN messages
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, CollectionFilterCollection $filters): QueryBuilder
    {

        $response = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);


        $response->innerJoin('entity.chat', 'c', 'WITH', 'c.title LIKE :admin')->setParameter(':admin', 'ADMIN')->addSelect('c');
        return $response;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('object', 'Objet')->hideOnForm(),
            AssociationField::new('author', 'Membre')->hideOnForm(),
            TextareaField::new('content', 'Message'),
            BooleanField::new('status', 'Traité')->renderAsSwitch(false)->hideOnForm(),
            DateField::new('created_at', 'Date de réception')->hideOnForm(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Messagerie')
            //->setPageTitle('edit', fn (Message $message) => sprintf('Répondre à <b>%s</b> :', $message->getAuthor()->getPseudo()))
            ->setPageTitle('edit', 'Répondre')
            ->setSearchFields(['object', 'author', 'content']);
    }

    public function configureActions(Actions $actions): Actions
    {


        $markAsNotTreated = Action::new("marquer comme non-traité")->linkToCrudAction('markAsNotTreated')->setIcon("fas fa-backward")->setLabel(false)->setCssClass('text-dark')->displayIf(static function ($entity) {
            //if status is true, message is readen
            return $entity->getStatus();
        });


        //Action when status is true (="traité")
        $archive = Action::new('archiver')->setIcon('fas fa-trash')->setLabel(false)->setCssClass('text-danger')->linkToCrudAction('archive')
            ->displayIf(static function ($entity) {
                //if status is true, message is read and can ben archived
                return $entity->getStatus();
            });


        $editMail = Action::new('répondre')->setIcon('fas fa-reply')->setLabel(false)->linkToCrudAction("editMail")->setCssClass("text-primary")
            ->displayIf(static function ($entity) {
                //if status is false, message is unread
                return !$entity->getStatus();
            });

        $markAsTreated = Action::new("Marquer comme traité")->linkToCrudAction('markAsTreated')->setIcon("fas fa-clipboard-check")->setLabel(false)->setCssClass('text-success')->displayIf(static function ($entity) {
            //if status is false, message is unread
            return !$entity->getStatus();
        });


        return $actions->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $archive)
            ->add(Crud::PAGE_INDEX, $editMail)
            ->add(Crud::PAGE_INDEX, $markAsTreated)
            ->add(Crud::PAGE_INDEX, $markAsNotTreated)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-paper-plane')->setLabel('Envoyez un message')->setCssClass('btn bg-black');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fas fa-paper-plane')->setLabel('Envoyez un message')->setCssClass('btn bg-black');
            })
            
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
    }

    /**
     * method to create a new response admin message
     *
     * @param EntityManagerInterface $em
     * @param AdminContext $context
     * @return void
     */
    public function editMail(EntityManagerInterface $em, AdminContext $context, ChatRepository $chatRepository)
    {
        $message = new Message();

        $userToReplyTo = $context->getEntity()->getInstance()->getAuthor();
        $message->setAuthor($userToReplyTo);
        $message->setContent("  ");

        $chat = $chatRepository->findOneBy(["title" => "RESPONSE"]);

        if (!$chat) {
            $chat = new Chat();
            $chat->setTitle("RESPONSE");
            $em->persist($chat);
            $em->flush();
        }
        $message->setChat($chat);
        $em->persist($message);
        $em->flush();


        $adminUrlGenerator = $this->get(AdminUrlGenerator::class);

        $url = $adminUrlGenerator
            ->setController(MessageCrudController::class)
            ->setAction('edit')
            ->setEntityId($message->getId())
            ->generateUrl();
        return $this->redirect($url);
    }
 
    public function archive(EntityManagerInterface $em, AdminContext $context, ChatRepository $chatRepository): Response
    {
        $adminUrlGenerator = $this->get(AdminUrlGenerator::class);

        $url = $adminUrlGenerator
            ->setController(MessageCrudController::class)
            ->setAction('index')
            ->generateUrl();

        $message = $context->getEntity()->getInstance();
        $chat = $chatRepository->findOneBy(["title" => "ARCHIVE"]);

        if (!$chat) {
            $chat = new Chat();
            $chat->setTitle("ARCHIVE");
            $em->persist($chat);
            $em->flush();
        }
        $message->setChat($chat);
        $em->persist($message);
        $em->flush();

        return $this->redirect($url);
    }

    public function markAsTreated(EntityManagerInterface $em, AdminContext $context): Response
    {
        $adminUrlGenerator = $this->get(AdminUrlGenerator::class);

        $url = $adminUrlGenerator
            ->setController(MessageCrudController::class)
            ->setAction('index')
            ->generateUrl();

        $message = $context->getEntity()->getInstance();
        $message->setStatus(true);
        $em->persist($message);
        $em->flush();

        return $this->redirect($url);
    }

    public function markAsNotTreated(EntityManagerInterface $em, AdminContext $context): Response
    {
        $adminUrlGenerator = $this->get(AdminUrlGenerator::class);

        $url = $adminUrlGenerator
            ->setController(MessageCrudController::class)
            ->setAction('index')
            ->generateUrl();

        $message = $context->getEntity()->getInstance();
        $message->setStatus(false);
        $em->persist($message);
        $em->flush();

        return $this->redirect($url);
    }

}
