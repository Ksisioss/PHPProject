<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Registration;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventController extends AbstractController
{
    #[Route('/event', name: 'event_list')]
    public function index(EventRepository $eventRepository, RegistrationRepository $registrationRepository): Response
    {
        $events = $eventRepository->findAll();
        $user = $this->getUser();

        // Fetch registrations for the current user indexed by event IDs
        $registrations = $registrationRepository->findBy(['user' => $user]);

        // Create an array to hold registrations indexed by event IDs
        $eventRegistrations = [];
        foreach ($registrations as $registration) {
            $eventId = $registration->getEvent()->getId();
            $eventRegistrations[$eventId] = $registration; // Registration object stored by event ID
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'eventRegistrations' => $eventRegistrations,
        ]);
    }

    #[Route('/event/new', name: 'event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_list');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/register/{id}', name: 'event_register')]
    #[IsGranted('ROLE_USER')]
    public function register(Event $event, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();

        $registration = new Registration();
        $registration->setEvent($event);
        $registration->setUser($user);
        $registration->setCreatedAt(new \DateTime());

        $entityManager->persist($registration);
        $entityManager->flush();


        return $this->redirectToRoute('event_list');
    }

    #[Route('/event/unregister/{id}', name: 'event_unregister')]
    #[IsGranted('ROLE_USER')]
    public function unregister(Event $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Find the existing registration to unregister
        $registration = $entityManager->getRepository(Registration::class)->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if ($registration) {
            // Remove the registration
            $entityManager->remove($registration);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_list');
    }


}
