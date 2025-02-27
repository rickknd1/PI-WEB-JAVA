<?php

namespace App\Repository;

use App\Entity\MembreComunity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MembreComunity>
 */
class MembreComunityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembreComunity::class);
    }
    public function findByUserId($userId)
    {
        return $this->createQueryBuilder('m')
            ->where('m.id_user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }
    public function findOwnersForNoActiveCommunities()
    {
        return $this->createQueryBuilder('mc')
            ->select('u.username', 'c.id AS community_id')
            ->innerJoin('mc.community', 'c')
            ->innerJoin('mc.id_user', 'u')
            ->where('c.statut = :statut')
            ->andWhere('mc.status = :status')
            ->setParameter('statut', 0)
            ->setParameter('status', 'owner')
            ->getQuery()
            ->getResult();
    }



//    /**
//     * @return MembreComunity[] Returns an array of MembreComunity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MembreComunity
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
