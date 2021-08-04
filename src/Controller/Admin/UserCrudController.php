<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserVolume;
use App\Entity\Volume;
use App\Form\UserVolumeType;
use App\Form\VolumeType;
use App\Service\Localisator;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\BooleanConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCrudController extends AbstractCrudController
{
    private $passwordEncoder;
    private $adminUrlGenerator;
    private $localisator;

    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    public function __construct( UserPasswordHasherInterface $passwordEncoder, AdminUrlGenerator $adminUrlGenerator, Localisator $localisator)
    {
        $this->passwordEncoder =$passwordEncoder;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->localisator = $localisator;
    }
    public function configureActions(Actions $actions): Actions
    { 
        //Custom action to send an email to a user through the user dashboard
        $sendEmail = Action::new('sendEmail')->setIcon('fas fa-paper-plane')->setLabel(false)->linkToCrudAction('sendEmail')->setCssClass("text-primary");
        return $actions
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->add(Crud::PAGE_EDIT, Action::INDEX)   
            ->add(Crud::PAGE_INDEX, $sendEmail)   
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fas fa-trash')->setLabel(false);
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-plus')->setLabel('Ajouter un utilisateur')->setCssClass('btn bg-black');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fas fa-edit')->setLabel(false)->setCssClass('text-dark');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setCssClass('btn bg-black');
            })
           
            ->update(Crud::PAGE_EDIT, Action::DELETE, function (Action $action) {
                return $action->setIcon('fas fa-trash')->setLabel('Supprimer')->setCssClass('text-danger');
            }) 
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fas fa-save')->setLabel('Sauvegarder')->setCssClass('btn bg-black');
            })
            ->reorder(Crud::PAGE_EDIT, [ Action::DELETE, Action::SAVE_AND_RETURN, Action::INDEX]);
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('new', 'Ajouter un utilisateur')
            ->setPageTitle('edit',fn (User $user) => sprintf('Modifier l\'utilisateur <b>%s</b> :', $user->getPseudo()))
            ->setPageTitle('edit','Modifier')
            ->setPageTitle('index', 'Les utilisateurs')
            ->setFormOptions( ['validation_groups' => ['add']], ['validation_groups' => ['update']] );// Do not validate password on updating a user
    }
   
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('pseudo'),
            TextField::new('firstname')->hideOnIndex(),
            TextField::new('lastname')->hideOnIndex(),
            ImageField::new('pictureUrl', 'Avatar')->hideOnForm(),
            ChoiceField::new('roles')
                ->setLabel("Role")
                ->setChoices([ 
                        'Membre' => 'ROLE_USER',
                        'Admin' => 'ROLE_ADMIN',
                        ])      
                        ->allowMultipleChoices(true)
                        ->renderExpanded(true)
                        ->setFormType(ChoiceType::class)
                        
                       ,           
            BooleanField::new('status', 'Actif')->onlyOnIndex(),
            ChoiceField::new('status', 'Actif')->onlyOnForms()->setChoices([
                'Actif'=>true,
                'Inactif'=>false
            ]),
            TextField::new('email'), 
            TextField::new('password')->onlyWhenCreating()->setFormType(PasswordType::class),
            TextField::new('address', 'Adresse')->hideOnIndex(),
            IntegerField::new('zip_code', 'Code postal')->hideOnIndex(),
            TextField::new('city', 'Ville'),
            AssociationField::new('volumes')->onlyWhenUpdating()->setFormTypeOption('disabled', 'disabled'),
            AssociationField::new('volumes')->hideOnForm()->renderAsNativeWidget()
        ];
    }
    // On creation of a new user, we use the address to fetch coordinates on the API and hash the given password before persisting
    public function persistEntity(EntityManagerInterface $entityManager, $user):void
    {
        if (!($user instanceof User)) {
            return;
        }
        $coordinates = $this->localisator->gpsByAdress($user->getAddress(), $user->getZipCode());
        extract($coordinates);
        
        $user->setLatitude($latitude);
        $user->setLongitude($longitude);
        $pass = $user->getPassword();

        $user->setPassword(
            $this->passwordEncoder->hashPassword(
                $user,
                $pass
            )
        );
        // $this->addFlash('success', 'L\'utilisateur ' . $user->getPseudo() . ' a bien été créé');
        $entityManager->persist($user);
        $entityManager->flush();   
    } 
    // On updating a user, we use the updated address to fetch coordinates on the API before persisting
    public function updateEntity(EntityManagerInterface $entityManager, $user):void
    {
        if (!($user instanceof User)) {
            return;
        }
        
        $coordinates = $this->localisator->gpsByAdress($user->getAddress(), $user->getZipCode());
        extract($coordinates);
        //TODO redirect if no coordinates found
        // if(isset($error)) {
        //     $url = $this->adminUrlGenerator
        //     ->setController(UserCrudController::class)
        //     ->setAction('index')
        //     ->generateUrl();
        //     return $this->redirect($url);
        // }
        $user->setLatitude($latitude);
        $user->setLongitude($longitude);
     
       
        $entityManager->persist($user);
        $entityManager->flush();  
        // $this->addFlash('success', 'L\'utilisateur ' . $user->getPseudo() . ' a bien été mis à jour'); 
    } 
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityManager->remove($entityInstance);
        $entityManager->flush();
        //$this->addFlash('success', 'L\'utilisateur ' . $entityInstance->getPseudo() . ' a bien été supprimé'); 
    }
       
    public function sendEmail()
    {
        $adminUrlGenerator = $this->get(AdminUrlGenerator::class);

        $url = $adminUrlGenerator
            ->setController(MessageCrudController::class)
            ->setAction('new')
            ->unset(EA::ENTITY_ID)
            ->set('id', $_GET['entityId'])
            ->generateUrl();
          
        return $this->redirect($url);
    }
}
