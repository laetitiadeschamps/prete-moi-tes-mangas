<?php

namespace App\Controller\Api;

use App\Entity\Manga;
use App\Entity\UserVolume;
use App\Repository\MangaRepository;
use App\Repository\UserRepository;
use App\Repository\UserVolumeRepository;
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
    private $userVolume;

    public function __construct(MangaRepository $mangaRepository, VolumeRepository $volumeRepository, UserVolumeRepository $userVolume, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->mangaRepository = $mangaRepository;
        $this->volumeRepository = $volumeRepository;
        $this->userRepository = $userRepository;
        $this->em=$em;
        $this->userVolume = $userVolume;
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
            $user_volume = new UserVolume();
            $user_volume->setUser($this->userRepository->find($id));
            $user_volume->setVolume($volume);
            $this->em->persist($user_volume);
        }
        $this->em->flush();

        return $this->json("Le manga ". $manga->getTitle() . " a été ajouté à votre collection", 201);
        
    }
    /**
     * @Route("/user/{id}/manga/{mangaId}", name="update", methods={"PUT|PATCH"})
     */
    public function update(int $id, int $mangaId, Request $request): Response
    {
        $manga = $this->mangaRepository->find($mangaId);
        $user = $this->userRepository->find($id);
        
        $volumes = $manga->getVolumes();
      
        //We remove all volumes of the collection from the current user's collection
        foreach ($volumes as $volume) {
            $volume_user = $this->userVolume->findOneBy(['user'=>$user, 'volume'=>$volume]);
            $volume_user ? $this->em->remove($volume_user):'';
        }
        
        // Then we add only the volumes sent by the fetch request to the user's collection
        $volumesOwned = $this->volumeRepository->findSelectedVolumes($mangaId,$request->get('volumes')); 
        
        foreach($volumesOwned as $volumeOwned) {
            $user_volume = new UserVolume();
            $user_volume->setUser($user);
            $user_volume->setVolume($volumeOwned);
            $this->em->persist($user_volume);
        }
        $this->em->flush();
        return $this->json("Le manga ". $manga->getTitle() ." a bien été mis à jour", 200); 
    }
    /**
     * @Route("/user/{id}/manga/{mangaId}/availability", name="availability", methods={"PUT|PATCH"})
     */
    public function availability(int $mangaId, Request $request): Response
    {
        $manga = $this->mangaRepository->find($mangaId);
        return $this->json("Vos disponibilités pour le manga ". $manga->getTitle() ." ont bien été mises à jour", 200);   
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
         foreach ($volumes as $volume) {
             $volume_user = $this->userVolume->findOneBy(['user'=>$this->userRepository->find($id), 'volume'=>$volume]);
             $volume_user ? $this->em->remove($volume_user):'';
         }
         $this->em->flush();
        return $this->json("Le manga" . $manga->getTitle() ." a été supprimé de votre collection", 204);
    }
}
