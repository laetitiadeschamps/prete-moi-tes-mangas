<?php

namespace App\Repository;

use App\Entity\Chat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Chat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chat[]    findAll()
 * @method Chat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    /**
     * method to get all chats of a user
     * @param integer $id
     * @return Chat[] an array of chats
     */
    public function findAllByUser(int $id)
    {
        return $this->createQueryBuilder('c')
            ->andWhere(':id MEMBER OF c.users')
            ->setParameter(':id', $id)
            ->join('c.users', 'u')
            ->orderBy('c.created_at', 'DESC')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }

    /**
     *
     * method to get one chat from one user with all users and messages related to
     * @param integer $chatId
     * @return Chat
     */
    public function findOneWithMessages(int $chatId): ?Chat
    {

        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter(':id', $chatId)
            ->leftJoin('c.messages', 'm', 'WITH', 'm.chat=c.id')
            ->addSelect('m')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * method to get the chat of 2 users
     *
     * @param integer $userId
     * @param integer $contactId
     * @return Chat
     */
    public function getChatIdFromUsers(int $userId, int $contactId): ?Chat
    {
                return $this->createQueryBuilder('chat')
            ->join('chat.users', 'users')
            ->where(':userId MEMBER OF chat.users')
            ->andWhere(':contactId MEMBER OF chat.users')
            ->setParameter(':userId', $userId)
            ->setParameter(':contactId', $contactId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Chat
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
