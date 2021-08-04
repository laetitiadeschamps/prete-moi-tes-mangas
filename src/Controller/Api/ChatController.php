<?php

namespace App\Controller\Api;

use App\Entity\Chat;
use App\Entity\Message;
use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/v1/user/{id}", name="api_chat_", requirements={"id"="\d+", "chatId"="\d+"})
 * 
 */
class ChatController extends AbstractController
{
    protected $userRepository;
    protected $chatRepository;
    protected $messageRepository;
    protected $em;
    protected $serializer;
    private $mailer;


    public function __construct(SerializerInterface $serializer, UserRepository $userRepository, EntityManagerInterface $em, MessageRepository $messageRepository, ChatRepository $chatRepository, MailerInterface $mailer)
    {
        $this->userRepository = $userRepository;
        $this->chatRepository = $chatRepository;
        $this->messageRepository = $messageRepository;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->mailer = $mailer;
    }


    /**
     * method to fetch all chats of a user
     *
     * @Route("/chat", name="list", methods="GET")
     * @param int $id user Id
     * @return Response
     */
    public function list(int $id): Response
    {
        if (!$this->userRepository->find($id)) {
            return $this->json(
                ['error' => 'La ressource demandée n\'existe pas'],
                404
            );
        }
        // fetching all chats from one user
        /** @var array $chats */
        $chats = $this->chatRepository->findAllByUser($id);

        //fetching the last message of each conversation
        $messageArray =[];
        foreach ($chats as $chat) {
            $messageArray[$chat->getId()]['chat'] = $chat;
            $messageArray[$chat->getId()]['lastmessage'] = $this->messageRepository->getLastMessage($chat->getId());
        }

        return $this->json($messageArray, 200, [], [
            'groups' => 'chats'
        ]);
    }

    /**
     * method to get one chat of a user
     * @Route("/chat/{chatId}", name="details", methods="GET")
     *
     * @param integer $id
     * @param integer $chatId
     * @return Response
     */
    public function details(int $id, int $chatId): Response
    {
        
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(
                ['error' => 'La ressource demandée n\'existe pas'],
                404
            );
        }

        $chat = $this->chatRepository->findOneWithMessages($chatId);

        if (!$chat) {
            return $this->json(
                ['error' => 'La ressource demandée n\'existe pas'],
                404
            );
        }

