<?php

namespace App\Controller\Admin;



use App\Entity\Manga;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\MangaRepository;
use App\Repository\UserRepository;
use App\Repository\UserVolumeRepository;
use App\Repository\VolumeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    private $mangaRepository;
    private $userVolumeRepository;
    private $userRepository;

    public function __construct(MangaRepository $mangaRepository, UserVolumeRepository $userVolumeRepository, UserRepository $userRepository)
    {
        $this->mangaRepository = $mangaRepository;
        $this->userVolumeRepository = $userVolumeRepository; 
        $this->userRepository = $userRepository;  
    }
    /**
     * @Route("/", name="admin")
     */
    public function index(): Response
    {
        //return parent::index();
       
        $mangas = $this->mangaRepository->getCount()['count']; 
        $cities = $this->userRepository->getCityCount()['count'];
        $volumes = $this->userVolumeRepository->getAvailableCount()['count'];
        return $this->render('admin/dashboard.html.twig', [
            'mangas'=>$mangas,
            'cities'=>$cities,
            'volumes'=>$volumes
        ]);
    }
    

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Kasu')
            ->setFaviconPath('images/logo.png');
    }
    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('css/admin.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Mangas', 'fas fa-book', Manga::class);
        yield MenuItem::linkToCrud('Messagerie', 'far fa-envelope', Message::class);
       
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        /** @var User $user */
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            ->setName($user->getPseudo())
            // use this method if you don't want to display the name of the user

            ->setAvatarUrl($user->getPicture())

            ->setMenuItems( [MenuItem::linkToLogout('__ea__user.sign_out', '')->setCssClass('logout')]);
            
    }
    
}
