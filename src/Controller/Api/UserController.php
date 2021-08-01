<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\UserVolume;
use App\Form\UserType;
use App\Repository\ChatRepository;
use App\Repository\UserRepository;
use App\Repository\UserVolumeRepository;
use App\Service\Localisator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
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
    private $chatRepository;
    private $mailer;

    public function __construct(MailerInterface $mailer, UserRepository $userRepository, SerializerInterface $serializer, EntityManagerInterface $em, Localisator $localisator, ValidatorInterface $validator, UserVolumeRepository $userVolumeRepository, ChatRepository $chatRepository)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->em = $em;
        $this->localisator = $localisator;
        $this->validator = $validator;
        $this->userVolumeRepository = $userVolumeRepository;
        $this->chatRepository = $chatRepository;
        $this->mailer = $mailer;
    }
    /**
     * Method to see a user's profile (the logged in user or any other user)
     * @Route("/{id}", name="details", methods={"GET"})
     */
    public function details(int $id, Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();
        $contact = $this->userRepository->find($id);
        $contactForDisplay = $this->userRepository->findContactForProfile($id);
        // If the id is not on of an existing user, we throw an error
        if(!$contact) {
            return $this->json(
                ['error' => 'Cet utilisateur n\'existe pas'],
                404
            );
        }
        // We build an array that contains on the one hand the user's infos and on the other hand the chat between the user and the logged in user. Returns null if no chat found
        if($user == $contact) {
            $chat = null;
        } else {
            $chat = $this->chatRepository->getChatIdFromUsers($user->getId(), $contact->getId());
        }
        
      
        $infos['contact'] = $contactForDisplay;
     
        foreach($contact->getVolumes() as $volume) {
           
            $infos['contact']['manga'][$volume->getVolume()->getManga()->getTitle()]['info']=$volume->getVolume()->getManga();
            $infos['contact']['manga'][$volume->getVolume()->getManga()->getTitle()]['volumes'][]=['status'=> $volume->getStatus(), 'number'=>$volume->getVolume()->getNumber()];
            //$infos['contact']['manga'][$volume->getVolume()->getManga()->getTitle()]['volumes'][]['number']=$volume->getVolume()->getNumber();
        }
        
        $infos['chat'] = $chat;

       
        return $this->json($infos, 200, [], [
            'groups' => 'users'
        ]);
    }

    /**
     * Method to update the logged in user's profile info
     * @Route("/{id}/update", name="update", methods={"PUT|PATCH"})
     */
    public function update(User $user, Request $request, UserPasswordHasherInterface $passwordEncoder): Response
    {
        //We decode de JSON input to check if the password has been changed
        $jsonArray = json_decode($request->getContent(), true);
        $needsHash = false;
        if (isset($jsonArray['password'])) {
            $needsHash = true;
        };
        $jsonData = $request->getContent();
        // We edit our user with given updated informations
        $this->serializer->deserialize($jsonData, User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user, AbstractNormalizer::IGNORED_ATTRIBUTES => ['zip_code']]); 
        // We set the zipcode independantly from the other properties because it needs cto be converted in an integer 
        isset($jsonArray['zip_code']) ? $user->setZipCode(intval($jsonArray['zip_code'])):'';

        // According to the updated address and city, we update coordinates
        $coordinates = $this->localisator->gpsByAdress($user->getAddress()??'error', $user->getZipCode()??'error');
        extract($coordinates);
        $zipCodeError = null;
        //if an error in Localisator is returned :
        if (isset($error)) {
            $zipCodeError = "L'adresse n\'est pas valide";
        } else {
            $user->setLatitude($latitude);
            $user->setLongitude($longitude);
        }
        if(isset($jsonArray['password'])) {
              //We validate the inputs according to our constraints
              
                $errors = $this->validator->validate($user);
        } else {
            //We validate the inputs according to our constraints
            $errors = $this->validator->validate($user, null, ['update']);
        }
        
        //If there are any errors, we send back a list of errors (reformatted for clearer output) 
        if ($zipCodeError || count($errors) > 0) {
            $errorslist = array();
            if($zipCodeError) {
                $errorslist['zip_code']=$zipCodeError;
            }
            foreach ($errors as $error) {
                $field = $error->getPropertyPath();
                $errorslist[$field] = $error->getMessage();
            }
            return $this->json($errorslist, 400);
        }
        //If a new password has been given, we hash it before sending to the database
        if($needsHash) {
            $user->setPassword(
                $passwordEncoder->hashPassword(
                    $user,
                    $user->getPassword()
                )
            );
        }
        

       
        $this->em->flush();
        return $this->json("Votre compte a bien été mis à jour", 200);
    }

    /**
     * Method to create a user
     * @Route("/add", name="add", methods={"POST"})
     */
    public function add(Request $request, UserPasswordHasherInterface $passwordEncoder): Response
    {

        $JsonData = $request->getContent();
        $user = new User();

        $this->serializer->deserialize($JsonData, User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user, AbstractNormalizer::IGNORED_ATTRIBUTES => ['zip_code']]); 
        // We set the zipcode independantly from the other properties because it needs cto be converted in an integer 
       $jsonArray = json_decode($request->getContent(), true);
       if(isset($jsonArray['zip_code'])) {
            $user->setZipCode(intval($jsonArray['zip_code']));
       }
       
        //hashing password and setting it for the newly created user
        // Retrieving coordinates according to user address and zip code and setting them for the newly created user
        $coordinates = $this->localisator->gpsByAdress($user->getAddress()??'error', $user->getZipCode()??'error');

        extract($coordinates);
        //if an error in Localisator is returned :
        $zipCodeError = null;
        if (isset($error)) {
            $zipCodeError="L\'adresse n'\est pas valide";
        } else {
            $user->setLatitude($latitude);
            $user->setLongitude($longitude);
        }
 
        $user->setRoles(['ROLE_USER']);

        //We validate the inputs according to our constraints
        $errors = $this->validator->validate($user);

        //If there are any errors, we send back a list of errors (reformatted for clearer output)
        if ($zipCodeError ||count($errors) > 0) {
            $errorslist = array();
            if($zipCodeError) {
                $errorslist['zip_code']=$zipCodeError;
            }
            foreach ($errors as $error) {
                $field = $error->getPropertyPath();
                $errorslist[$field] = $error->getMessage();
            }
            return $this->json($errorslist, 400);
        }
        $user->setPassword(
            $passwordEncoder->hashPassword(
                $user,
                $user->getPassword()
            )
        );


        $this->em->persist($user);
        $this->em->flush();

        $email = (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject('KASU Admin : création de compte')
                ->htmlTemplate('emails/new_account.html.twig')
                ->context([
                    'user' => $user,
                   
                ]);
            $this->mailer->send($email);
        return $this->json('L\'utilisateur ' . $user->getPseudo() . ' a bien été créé', 201);
    }
}
