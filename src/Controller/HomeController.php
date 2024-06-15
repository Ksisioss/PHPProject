<?php

namespace App\Controller;


use App\Repository\EventRepository;
use App\Entity\Registration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $events = $eventRepository->findBy([], ['date' => 'ASC']);
        $firstUpcomingEvent = $events[0] ?? null;

        // Group events by country for tabs
        $groupedEvents = [];
        foreach ($events as $event) {
            $country = $event->getCountry();
            $groupedEvents[$country][] = $event;
        }

        $user = $this->getUser();

        if (!$user) {
            return $this->render('home/home.html.twig', [
                'controller_name' => 'HomeController',
                'events' => $events,
                'groupedEvents' => $groupedEvents,
                'firstUpcomingEvent' => $firstUpcomingEvent,
                'allEvents' => $events,
                'registrations' => null,
            ]);
        }

        $registrations = $entityManager->getRepository(Registration::class)->findBy(['user' => $user]);

        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'registrations' => $registrations,
            'events' => $events,
            'groupedEvents' => $groupedEvents,
            'firstUpcomingEvent' => $firstUpcomingEvent,
            'allEvents' => $events,
        ]);
    }
}
