<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\VocabularyRepository;
use App\Repository\LearningProgressRepository;


final class DefaultController extends AbstractController
{
  
    public function index()
    {
        // usually you'll want to make sure the user is authenticated first
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // returns your User object, or null if the user is not authenticated
        // use inline documentation to tell your editor your exact User class
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        return $user;
    }

#[Route('/dashboard', name: 'dashboardIndex')]
public function dashboard(
    LearningProgressRepository $progressRepo,
    VocabularyRepository $vocabRepo
): Response {
    $user = $this->getUser();

    // Learned words (>= 5 correct)
    $learnedWords = $progressRepo->countLearnedWords($user);

    // Accuracy
    $accuracy = $progressRepo->getAccuracy($user);

    // Streak
    $streak = $progressRepo->getStreak($user);

    // Languages
    $languages = $vocabRepo->countDistinctLanguages($user);

    // Today's learning plan
    $today = $progressRepo->findForToday($user, 10);

    return $this->render('views/dashboard.html.twig', [
        'stats' => [
            'learnedWords' => $learnedWords,
            'accuracy' => $accuracy,
            'streak' => $streak,
            'languages' => $languages,
        ],
        'today' => $today,
    ]);
}


 




}
