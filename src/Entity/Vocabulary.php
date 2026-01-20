<?php

namespace App\Entity;

use App\Repository\VocabularyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VocabularyRepository::class)]
class Vocabulary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $word;

    #[ORM\Column(type: 'json')]
    private array $translations = [];

    #[ORM\Column(length: 20)]
    private string $language;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $level = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $exampleSentence = null;

    /* =========================
       GETTER & SETTER
       ========================= */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function setWord(string $word): self
    {
        $this->word = trim($word);
        return $this;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function setTranslations(array $translations): self
    {
        // Leere Werte entfernen & trimmen
        $this->translations = array_values(array_filter(
            array_map('trim', $translations)
        ));

        return $this;
    }

    public function addTranslation(string $translation): self
    {
        $translation = trim($translation);

        if ($translation !== '' && !in_array($translation, $this->translations, true)) {
            $this->translations[] = $translation;
        }

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = strtoupper(trim($language));
        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level ? strtoupper(trim($level)) : null;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category ? trim($category) : null;
        return $this;
    }

    public function getExampleSentence(): ?string
    {
        return $this->exampleSentence;
    }

    public function setExampleSentence(?string $exampleSentence): self
    {
        $this->exampleSentence = $exampleSentence ? trim($exampleSentence) : null;
        return $this;
    }
}
