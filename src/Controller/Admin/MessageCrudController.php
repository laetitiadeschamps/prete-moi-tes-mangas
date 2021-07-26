<?php

namespace App\Controller\Admin;

use App\Entity\Chat;
use App\Entity\Message;
use App\Repository\ChatRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class MessageCrudController extends AbstractCrudController
{

    private $adminUrlGenerator;
    

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
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
            TextField::new('object', 'Objet'),
            TextField::new('author', 'Auteur'),
            TextareaField::new('content', 'Message'),
            BooleanField::new('status', 'Traité')->renderAsSwitch(false),
            DateField::new('created_at', 'Date de réception')->hideOnForm(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Messagerie')
            
            ->setSearchFields(['object', 'author', 'content']);
    }

    public function configureActions(Actions $actions): Actions
    {
        

        $markAsNotTreated = Action::new("marquer comme non-traité")->linkToCrudAction('markAsNotTreated')->setIcon("fas fa-backward")->setLabel("Annuler")->setCssClass('text-dark')->displayIf(static function ($entity) {
            //if status is true, message is readen
            return $entity->getStatus();
        });

        //Action when status is true (="traité")
        $archive = Action::new('archiver')->setIcon('fas fa-trash')->setLabel("Archiver")->setCssClass('text-danger')->linkToCrudAction('archive')
            ->displayIf(static function ($entity) {
                //if status is true, message is read and can ben archived
                return $entity->getStatus();
            });
        
        
        $answer = Action::new('répondre')->setIcon('fas fa-reply')->setLabel("Répondre")->linkToCrudAction('answer')->setCssClass("text-primary")
        ->displayIf(static function ($entity) {
            //if status is false, message is unread
            return !$entity->getStatus();

        });
        $markAsTreated = Action::new("Marquer comme traité")->linkToCrudAction('markAsTreated')->setIcon("fas fa-clipboard-check")->setLabel("Traité?")->setCssClass('text-success')->displayIf(static function ($entity) {
            //if status is false, message is unread
            return !$entity->getStatus();
        });

        return $actions->remove(Crud::PAGE_INDEX, Action::DELETE)
                ->add(Crud::PAGE_INDEX, $archive)
                ->add(Crud::PAGE_INDEX, $answer)
                ->add(Crud::PAGE_INDEX, $markAsTreated)
                ->add(Crud::PAGE_INDEX, $markAsNotTreated)
                ->remove(Crud::PAGE_INDEX, Action::EDIT)
                ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                    return $action->setIcon('fas fa-paper-plane')->setLabel('Envoyez un message')->setCssClass('btn bg-black');});
              
    
    }

    public function archive(EntityManagerInterface $em, AdminContext $context, ChatRepository $chatRepository) :Response
    {
        $adminUrlGenerator = $this->get(AdminUrlGenerator::class);

        $url = $adminUrlGenerator
        ->setController(MessageCrudController::class)
        ->setAction('index')
        ->generateUrl();

        $message = $context->getEntity()->getInstance();
        $chat = $chatRepository->findOneBy(["title"=>"ARCHIVE"]);
        
        if (!$chat){
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

    public function markAsTreated(EntityManagerInterface $em, AdminContext $context) :Response
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

    public function markAsNotTreated(EntityManagerInterface $em, AdminContext $context) :Response
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
