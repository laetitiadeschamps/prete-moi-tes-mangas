<?php
namespace App\Controller\Api;

use App\Entity\Manga;
use App\Entity\UserVolume;
use App\Repository\MangaRepository;
use App\Repository\UserRepository;
use App\Repository\UserVolumeRepository;
use App\Repository\VolumeRepository;
use App\Service\Localisator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @Route("/api/v1/search/{zipcode}", name="search", requirements={"zipcode"="^[0-9]{5}$"}, methods={"GET"})
     *
     * @return void
     */
    public function byPostCode($zipcode, Localisator $localisator, UserRepository $userRepository, VolumeRepository $volumeRepository, MangaRepository $mangaRepository)
    {
        $coordinates = $localisator->gpsByZipcode($zipcode);
        extract($coordinates);
        
        $users = $userRepository->search($latitude, $longitude);
        
        //TODO gérer si au moins 1 tome dispo
        $arrayResult=[];
        foreach ($users as $user) {
            
            // only users with active status
            if ($user->getStatus() == 1 && $user->getHolidayMode() == false) {

                $pseudo = $user->getPseudo();

                $arrayResult[$pseudo]=["userId" => $user->getId()];


                foreach ($user->getVolumes() as $volume) {
                    $mangaName = $volume->getVolume()->getManga()->getTitle();

                    if(!array_key_exists($mangaName,$arrayResult[$pseudo])){
                       
                       $arrayResult[$pseudo][$mangaName] = ["mangaInfo" => $volume->getVolume()->getManga()];
                       $arrayResult[$pseudo][$mangaName]["volumes"][] =  $volume;

                    } else {
                        $arrayResult[$pseudo][$mangaName]["volumes"][] =  $volume;
                    }
                    


                //     $arrayResult[$pseudo]["mangas"] = [$volume->getVolume()->getManga()];
                //     $arrayResult[$pseudo]["mangas"]["volumes"]=[];
                //    // array_push($arrayResult[$pseudo]["mangas"]["volumes"][], $volume->getVolume());
                    
                //     $arrayResult[$volume->getVolume()->getManga()->getTitle()]['manga'] = $volume->getVolume()->getManga();
                //     $arrayResult[$volume->getVolume()->getManga()->getTitle()]['users'][$user->getId()]['user']= $user;
                //     $arrayResult[$volume->getVolume()->getManga()->getTitle()]['users'][$user->getId()]['volumes'][]= $volume->getVolume();
                }
            }
        }

        //$users = $userRepository->search(2, 43);

        return $this->json($arrayResult, 200, [], [
            'groups'=>'search'
        ]);

      
        return $this->json($arrayResult, 200, [], [
        'groups' => 'search'
        ]);
    }
}