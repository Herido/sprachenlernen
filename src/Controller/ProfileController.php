<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\DeleteAccountType;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile_index')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        ParameterBagInterface $params
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile) {
                $avatarDir = $params->get('app.avatar_dir');

                $safeName = bin2hex(random_bytes(8));
                $ext = $avatarFile->guessExtension() ?: 'jpg';
                $newFilename = $safeName.'.'.$ext;

                if ($user->getAvatarFilename()) {
                    $old = $avatarDir.'/'.$user->getAvatarFilename();
                    if (is_file($old)) @unlink($old);
                }

                try {
                    $avatarFile->move($avatarDir, $newFilename);
                    $user->setAvatarFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Upload fehlgeschlagen.');
                }
            }

            $em->flush();
            $this->addFlash('success', 'Profil gespeichert!');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('views/profile/profileIndex.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/password', name: 'profile_password')]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $currentPassword = (string) $form->get('currentPassword')->getData();
            if (!$hasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('danger', 'Aktuelles Passwort ist falsch.');
                return $this->redirectToRoute('profile_password');
            }

            $newPassword = (string) $form->get('newPassword')->getData();
            $user->setPassword($hasher->hashPassword($user, $newPassword));

            $em->flush();
            $this->addFlash('success', 'Passwort geändert!');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('views/profile/profilPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/delete', name: 'profile_delete')]
    public function deleteAccount(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ParameterBagInterface $params    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(DeleteAccountType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pw = (string) $form->get('password')->getData();
            if (!$hasher->isPasswordValid($user, $pw)) {
                $this->addFlash('danger', 'Passwort ist falsch.');
                return $this->redirectToRoute('profile_delete');
            }

            $avatarDir = $params->get('app.avatar_dir');
            if ($user->getAvatarFilename()) {
                $path = $avatarDir.'/'.$user->getAvatarFilename();
                if (is_file($path)) @unlink($path);
            }

            $em->remove($user);
            $em->flush();

            $this->addFlash('success', 'Dein Account wurde gelöscht.');
        return $this->redirectToRoute('app_logout');
        }

        return $this->render('views/profile/profilDelete.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
