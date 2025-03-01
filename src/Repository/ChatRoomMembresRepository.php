<?php

namespace App\Repository;

use App\Entity\ChatRoomMembres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChatRoomMembres>
 */
class ChatRoomMembresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatRoomMembres::class);
    }
    public function findByUserId($userId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return ChatRoomMembres[] Returns an array of ChatRoomMembres objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ChatRoomMembres
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