        //handling unread messages
        $messages = $chat->getMessages();
        foreach ($messages as $message) {
            if ($message->getAuthor() != $user && $message->getStatus() == 0) {
                $newMessages[] = $message;
            }
        }
        $newMessages = [];
        foreach ($newMessages as $message) {
            $message->setStatus(1);
        }
        $this->em->flush();
        return $this->json($chat, 200, [], [
            'groups' => 'one-chat'
        ]);
    }

    /**
     * Method to create a chat if it doesn't exist already and return the id of the chat if it does
     *
     * @Route("/chat", name="createOrGet", methods="POST")
     * @param Request $request
     * @param integer $id userId
     * @return Response
     */
    public function createOrGet(Request $request,int $id): Response
    {
        $user = $this->userRepository->find($id); 
        //getting second user
        $jsonData = $request->toArray();


        if (!isset($jsonData['other_user'])){
            return $this->json(
                ['error' => 'Les données transmises ne sont pas valides'],
                400
            );
        }
        $otherUserId = $jsonData['other_user'];
        $otherUser = $this->userRepository->find($otherUserId);

        //if they don't exist
        if (!$user || !$otherUser) {
            return $this->json(
                ['error' => 'La ressource demandée n\'existe pas'],
                404
            );
        }

        // If there is already a chat between both users, we return the chat id, else we create one

        /** @var Chat $chat */
        $chat = $this->chatRepository->getChatIdFromUsers($user->getId(), $otherUser->getId());
        if($chat) {
            $id = $chat->getId();
        }
        else {
            $chat = new Chat();
            $title = $user->getPseudo() . " - " . $otherUser->getPseudo();
            $chat->setTitle($title);
            $chat->addUser($user);
            $chat->addUser($otherUser);
            $this->em->persist($chat);
            $this->em->flush();

            $id = $chat->getId();
        }
        
        return $this->json(
            [
                'id'=>$id,
                'message' => 'La conversation a bien été créée'
            ],
            201
        );
    }

    /**
     * Method to add a message from a user in a existing chat
     * @Route("/chat/{chatId}/message", name="add", methods="POST")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param integer $id
     * @param integer $chatId
     * @return Response
     */
    public function add(Request $request, ValidatorInterface $validator,int $id, int $chatId): Response
    {

        //first, i get the concerned chat
        $chat = $this->chatRepository->find($chatId);
        // then the concerned user
        $author = $this->userRepository->find($id);

        //if they don't exist
        if (!$chat || !$author) {
            return $this->json(
                ['error' => 'La ressource demandée n\'existe pas'],
                404
            );
        }

        //getting datas
        $jsonData = $request->getContent();
       
        //deserialization : Json => Object
        $message = $this->serializer->deserialize($jsonData, Message::class, 'json');

        $message->setAuthor($author);
        $message->setChat($chat);

        //datas validation
        $errors = $validator->validate($message);

        //errorArray to send to front useful messages of error (instead of ConstraintViolationListInterface)
        if (count($errors) > 0) {
            $errorArray = [];
            foreach ($errors as $error) {
                // name of field where there is an error
                $field = $error->getPropertyPath();

                // getting the message error
                $errorArray[$field] = $error->getMessage();
            }
            return $this->json(
                [
                    'error' => $errorArray
                ],
                500
            );
        } else {

            $this->em->persist($message);
            $this->em->flush();

            // We find the recipient of the message to email him
            /** @var Array $members */
            $members = $chat->getUsers();

            foreach ($members as $member) {
                if ($member->getId() !== $author->getId()) {
                    $recipient = $member;
                }
            }

            //sending email to notify a new message 
            $email = (new TemplatedEmail())
                ->to($recipient->getEmail())
                ->subject('Nouveau message !')
                ->htmlTemplate('emails/new_message.html.twig')
                ->context([
                    'user' => $recipient,
                    'author' => $author,
                    'message' => $message
                ]);
            $this->mailer->send($email);

            return $this->json($chat, 201, [], [
                'groups' => 'one-chat'
            ]);
        }
    }


    /**
     * Method to create a conversation with an admin through the contact form
     * @Route("/contact-admin", name="contactAdmin", methods="POST")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param integer $id User
     * @return Response
     */
    public function contactAdmin(Request $request, ValidatorInterface $validator,int $id): Response
    {


        $author = $this->userRepository->find($id);
        $admins = $this->userRepository->findAdmin();

        if (!$author || !$admins) {

            return $this->json(
                ['error' => 'La ressource demandée n\'existe pas'],
                404
            );
        }
        
        //We want to create a chat and relate it to the user and all admins.

        $chatAdmin = $this->chatRepository->findOneBy(["title" => "ADMIN"]);

        //creation a Chat ADMIN if not existent
        if (!$chatAdmin) {
            $chatAdmin = new Chat();
            $chatAdmin->setTitle("ADMIN");
            $this->em->persist($chatAdmin);
            $this->em->flush();
        }

        $chatAdmin = $this->chatRepository->findOneBy(["title" => "ADMIN"]);


        //We want to create a message with datas from POST request and link it to the chat.
        $jsonData = $request->getContent();

        //deserialization : Json => Object
        $message = $this->serializer->deserialize($jsonData, Message::class, 'json');

        //object cannot be null on contact-admin
        if ($message->getObject() == null) {
            return $this->json(
                [
                    'error' => 'L\'objet doit être renseigné'
                ],
                400
            );
        }
        $errors = $validator->validate($message);

        //errorArray to send to front useful messages of error (instead of ConstraintViolationListInterface)
        if (count($errors) > 0) {
            $errorArray = [];
            foreach ($errors as $error) {
                // name of field where there is an error
                $field = $error->getPropertyPath();

                // getting the message error
                $errorArray[$field] = $error->getMessage();
            }
            return $this->json(
                [
                    'error' => $errorArray
                ],
                500
            );
        }
        

        $message->setAuthor($author);
        $message->setChat($chatAdmin);

        $this->em->persist($message);
        $this->em->flush();

        //emailing 
        $email = (new TemplatedEmail())

            ->to(new Address($author->getEmail()))
            ->subject('KASU Admin : accusé de réception')

            // path of the Twig template to render
            ->htmlTemplate('emails/new_contact_form.html.twig')

            // pass variables (name => value) to the template
            ->context([

                'user' => $author,
            ]);
        $this->mailer->send($email);

        return $this->json(
            [
                'message' => 'La demande de contact a bien été envoyée'
            ],
            201
        );
    }
}
