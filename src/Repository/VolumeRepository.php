<?php

namespace App\Repository;

use App\Entity\Volume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Volume|null find($id, $lockMode = null, $lockVersion = null)
 * @method Volume|null findOneBy(array $criteria, array $orderBy = null)
 * @method Volume[]    findAll()
 * @method Volume[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolumeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Volume::class);
    }

    public function findSelectedVolumes($mangaId, $volumes) {
       
        $qb = $this->createQueryBuilder('volume')->where('volume.number IN ('. $volumes .')')->andWhere('volume.manga = :manga')->setParameter(':manga', $mangaId);
        $query = $qb->getQuery();
       
        return $query->getResult();
      }
      public function search($users)
      {
          foreach($users as $user) {
              $userIds[] = $user->getId();
          }
          $userIds = implode(", ", $userIds);     
          $qb = $this->createQueryBuilder('volume')->join('volume.users', 'users')->addSelect('users')->where('users');
          $query = $qb->getQuery();
         
          return $query->getResult();
      }


      

  
    

    // /**
    //  * @return Volume[] Returns an array of Volume objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Volume
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
