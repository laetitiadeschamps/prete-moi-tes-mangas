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

   
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('pseudo'),
            TextField::new('firstname')->hideOnIndex(),
            TextField::new('lastname')->hideOnIndex(),
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
            AssociationField::new('volumes')->hideOnIndex(),
            AssociationField::new('volumes')->hideOnForm()->renderAsNativeWidget()
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $user):void
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
        
        $pass = $user->getPassword();

        $user->setPassword(
            $this->passwordEncoder->hashPassword(
                $user,
                $pass
            )
        );
        $entityManager->persist($user);
        $entityManager->flush();   
    } 
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
    }    
}
