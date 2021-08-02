<?php
namespace App\Controller\Api;

use App\Repository\UserRepository;
use App\Service\Localisator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Routing\Annotation\Route;


/**
*
*/
class SearchController extends AbstractController
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    /**
     * Method to get all users and their collections living within 30km of the zipcode
     *
     * @Route("/api/v1/search/{zipcode}", name="search", requirements={"zipcode"="^[0-9]{5}$"}, methods={"GET"})
     * @param string $zipcode
     * @param Localisator $localisator
     * @param UserRepository $userRepository
     * @return Response
     */
    public function byPostCode(string $zipcode, Localisator $localisator, UserRepository $userRepository): Response
    {
        $coordinates = $localisator->gpsByZipcode($zipcode);
        extract($coordinates);
        //if an error in Localisator is returned :
        if (isset($error)){
            return $this->json($coordinates['error'], 400);
        }

        $users = $userRepository->search($latitude, $longitude);
        
        $arrayResult=[];
        foreach ($users as $user) {
            
            // only users with active status and not in holiday mode
            if ($user->getStatus() == 1 && $user->getHolidayMode() == false) {
                $pseudo = $user->getPseudo();

                $arrayResult[$pseudo]=["user" => $user];
                $arrayResult[$pseudo]["mangas"]=[];

                foreach ($user->getVolumes() as $volume) {

                    $mangaName = $volume->getVolume()->getManga()->getTitle();
                    
                    if (!array_key_exists($mangaName, $arrayResult[$pseudo]["mangas"])) {

                        $arrayResult[$pseudo]["mangas"][$mangaName]["mangaInfo"] = $volume->getVolume()->getManga();
                       
                        $arrayResult[$pseudo]["mangas"][$mangaName]["userVolumes"][] =  $volume;

                    } else {

                        $arrayResult[$pseudo]["mangas"][$mangaName]["userVolumes"][] =  $volume;

                    }
                }
            }
        }
        

        return $this->json($arrayResult, 200, [], [
            'groups'=>'search'
        ]);


    }
}