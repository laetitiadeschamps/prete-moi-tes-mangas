<?php

namespace App\Controller\Admin;

use App\Entity\Chat;
use App\Entity\Message;
use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MessageCrudController extends AbstractCrudController
{
    private $adminUrlGenerator;
    private $userRepository;
 

    public function __construct(AdminUrlGenerator $adminUrlGenerator, MessageRepository $messageRepository, UserRepository $userRepository, ChatRepository $chatRepository)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->userRepository = $userRepository;
        
        
    }

    public static function getEntityFqcn(): string
    {
        return Message::class;
    }
    
    /**
     * method to override creationIndexQueryBuilder to get only ADMIN messages
     *
     * @param SearchDto $searchDto
     * @param EntityDto $entityDto
     * @param FieldCollection $fields
     * @param CollectionFilterCollection $filters
     * @return QueryBuilder
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, CollectionFilterCollection $filters): QueryBuilder
    {

        $response = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);


        $response->innerJoin('entity.chat', 'c', 'WITH', 'c.title LIKE :admin')->setParameter(':admin', 'ADMIN')->addSelect('c');
        return $response;
    }


    /**
     * configuration fields of CRUD
     *
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('object', 'Objet')->onlyWhenCreating(),
            TextField::new('object', 'Objet')->OnlyOnIndex(),
            AssociationField::new('author', 'Membre')->onlyOnIndex(),
            AssociationField::new('author', 'Membre')->onlyOnForms()->setFormTypeOption('disabled', 'disabled'),
            AssociationField::new('author', 'Membre')->onlyWhenCreating()->setFormTypeOption('data', isset($_GET['id'])?$this->userRepository->find($_GET['id']):null),
            TextareaField::new('content', 'Message'),
            BooleanField::new('status', 'Traité')->renderAsSwitch(false)->hideOnForm(),
            DateField::new('created_at', 'Date de réception')->hideOnForm(),
        ];
    }

   /**
    * configuration of crud
    *
    * @param Crud $crud
    * @return Crud
    */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Messagerie')
            ->setPageTitle('new', 'Créer un message')
            ->setPageTitle('edit', fn (Message $message) => sprintf('Répondre à <b>%s</b> :', $message->getAuthor()->getPseudo()))
            ->setSearchFields(['object', 'author', 'content']);
    }

    /**
     * configuration of actions and custom actions
     *
     * @param Actions $actions
     * @return Actions
     */
    public function configureActions(Actions $actions): Actions
    {


        $markAsNotTreated = Action::new("marquer comme non-traité")->linkToCrudAction('markAsNotTreated')->setIcon("fas fa-backward")->setLabel(false)->setCssClass('text-dark')->displayIf(static function ($entity) {
            //if status is true, message is read
            return $entity->getStatus();
        });


        //Action when status is true (="traité")
        $archive = Action::new('archiver')->setIcon('fas fa-trash')->setLabel(false)->setCssClass('text-danger')->linkToCrudAction('archive')
            ->displayIf(static function ($entity) {
                //if status is true, message is read and can be archived
                return $entity->getStatus();
            });


        $editMail = Action::new('répondre')->setIcon('fas fa-paper-plane')->setLabel(false)->linkToCrudAction("editMail")->setCssClass("text-primary")
            ->displayIf(static function ($entity) {
                //if status is false, message is unread
                return !$entity->getStatus();
            });

        $markAsTreated = Action::new("Marquer comme traité")->linkToCrudAction('markAsTreated')->setIcon("fas fa-clipboard-check")->setLabel(false)->setCssClass('text-success')->displayIf(static function ($entity) {
            //if status is false, message is unread
            return !$entity->getStatus();
        });


        return $actions
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $archive)
            ->add(Crud::PAGE_INDEX, $editMail)
            ->add(Crud::PAGE_INDEX, $markAsTreated)
            ->add(Crud::PAGE_INDEX, $markAsNotTreated)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-paper-plane')->setLabel('Envoyez un message')->setCssClass('btn bg-black');
            })
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fas fa-paper-plane')->setLabel('Envoyez un message')->setCssClass('btn bg-black');
            })
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fas fa-paper-plane')->setLabel('Envoyez un message')->setCssClass('btn bg-black');
            })
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
    }

    /**
     * method to create a new response admin message for a member request
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

  

        $url = $this->adminUrlGenerator
            ->setController(MessageCrudController::class)
            ->setAction('edit')
            ->setEntityId($message->getId())
            ->generateUrl();
        return $this->redirect($url);
    }
 
    /**
     * method to archive a request
     *
     * @param EntityManagerInterface $em
     * @param AdminContext $context
     * @param ChatRepository $chatRepository
     * @return Response
     */
    public function archive(EntityManagerInterface $em, AdminContext $context, ChatRepository $chatRepository, FlashBagInterface $flashBagInterface): Response
    {

        $url = $this->adminUrlGenerator
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
        //$flashBagInterface->add('success', "Le message a bien été archivé !");

        return $this->redirect($url);
    }

    /**
     * method to change status of a request and mark it as treated
     *
     * @param EntityManagerInterface $em
     * @param AdminContext $context
     * @return Response
     */
    public function markAsTreated(EntityManagerInterface $em, AdminContext $context): Response
    {

        $url = $this->adminUrlGenerator
            ->setController(MessageCrudController::class)
            ->setAction('index')
            ->generateUrl();

        $message = $context->getEntity()->getInstance();
        $message->setStatus(true);
        $em->persist($message);
        $em->flush();

        return $this->redirect($url);
    }

    /**
     * method to change status of a request and mark it as untreated
     *
     * @param EntityManagerInterface $em
     * @param AdminContext $context
     * @return Response
     */
    public function markAsNotTreated(EntityManagerInterface $em, AdminContext $context): Response
    {

        $url = $this->adminUrlGenerator
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
