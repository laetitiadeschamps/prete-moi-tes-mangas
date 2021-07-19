<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }


    public function search($latitude, $longitude)
    {
        // name of the current table
        $table = $this->getClassMetadata()->table["name"];
       
        //sql query with haversine formula to get all users within 30km of the coordinates points
        $sql = "SELECT u.* "
        .",(
            6371 *
            acos(cos(radians(:lat)) * 
            cos(radians(u.latitude)) * 
            cos(radians(u.longitude) - 
            radians(:long)) + 
            sin(radians(:lat)) * 
            sin(radians(u.latitude)))
            ) AS distance "
            ."FROM " . $table . " AS u "
        ."HAVING distance < 30 "
        ."ORDER BY distance;";

        // mapping of the user entity to get object datas
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addEntityResult(User::class, "u");

        foreach ($this->getClassMetadata()->fieldMappings as $obj) {
            $rsm->addFieldResult("u", $obj["columnName"], $obj["fieldName"]);
        }

        //native query
        $stmt = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $stmt->setParameter(":lat", $latitude);
        $stmt->setParameter(":long", $longitude);
        $stmt->execute();

        return $stmt->getResult();
    }


    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
