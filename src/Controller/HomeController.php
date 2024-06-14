<?php

namespace App\Controller;

use App\Entity\Registration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login'); // Redirect to login if not authenticated
        }

        $registrations = $entityManager->getRepository(Registration::class)->findBy(['user' => $user]);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'registrations' => $registrations,
        ]);
    }
}
