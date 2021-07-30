<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * method to get the last message of one chat
     *
     * @param [type] $chatId
     * @return void
     */
    public function getLastMessage($chatId){

        return $this->createQueryBuilder('m')
                    ->where('m.chat = :chatId')
                    ->setParameter(':chatId', $chatId)
                    ->orderBy('m.created_at', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

        // /**
    //  * @return Manga[] Returns an array of Message f
    //  */
    
    public function getUnreadCount()
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id) as count')
            ->where('m.status = 0')
            ->innerJoin('m.chat', 'c', 'WITH', 'c.title LIKE :admin')->setParameter(':admin', 'ADMIN')
            ->getQuery()
            ->getSingleResult();
    }  
    public function getArchiveCount()
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id) as count')
            ->innerJoin('m.chat', 'c', 'WITH', 'c.title LIKE :admin')->setParameter(':admin', 'ARCHIVE')
            ->getQuery()
            ->getSingleResult();
    } 
    // /**
    //  * @return Message[] Returns an array of Message objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
