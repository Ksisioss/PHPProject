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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventController extends AbstractController
{
    private EmailService $emailService;


    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    #[Route('/event', name: 'event_list')]
    public function index(EventRepository $eventRepository, Security $security, RegistrationRepository $registrationRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $security->getUser();

        $eventsQuery = $eventRepository->findAll();

        $events = $paginator->paginate(
            $eventsQuery,
            $request->query->getInt('page', 1),
            10
        );

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

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'eventRegistrations' => $eventRegistrations,
            'isFull' => $isFull
        ]);
    }

    #[Route('/event/new', name: 'event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        $user = $this->getUser();
        $event->setCreatedBy($user);
        if ($form->isSubmitted() && $form->isValid()) {
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

        $subject = 'Confirmation d\'enregistrement';
        $body = sprintf('Bonjour %s, votre inscription à l\'événement %s a été confirmé.', $user->getFirstName(), $event->getTitle());
        $this->emailService->sendEmail($user->getEmail(), $subject, $body);

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

        $subject = 'Confirmation d\'annulation';
        $body = sprintf('Bonjour %s, votre inscription à l\'événement %s a été annulée.', $user->getFirstName(), $event->getTitle());
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

        return $this->redirectToRoute('event_list');
    }

    #[Route('/event/edit/{id}', name: 'event_edit')]
    #[IsGranted('EVENT_EDIT', 'event')]
    public function edit(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('event_list');
        }

        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
}
