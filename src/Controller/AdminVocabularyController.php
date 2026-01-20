<?php

namespace App\Controller;

use App\Entity\Vocabulary;
use App\Repository\VocabularyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminVocabularyController extends AbstractController
{
    #[Route('/admin/vocabulary', name: 'admin_vocabulary')]
    public function index(VocabularyRepository $repo)
    {
        return $this->render('views/admin/vocabulary.html.twig', [
            'vocabularies' => $repo->findAll(),
        ]);
    }

    #[Route('/admin/vocabulary/manual', name: 'admin_vocabulary_manual', methods: ['POST'])]
    public function manual(Request $request, EntityManagerInterface $em)
    {
        $vocab = new Vocabulary();
        $vocab->setWord($request->request->get('word'));
        $vocab->setTranslations(array_map(
            'trim',
            explode(';', $request->request->get('translations'))
        ));
        $vocab->setLanguage($request->request->get('language'));
        $vocab->setLevel($request->request->get('level'));
        $vocab->setCategory($request->request->get('category'));
        $vocab->setExampleSentence($request->request->get('example_sentence'));

        $em->persist($vocab);
        $em->flush();

        $this->addFlash('success', 'Vokabel hinzugefügt');
        return $this->redirectToRoute('admin_vocabulary');
    }

    #[Route('/admin/vocabulary/import', name: 'admin_vocabulary_import', methods: ['POST'])]
    public function import(Request $request, EntityManagerInterface $em)
    {
        $file = $request->files->get('csv');

        if (!$file || $file->getClientOriginalExtension() !== 'csv') {
            $this->addFlash('danger', 'Ungültige CSV');
            return $this->redirectToRoute('admin_vocabulary');
        }

        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        $imported = $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            if (empty($data['word']) || empty($data['translations']) || empty($data['language'])) {
                $errors++;
                continue;
            }

            $vocab = new Vocabulary();
            $vocab->setWord($data['word']);
            $vocab->setTranslations(explode(';', $data['translations']));
            $vocab->setLanguage($data['language']);
            $vocab->setLevel($data['level'] ?? null);
            $vocab->setCategory($data['category'] ?? null);
            $vocab->setExampleSentence($data['example'] ?? null);

            $em->persist($vocab);
            $imported++;
        }

        fclose($handle);
        $em->flush();

        $this->addFlash('success', "$imported importiert, $errors Fehler");
        return $this->redirectToRoute('admin_vocabulary');
    }
}
