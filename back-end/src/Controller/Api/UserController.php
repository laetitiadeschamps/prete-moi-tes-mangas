<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\UserVolumeRepository;
use App\Service\Localisator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
* @Route("/api/v1/user", name="api_user-", requirements={"id"="\d+"})
*/
class UserController extends AbstractController
{
    private $userRepository;
    private $em;
    private $serializer;
    private $localisator;
    private $validator;
    private $userVolumeRepository;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, Localisator $localisator, ValidatorInterface $validator, UserVolumeRepository $userVolumeRepository)
    {
        $this->userRepository = $userRepository;
        $this->serializer=$serializer;
        $this->em = $em;
        $this->localisator = $localisator;
        $this->validator = $validator;
        $this->userVolumeRepository = $userVolumeRepository;
    }
    /**
     * @Route("/{id}/", name="details", methods={"GET"})
     */
    public function details(int $id): Response
    {
       $user = $this->userRepository->find($id);
        if(!$user) {
            return $this->json(
                ['error' => 'Cet utilisateur n\'existe pas'], 404
            );
        }
        return $this->json($user, 200, [], [
            'groups'=>'users'
        ]); 
    }

    /**
     * @Route("/{id}/update", name="update", methods={"PUT|PATCH"})
     */
    public function update(User $user, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
      
        //Decode de JSON input to check if the password has been changed
        $jsonArray = json_decode($request->getContent(), true);
        $needsHash = false;
        if(isset($jsonArray['password'])) {
            $needsHash = true;
        };  
        $jsonData = $request->getContent();
        // editing our user with given updated informations
        $this->serializer->deserialize($jsonData, User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user, AbstractNormalizer::IGNORED_ATTRIBUTES => ['zip_code']]); 
        $user->setZipCode(intval($jsonArray['zip_code']));
        //We validate the inputs according to our constraints
        $errors = $this->validator->validate($user);
        //If there are any errors, we send back a list of errors (reformatted for clearer output)
        
        if (count($errors) > 0) {
            $errorslist = array();
            foreach ($errors as $error) {
                $field = $error->getPropertyPath();
                $errorslist[$field] = $error->getMessage();
            }
            return $this->json($errorslist, 400);
        }
        //If a new password has been given, we hash it before sending to DB
        if($needsHash) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $user->getPassword()
                )
            );
        }
        $coordinates = $this->localisator->gpsByAdress($user->getAddress(), $user->getZipCode());
        $user->setLatitude($coordinates['latitude']);
        $user->setLongitude($coordinates['longitude']);
        $this->em->flush();
        return $this->json("Votre compte a bien été mis à jour", 200); 
    }

    /**
     * @Route("/add", name="add", methods={"POST"})
     */
    public function add(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
       
        $JsonData = $request->getContent();
        $user = $this->serializer->deserialize($JsonData, User::class, 'json');
        //hashing password and setting it for the newly created user
        // Retrieving coordinates according to user address and zip code and setting them for the newly created user
        $coordinates = $this->localisator->gpsByAdress($user->getAddress(), $user->getZipCode());
        $user->setLatitude($coordinates['latitude']);
        $user->setLongitude($coordinates['longitude']);
        $user->setRoles(['ROLE_USER']);
        //We validate the inputs according to our constraints
        $errors = $this->validator->validate($user);
        //If there are any errors, we send back a list of errors (reformatted for clearer output)
        if (count($errors) > 0) {
            $errorslist = array();
	        foreach ($errors as $error) {
                $field = $error->getPropertyPath();
                $errorslist[$field] = $error->getMessage();
            }
            return $this->json($errorslist, 400);
        }
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user, $user->getPassword()
            )
        );
        $this->em->persist($user);
        $this->em->flush();
        return $this->json('L\'utilisateur '. $user->getPseudo().' a bien été créé', 201);
    }


}
