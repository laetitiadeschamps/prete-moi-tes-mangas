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
    public function byPostCode($zipcode, Localisator $localisator, UserRepository $userRepository)
    {
        $coordinates = $localisator->gpsByZipcode($zipcode);
        extract($coordinates);
       $users = $userRepository->search(2, 43);
       

    }

}