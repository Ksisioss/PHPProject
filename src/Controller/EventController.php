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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventController extends AbstractController
{
    #[Route('/events', name: 'event_list')]
    public function index(Request $request, EventRepository $eventRepository, RegistrationRepository $registrationRepository): Response
    {
        $nameFilter = $request->query->get('name');
        $dateFilter = $request->query->get('date');

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

        if ($nameFilter) {
            $events = array_filter($events, function ($event) use ($nameFilter) {
                return stripos($event->getTitle(), $nameFilter) !== false;
            });
        }

        if ($dateFilter) {
            $events = array_filter($events, function ($event) use ($dateFilter) {
                return $event->getDate()->format('Y-m-d') === $dateFilter;
            });
        }

        // Group events by date
        $groupedEvents = [];
        foreach ($events as $event) {
            $date = $event->getDate()->format('Y-m-d');
            $groupedEvents[$date][] = $event;
        }

        return $this->render('event/event_list.html.twig', [
            'groupedEvents' => $groupedEvents,
            'nameFilter' => $nameFilter,
            'dateFilter' => $dateFilter,
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

