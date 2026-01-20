<?php

namespace App\Repository;

use App\Entity\LearningProgress;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LearningProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LearningProgress::class);
    }

    // Gesamtversuche
    public function getTotalAnswers(User $user): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('SUM(p.correctCount + p.wrongCount)')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult() ?: 0;
    }

    // Richtige Antworten
    public function getCorrectAnswers(User $user): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('SUM(p.correctCount)')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult() ?: 0;
    }
public function countLearnedWords(User $user): int
{
    return (int) $this->createQueryBuilder('p')
        ->select('COUNT(p.id)')
        ->where('p.user = :user')
        ->andWhere('p.correctCount >= 5')
        ->setParameter('user', $user)
        ->getQuery()
        ->getSingleScalarResult();
}

public function getAccuracy(User $user): int
{
    $data = $this->createQueryBuilder('p')
        ->select('SUM(p.correctCount) as correct, SUM(p.wrongCount) as wrong')
        ->where('p.user = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getOneOrNullResult();

    if (!$data || ($data['correct'] + $data['wrong']) == 0) {
        return 0;
    }

    return (int) round(($data['correct'] / ($data['correct'] + $data['wrong'])) * 100);
}

public function getStreak(User $user): int
{
    $dates = $this->createQueryBuilder('p')
        ->select('DISTINCT DATE(p.lastSeen) as d')
        ->where('p.user = :user')
        ->andWhere('p.lastSeen IS NOT NULL')
        ->setParameter('user', $user)
        ->orderBy('d', 'DESC')
        ->getQuery()
        ->getScalarResult();

    $streak = 0;
    $today = new \DateTimeImmutable('today');

    foreach ($dates as $row) {
        $date = new \DateTimeImmutable($row['d']);
        if ($date->diff($today)->days === $streak) {
            $streak++;
        } else {
            break;
        }
    }

    return $streak;
}

public function findForToday(User $user, int $limit = 10): array
{
    return $this->createQueryBuilder('p')
        ->where('p.user = :user')
        ->orderBy('p.lastSeen', 'ASC')
        ->setParameter('user', $user)
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    

    
}
}