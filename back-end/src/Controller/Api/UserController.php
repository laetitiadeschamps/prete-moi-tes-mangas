<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\Localisator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
* @Route("/api/v1/user", name="api_user-", requirements={"id"="\d+"})
*/
class UserController extends AbstractController
{
    private $userRepository;
    private $em;
    private $serializer;
    private $localisator;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, Localisator $localisator)
    {
        $this->userRepository = $userRepository;
        $this->serializer=$serializer;
        $this->em = $em;
        $this->localisator = $localisator;
    }
    /**
     * @Route("/{id}/", name="details", methods={"GET"})
     */
    public function details(User $user): Response
    {
      
        return $this->json($user, 200, [], [
            'groups'=>'users'
        ]); 
    }

    /**
     * @Route("/{id}/update", name="update", methods={"PUT|PATCH"})
     */
    public function update(User $user, Request $request): Response
    {
        //Decode de JSON input 
        $jsonData = $request->getContent();
        $this->serializer->deserialize($jsonData, User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
        
        $this->em->flush();

        return $this->json("Votre compte a bien été mis à jour", 200); 
    }

    /**
     * @Route("/add", name="add", methods={"POST"})
     */
    public function add(Request $request, UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer): Response
    {
        
        $JsonData = $request->getContent();
        $user = $serializer->deserialize($JsonData, User::class, 'json');
        //localisation

        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            )
        );
      
        $coordinates = $this->localisator->gpsByZipcodeAndCity($user->getCity(), $user->getZipCode());
        
        $user->setLatitude($coordinates['latitude']);
        $user->setLongitude($coordinates['longitude']);
        $this->em->persist($user);
        $this->em->flush();

    return $this->json('L\'utilisateur '. $user->getPseudo().' a bien été créé', 201);
    }


}
