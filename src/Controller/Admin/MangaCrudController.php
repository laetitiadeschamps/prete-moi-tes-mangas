<?php

namespace App\Controller\Admin;

use App\Entity\Manga;
use App\Repository\MangaRepository;
use App\Service\JikanApi;
use App\Service\VolumesCreation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Core\User\UserInterface;

class MangaCrudController extends AbstractCrudController
{
    private $jikanApi;
    private $mangaRepository;
    private $volumesCreation;

    public static function getEntityFqcn(): string
    {
        return Manga::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre'),
            TextField::new('author', 'Auteur'),
            ImageField::new('picture', 'Image')->hideOnForm()->setCssClass('manga-img'),
            IntegerField::new('volume_number', 'Nombre de tomes')->hideOnForm(),
            DateField::new('created_at', 'Date de création')->hideOnForm()
        ];
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-plus')->setLabel('Ajouter un manga')->setCssClass('btn bg-black');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setCssClass('btn bg-black');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fas fa-trash')->setLabel(false)->setCssClass('text-danger');
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->setIcon('fas fa-trash')->setLabel('Supprimer')->setCssClass('text-danger');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fas fa-eye')->setLabel(false)->setCssClass('text-dark');
            });
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Ajouter un manga')
            ->setPageTitle('index', 'Mes mangas')
            ->setSearchFields(['title', 'author']);
    }
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('title');
    }
    public function __construct(JikanApi $jikanApi, MangaRepository $mangaRepository, VolumesCreation $volumesCreation)
    {
        $this->jikanApi = $jikanApi;
        $this->mangaRepository = $mangaRepository;
        $this->volumesCreation = $volumesCreation;
    }
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        //When a new manga is created, we call our API to get all other information
        $result = $this->jikanApi->fetch($entityInstance->getTitle());
        $entityInstance->setPicture($result["results"][0]["image_url"]);
        $entityInstance->setVolumeNumber($result["results"][0]["volumes"]);
        $entityInstance->setSynopsis($result["results"][0]["synopsis"]);
        $entityManager->persist($entityInstance);
        $entityManager->flush();
        //Then we create as many entries in the volumes table as the number of volumes sent back by the API
        $manga = $this->mangaRepository->findOneBy(['title' => $entityInstance->getTitle()]);
        $this->volumesCreation->createAll($manga->getId());
    }
}
