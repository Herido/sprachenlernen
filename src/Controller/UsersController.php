<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/users')]
class UsersController extends AbstractController
{
    #[Route('', name: 'admin_users')]
    public function index(UserRepository $repo): Response
    {
        return $this->render('views/admin/user.html.twig', [
            'users' => $repo->findAll(),
        ]);
    }

    #[Route('/toggle-role/{id}', name: 'admin_users_toggle_role')]
    public function toggleRole(User $user, EntityManagerInterface $em): Response
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            $user->setRoles(['ROLE_USER']);
        } else {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $em->flush();

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/delete/{id}', name: 'admin_users_delete')]
    public function delete(User $user, EntityManagerInterface $em): Response
    {
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Du kannst dich nicht selbst löschen.');
            return $this->redirectToRoute('admin_users');
        }

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('admin_users');
    }
}
