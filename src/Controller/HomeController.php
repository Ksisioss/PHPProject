<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findBy([], ['date' => 'ASC']);
        $firstUpcomingEvent = $events[0] ?? null;

        // Group events by country for tabs
        $groupedEvents = [];
        foreach ($events as $event) {
            $country = $event->getCountry();
            $groupedEvents[$country][] = $event;
        }

        return $this->render('home/home.html.twig', [
            'events' => $events,
            'groupedEvents' => $groupedEvents,
            'firstUpcomingEvent' => $firstUpcomingEvent,
            'allEvents' => $events
        ]);
    }
}
