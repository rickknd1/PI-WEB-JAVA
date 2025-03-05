<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use http\Client\Curl\User;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function searchByKeyword(string $keyword)
    {
        return $this->createQueryBuilder('p')
            ->where('p.content LIKE :keyword OR p.title LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->getQuery()
            ->getResult();
    }

    public function getVisiblePosts(User $user)
    {
        return $this->createQueryBuilder('p')
            ->where('p.visibility = :public')
            ->orWhere('p.visibility = :friends AND :user MEMBER OF p.author.friends')
            ->orWhere('p.visibility = :community AND :user MEMBER OF p.community.members')
            ->setParameter('public', 'public')
            ->setParameter('friends', 'friends')
            ->setParameter('community', 'community')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function searchPosts(?string $titre, ?string $content, ?\DateTimeInterface $date): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($titre) {
            $qb->andWhere('p.titre LIKE :titre')
                ->setParameter('titre', '%' . $titre . '%');
        }

        if ($content) {
            $qb->andWhere('p.content LIKE :content')
                ->setParameter('content', '%' . $content . '%');
        }

        if ($date) {
            $qb->andWhere('DATE(p.createdAt) = :date')
                ->setParameter('date', $date->format('Y-m-d'));
        }

        return $qb->getQuery()->getResult();
    }

    public function searchByName(string $query)
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :query')
            ->setParameter('query', "%$query%")
            ->getQuery()
            ->getResult();
    }

    public function findSimilarPosts(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.likes', 'l')
            ->where('l.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findRecommendedPosts(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.likes', 'l')
            ->innerJoin('l.user', 'u')
            ->where('u IN (
            SELECT DISTINCT l2.user FROM App\Entity\Like l2
            WHERE l2.post IN (
                SELECT l3.post FROM App\Entity\Like l3 WHERE l3.user = :user
            ) AND l2.user != :user
        )')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }



    //    /**
    //     * @return Post[] Returns an array of Post objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
