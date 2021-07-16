<?php

namespace App\Controller\Api;

use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    

    public function __construct(UserRepository $userRepository, EntityManagerInterface $em, MessageRepository $messageRepository, ChatRepository $chatRepository){

        $this->userRepository = $userRepository;
        $this->chatRepository = $chatRepository;
        $this->messageRepository = $messageRepository;
        $this->em = $em;
        

    }
    
        
    
    /**
     * method to fetch all chats of a user
     * @Route("/chat", name="list")
     * 
     */
    public function list($id): Response
    {
        //I need 
        return $this->json("", 200, [""], [
            'groups' => 'mangas'
        ]);
    }
}
