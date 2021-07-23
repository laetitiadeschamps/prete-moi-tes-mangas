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
            BooleanField::new('status', 'Traité')->renderAsSwitch(),
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

        //Action when status is true (="traité")
        $archive = Action::new('Archiver')->setIcon('fas fa-trash')->setLabel(false)->linkToCrudAction('archive')
            ->displayIf(static function ($entity) {
                return $entity->getStatus();
            });
        
        
        $answer = Action::new('Répondre')->setIcon('fas fa-reply')->setLabel(false)->linkToCrudAction('answer')
        ->displayIf(static function ($entity) {
            
            return !$entity->getStatus();

        });
        return $actions->remove(Crud::PAGE_INDEX, Action::DELETE)
                ->add(Crud::PAGE_INDEX, $archive)
                ->add(Crud::PAGE_INDEX, $answer)
                ->remove(Crud::PAGE_INDEX, Action::EDIT)
                ;
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


}
