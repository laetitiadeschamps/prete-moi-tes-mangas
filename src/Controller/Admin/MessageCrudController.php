<?php

namespace App\Controller\Admin;

use App\Entity\Message;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Component\Validator\Constraints\Choice;

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


    //TODO command creation chat ADMIN (check if it is not already present)
    //TODO supprimer au lieu d'archiver
    //TODO et répondre au lieu d'éditer


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
    
}
