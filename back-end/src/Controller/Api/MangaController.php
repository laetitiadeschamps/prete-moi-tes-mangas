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
* @Route("/api/v1", name="api_manga-", requirements={"id"="\d+"})
*/
class MangaController extends AbstractController
{
    /**
     * Method allowing to fetch all mangas
     * @Route("/manga", name="list", methods={"GET"})
     */
    public function list(MangaRepository $mangaRepository): Response
    {
       
        return $this->json($mangaRepository->findAll(), 200, [], [
            'groups' => 'mangas'
        ]);
    }
    /**
     * Method allowing to add a manga into a user's collection
     * @Route("/user/{id}/manga", name="add", methods={"POST"})
     */
    public function add(int $id, MangaRepository $mangaRepository, UserRepository $userRepository, VolumeRepository $volumeRepository, Request $request, EntityManagerInterface $em): Response
    {
     
        //We find the manga that was selected and its attached volumes   
        $manga = $mangaRepository->findOneBy(['title'=>$request->get('title')]);
       
        $volumes = $volumeRepository->findSelectedVolumes($manga->getId(),$request->get('volumes'));

        foreach($volumes as $volume) {
            $volume->addUsers($userRepository->find($id));
        }
        $em->flush();


        return $this->json("Le manga". $manga->getTitle() . "a été ajouté à votre collection", 201);
        
    }
    /**
     * @Route("/user/{id}/manga/{mangaId}", name="update", methods={"PUT|PATCH"})
     */
    public function update(int $mangaId, MangaRepository $mangaRepository, EntityManagerInterface $em, Request $request, SerializerInterface $serializer): Response
    {
        return $this->json("Le manga XX a été ajouté à votre collection", 201);   
    }
    /**
     * @Route("/user/{id}/manga/{mangaId}", name="delete", methods={"DELETE"})
     */
    public function delete(int $mangaId, MangaRepository $mangaRepository, EntityManagerInterface $em): Response
    {
        return $this->json("Le manga XX a été ajouté à votre collection", 201);
    }
}
