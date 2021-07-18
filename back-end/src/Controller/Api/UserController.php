<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
* @Route("/api/v1/user/{id}", name="api_user-", requirements={"id"="\d+"})
*/
class UserController extends AbstractController
{
    private $userRepository;
    private $em;
    private $serializer;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->serializer=$serializer;
        $this->em = $em;
    }
    /**
     * @Route("/", name="details", methods={"GET"})
     */
    public function details(User $user): Response
    {
      
        return $this->json($user, 200, [], [
            'groups'=>'users'
        ]); 
    }

    /**
     * @Route("/test", name="update", methods={"PUT|PATCH"})
     */
    public function update(User $user, Request $request): Response
    {
     
        //Decode de JSON input 
        $jsonData = $request->getContent();
        $this->serializer->deserialize($jsonData, User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
       
        $this->em->flush();

        return $this->json("Votre compte a bien été mis à jour", 200); 
    }
}
