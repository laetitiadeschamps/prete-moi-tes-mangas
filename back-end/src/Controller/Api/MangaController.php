<?php

namespace App\Controller\Api;

use App\Entity\Manga;
use App\Repository\MangaRepository;
use App\Repository\UserRepository;
use App\Repository\VolumeRepository;
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
* @Route("/api/v1", name="api_manga-", requirements={"id"="\d+", "mangaId"="\d+"})
*/
class MangaController extends AbstractController
{
    private $mangaRepository;
    private $volumeRepository;
    private $em;
    private $userRepository;

    public function __construct(MangaRepository $mangaRepository, VolumeRepository $volumeRepository, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->mangaRepository = $mangaRepository;
        $this->volumeRepository = $volumeRepository;
        $this->userRepository = $userRepository;
        $this->em=$em;
    }
    /**
     * Method allowing to fetch all mangas
     * @Route("/manga", name="list", methods={"GET"})
     */
    public function list(): Response
    {
        return $this->json($this->mangaRepository->findAll(), 200, [], [
            'groups' => 'mangas'
        ]);
    }
    /**
     * Method allowing to add a manga into a user's collection
     * @Route("/user/{id}/manga", name="add", methods={"POST"})
     */
    public function add(int $id, Request $request): Response
    {
     
        //We find the manga that was selected and its attached volumes   
        $manga = $this->mangaRepository->findOneBy(['title'=>$request->get('title')]);
       
        $volumes = $this->volumeRepository->findSelectedVolumes($manga->getId(),$request->get('volumes'));

        foreach($volumes as $volume) {
            $volume->addUsers($this->userRepository->find($id));
        }
        $this->em->flush();

        return $this->json("Le manga". $manga->getTitle() . "a été ajouté à votre collection", 201);
        
    }
    /**
     * @Route("/user/{id}/manga/{mangaId}", name="update", methods={"PUT|PATCH"})
     */
    public function update(int $mangaId): Response
    {
        
        return $this->json("Votre collection a bien été mise à jour", 200);   
    }
    /**
     * @Route("/user/{id}/manga/{mangaId}", name="delete", methods={"DELETE"})
     */
    public function delete(int $id, int $mangaId): Response
    {
         //We find the manga that was selected and its attached volumes   
         $manga = $this->mangaRepository->find($mangaId);
       
         $volumes = $manga->getVolumes();

         //We remove the current user from all volumes attached to the manga
         foreach($volumes as $volume) {
             $volume->removeUser($this->userRepository->find($id));
         }
        return $this->json("Le manga" . $manga->getTitle() ." a été supprimé de votre collection", 204);
    }
}
