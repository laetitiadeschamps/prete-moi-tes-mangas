<?php

namespace App\EventListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\User;

class AuthenticationSuccessListener extends Event
{


    /**
     * @param AuthenticationSuccessEvent $event
     */
    
    
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();

        /** @var User $user */

        $user = $event->getUser();
    
        if (!$user instanceof UserInterface) {
            return;
        }
    
        $data['data'] = array(
            'pseudo' => $user->getPseudo(),
            'latitude' =>$user->getLatitude(),
            'longitude' =>$user->getLongitude(),
            'email'=>$user->getEmail(),
            'id'=>$user->getId(),
            'picture'=>$user->getPicture(),


        );
    
        $event->setData($data);
    }
}