<?php

namespace App\Controller\Admin;

use App\Entity\Message;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SentMessageCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Message::class;
    }
    
  /**
   * method to override creationIndexQueryBuilder to get only admin messages to members
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


        $response->innerJoin('entity.chat', 'c', 'WITH', 'c.title LIKE :response')->setParameter(':response', 'RESPONSE')->addSelect('c');
        return $response;
    }

    /**
     * configuration crud
     *
     * @param Crud $crud
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Réponses envoyées')
            //->setPageTitle('detail', fn (Message $message) => sprintf('Message envoyé :'))
            ;
    }
    
    /**
     * configuration fields
     *
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            
            AssociationField::new('author', 'Destinataire'),
            TextareaField::new('content', 'Message'),
            DateField::new('created_at', 'Date d\'envoi'),
        ];
    }

    /**
     * customizing actions
     *
     * @param Actions $actions
     * @return Actions
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fas fa-trash')->setLabel(false);
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fas fa-eye')->setLabel(false)->setCssClass('text-dark');
            });
    }
    
}
