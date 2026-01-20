<?php

namespace App\Entity;

use App\Repository\LearningProgressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LearningProgressRepository::class)]
#[ORM\Table(name: 'learning_progress')]
#[ORM\UniqueConstraint(columns: ['user_id', 'vocabulary_id'])]
class LearningProgress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Vocabulary $vocabulary;

    #[ORM\Column]
    private int $correctCount = 0;

    #[ORM\Column]
    private int $wrongCount = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSeen = null;

    // ---------------- GETTER / SETTER ----------------

    public function getId(): ?int { return $this->id; }

    public function setUser(User $user): self {
        $this->user = $user;
        return $this;
    }

    public function getUser(): User { return $this->user; }

    public function setVocabulary(Vocabulary $vocabulary): self {
        $this->vocabulary = $vocabulary;
        return $this;
    }

    public function getVocabulary(): Vocabulary { return $this->vocabulary; }

    public function markCorrect(): void {
        $this->correctCount++;
        $this->lastSeen = new \DateTimeImmutable();
    }

    public function markWrong(): void {
        $this->wrongCount++;
        $this->lastSeen = new \DateTimeImmutable();
    }
}
