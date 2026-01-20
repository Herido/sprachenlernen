<?php

namespace App\Controller;

use App\Repository\VocabularyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\LearningProgress;

class LearnController extends AbstractController
{
    #[Route('/learn', name: 'learn_menu')]
    public function menu(): Response
    {
        return $this->render('views/learn/menu.html.twig');
    }

  

#[Route('/learn/flashcards', name: 'learn_flashcards')]
public function flashcards(
    Request $request,
    VocabularyRepository $repo
): Response {
    $user = $this->getUser();

    // 🔎 Filter aus URL lesen (?language=EN-DE&level=A1&category=Alltag)
    $language = $request->query->get('language');
    $level    = $request->query->get('level');
    $category = $request->query->get('category');

    // 🔁 Umkehr-Modus (?reverse=1)
    $reverse = $request->query->getBoolean('reverse');

    // 🧠 Lernlogik aus Repository
    if ($language || $level || $category) {
        $vocabularies = $repo->findForLearning(
            language: $language,
            level: $level,
            category: $category,
            limit: 20
        );
    } else {
        // Standard: personalisierte Reihenfolge
        $vocabularies = $repo->getFlashcardsForUser($user);
    }

    // 🔀 Zufällige Reihenfolge (PHP, nicht SQL)
    shuffle($vocabularies);

    return $this->render('views/learn/flashcards.html.twig', [
        'vocabularies' => array_map(fn($v) => [
            'id' => $v->getId(),

            // 🔁 Umkehr-Modus
            'word' => $reverse
                ? implode(', ', (array) $v->getTranslations())
                : $v->getWord(),

            'translations' => $reverse
                ? [$v->getWord()]
                : (array) $v->getTranslations(),
        ], $vocabularies),

        // 🎯 Filter zurück ins Template
        'activeFilters' => [
            'language' => $language,
            'level' => $level,
            'category' => $category,
            'reverse' => $reverse,
        ],
    ]);
}

   #[Route('/lernen/quiz', name: 'learn_quiz')]
public function quiz(VocabularyRepository $repo): Response
{
    $data = array_map(fn($v) => [
        'word' => $v->getWord(),
        'translations' => $v->getTranslations(),
    ], $repo->findAll());

    return $this->render('views/learn/quiz.html.twig', [
        'vocabularies' => json_encode($data, JSON_THROW_ON_ERROR),
    ]);
}


    #[Route('/learn/multiple-choice', name: 'learn_multiple_choice')]
    public function multipleChoice(VocabularyRepository $repo): Response
    {
        $vocabularies = $repo->findBy([], null, 4);

        return $this->render('views/learn/multiple_choice.html.twig', [
            'vocabularies' => $vocabularies,
            'correct' => $vocabularies[0] ?? null,
        ]);
    }

        #[Route('/learn/progress', name: 'learn_progress', methods: ['POST'])]
        public function progress(
            Request $request,
            VocabularyRepository $vocabRepo,
            EntityManagerInterface $em
        ): JsonResponse {
            $data = json_decode($request->getContent(), true);

            $vocabulary = $vocabRepo->find($data['vocabularyId']);
            $user = $this->getUser();

            $progress = $em->getRepository(LearningProgress::class)->findOneBy([
                'user' => $user,
                'vocabulary' => $vocabulary
            ]);

            if (!$progress) {
                $progress = new LearningProgress();
                $progress->setUser($user);
                $progress->setVocabulary($vocabulary);
                $em->persist($progress);
            }

            if ($data['result'] === 'correct') {
                $progress->markCorrect();
            } else {
                $progress->markWrong();
            }

            $em->flush();

            return new JsonResponse(['status' => 'ok']);
        }






















}
