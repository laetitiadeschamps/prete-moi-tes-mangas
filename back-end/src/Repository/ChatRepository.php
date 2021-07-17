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
     * method to get one chat from one user
     *
     */
    public function findOneByUser($userId, $chatId){

        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter(':id', $chatId)
            //->innerJoin('c.users', 'u', 'WITH', 'u.id = :userId')
            ->leftJoin('c.messages', 'm', 'WITH', 'm.chat=c.id')
            //->addSelect('u')
            ->addSelect('m')
            //->setParameter(':userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
    // /**
    //  * @return Chat[] Returns an array of Chat objects
    //  */
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
