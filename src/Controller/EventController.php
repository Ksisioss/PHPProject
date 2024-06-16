<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Registration;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class EventController extends AbstractController
{
    private EmailService $emailService;
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EmailService $emailService, EntityManagerInterface $entityManager, Security $security)
    {
        $this->emailService = $emailService;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/events', name: 'event_list')]
    public function index(EventRepository $eventRepository, Security $security, RegistrationRepository $registrationRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $nameFilter = $request->query->get('name');
        $dateFilter = $request->query->get('date');
        $countryFilter = $request->query->get('country');

        $eventsQuery = $eventRepository->findAllOrderedByDate();

        $events = $paginator->paginate(
            $eventsQuery,
            $request->query->getInt('page', 1),
            10
        );

        $user = $security->getUser();
        $registrations = $registrationRepository->findBy(['user' => $user]);
        $eventRegistrations = [];
        foreach ($registrations as $registration) {
            $eventId = $registration->getEvent()->getId();
            $eventRegistrations[$eventId] = $registration;
        }

        $isFull = [];
        foreach ($events as $event) {
            $isFull[$event->getId()] = $event->getMaxUser() - count($registrationRepository->findBy(['event' => $event]));
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

        if ($countryFilter) {
            $events = array_filter($events, function ($event) use ($countryFilter) {
                return $event->getCountry() === $countryFilter;
            });
        }

        return $this->render('event/event_list.html.twig', [
            'events' => $events,
            'nameFilter' => $nameFilter,
            'dateFilter' => $dateFilter,
            'countryFilter' => $countryFilter,
            'eventRegistrations' => $eventRegistrations,
            'isFull' => $isFull,
        ]);
        }

    #[Route('/event/new', name: 'event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $countryCode = $form->get('country')->getData();
            try {
                $countryName = Countries::getName($countryCode);
            } catch (\Exception $e) {
                $countryName = $countryCode; // Fallback to country code if name is not found
            }

            $event->setCountry($countryName);

            // Check if a default image exists for the country
            $imagePath = 'img/countries/' . strtolower(str_replace(' ', '-', $countryName)) . '.png';
            if (!file_exists($this->getParameter('kernel.project_dir') . '/public/' . $imagePath)) {
                $imagePath = 'img/schedule/default.jpg'; // Set default image if country image is not found
            }

            $event->setImage($imagePath);
            $event->setCreatedBy($this->getUser());

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_list');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws \Exception
     */
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

        $this->addFlash('success', 'You have successfully registered for the event.');

        $subject = 'Confirmation d\'enregistrement';
        $body = sprintf('Hello %s, you have successfully registered for the event %s.', $user->getFirstName(), $event->getTitle());
        $this->emailService->sendEmail($user->getEmail(), $subject, $body);

        return $this->redirectToRoute('event_list');
    }

    #[Route('/event/unregister/{id}', name: 'event_unregister')]
    #[IsGranted('ROLE_USER')]
    public function unregister(Event $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $registration = $entityManager->getRepository(Registration::class)->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if ($registration) {
            $entityManager->remove($registration);
            $entityManager->flush();
            $this->addFlash('success', 'You have successfully unregistered from the event.');
        } else {
            $this->addFlash('danger', 'Unregistration failed. You were not registered for this event.');
        }

        $subject = 'Confirmation d\'annulation';
        $body = sprintf('Hello %s, you have successfully unregistered from the event %s.', $user->getFirstName(), $event->getTitle());
        $this->emailService->sendEmail($user->getEmail(), $subject, $body);

        return $this->redirectToRoute('event_list');
    }

    #[Route('/event/delete/{id}', name: 'event_delete', methods: ['GET'])]
    public function delete(Event $event, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('EVENT_DELETE', $event)) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        $entityManager->remove($event);
        $entityManager->flush();
        $this->addFlash('success', 'Event deleted successfully.');

        return $this->redirectToRoute('event_list');
    }

    // src/Controller/EventController.php

    // src/Controller/EventController.php

    #[Route('/event/edit/{id}', name: 'event_edit')]
    #[IsGranted('EVENT_EDIT', 'event')]
    public function edit(Event $event, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $countryCode = $form->get('country')->getData();
            try {
                $countryName = Countries::getName($countryCode);
            } catch (\Exception $e) {
                $countryName = $countryCode; // Fallback to country code if name is not found
            }

            $event->setCountry($countryName);

            // Handle image upload only if a file is provided
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('event_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle exception if something happens during file upload
                }

                $event->setImage($newFilename);
            } else {
                // Update image path based on country
                $imagePath = 'img/countries/' . strtolower(str_replace(' ', '-', $countryName)) . '.png';
                if (!file_exists($this->getParameter('kernel.project_dir') . '/public/' . $imagePath)) {
                    $imagePath = 'img/schedule/default.jpg'; // Set default image if country image is not found
                }
                $event->setImage($imagePath);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Event updated successfully.');

            return $this->redirectToRoute('event_list');
        }

        $countries = Countries::getNames();

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'countries' => $countries,
        ]);
    }



}

