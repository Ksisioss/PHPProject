<?php
namespace App\Controller;

use App\Form\ProfileFormType;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully');
        }

        return $this->render('user/profile.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    #[Route('/users', name: 'user_list')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        // Rendre la vue avec la liste des utilisateurs
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

}