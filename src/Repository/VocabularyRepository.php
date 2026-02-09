<?php

namespace App\Repository;

use App\Entity\Vocabulary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;


class VocabularyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vocabulary::class);
    }


    public function getFlashcardsForUser(User $user): array
    {

        return $this->createQueryBuilder('v')
            ->leftJoin(
                'App\Entity\LearningProgress',
                'p',
                'WITH',
                'p.vocabulary = v AND p.user = :user'
            )
            ->setParameter('user', $user)
            ->orderBy('p.wrongCount', 'DESC')
            ->addOrderBy('p.lastSeen', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findForLearning(
    ?string $language = null,
    ?string $level = null,
    ?string $category = null,
    int $limit = 20
): array {
    $qb = $this->createQueryBuilder('v');

    if ($language) {
        $qb->andWhere('v.language = :language')
           ->setParameter('language', $language);
    }

    if ($level) {
        $qb->andWhere('v.level = :level')
           ->setParameter('level', $level);
    }

    if ($category) {
        $qb->andWhere('v.category = :category')
           ->setParameter('category', $category);
    }

    $result = $qb->getQuery()->getResult();

    shuffle($result);
    return array_slice($result, 0, $limit);
}

public function countLanguagesForUser(User $user): int
{
        if (!$user) return 0;

    return (int) $this->createQueryBuilder('v')
        ->select('COUNT(DISTINCT v.language)')
        ->getQuery()
        ->getSingleScalarResult();
}
public function countDistinctLanguages(User $user): int
{
    return (int) $this->createQueryBuilder('v')
        ->select('COUNT(DISTINCT v.language)')
        ->join('App\Entity\LearningProgress', 'p', 'WITH', 'p.vocabulary = v')
        ->where('p.user = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getSingleScalarResult();
}


}
