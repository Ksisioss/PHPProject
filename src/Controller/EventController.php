<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/events', name: 'event_list')]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        $nameFilter = $request->query->get('name');
        $dateFilter = $request->query->get('date');

        $events = $eventRepository->findAll();

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
        ]);
    }
}

