<?php

namespace App\Controller\Api;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\Localisator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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


    public function __construct(SerializerInterface $serializer, UserRepository $userRepository, EntityManagerInterface $em, MessageRepository $messageRepository, ChatRepository $chatRepository)
    {
        $this->userRepository = $userRepository;
        $this->chatRepository = $chatRepository;
        $this->messageRepository = $messageRepository;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * method to fetch all chats of a user
     * @Route("/chat", name="list", methods="GET")
     *
     */
    public function list($id): Response
    {
        // fetching all chats from one user
        $chats = $this->chatRepository->findAllByUser($id);
        
        //fetching the last message of each conversation
        $messagesArray = [];
        foreach($chats as $chat){
            $messagesArray[$chat->getId()] = $this->messageRepository->getLastMessage($chat->getId());
        }
        
        
        $array = [$chats, $messagesArray];
        return $this->json($array, 200, [], [
            'groups' => 'chats'
        ]);
    }

    /**
     * method to get one chat of a user
     * @Route("/chat/{chatId}", name="details", methods="GET")
     */
    public function details($chatId)
    {
        $chat = $this->chatRepository->findOneWithMessages($chatId);
        
        return $this->json($chat, 200, [], [
            'groups' => 'one-chat'
        ]);
    }

    /**
     * method to add a message from a user in a chat
     * @Route("/chat/{chatId}/message", name="add", methods="POST")
     */
    public function add(Request $request, ValidatorInterface $validator, $id, $chatId)
    {
        //first, i get the concerned chat
        $chat = $this->chatRepository->find($chatId);
        // then the concerned user
        $author = $this->userRepository->find($id);
        
        $jsonData = $request->getContent();
        //deserialization : Json => Object
        $message = $this->serializer->deserialize($jsonData, Message::class, 'json');

        
        $message->setAuthor($author);
        $message->setChat($chat);
        
        //datas validation
        $errors = $validator->validate($message);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return $this->json(
                [
                    'error' => $errorsString
                ],
                500
            );
        } else {

            $this->em->persist($message);
            $this->em->flush();
            
            return $this->json(
                [
                    'message' => 'Le message a bien été ajouté à la conversation'
                ],
                201
            );
        }
    }


}
