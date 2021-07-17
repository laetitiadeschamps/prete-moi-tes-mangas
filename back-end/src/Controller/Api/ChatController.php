<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
        $user = $this->userRepository->find($id);

        $chats = $user->getChats();

        return $this->json($chats, 200, [], [
            'groups' => 'chats'
        ]);
    }

    /**
     * method to get one chat of a user
     * @Route("/chat/{chatId}", name="details", methods="GET")
     */
    public function details($id, $chatId)
    {
        $user = $this->userRepository->find($id);
        $chat = $this->chatRepository->findOneByUser($id, $chatId);
        
        return $this->json($chat, 200, [], [
            'groups' => 'one-chat'
        ]);
    }
}
