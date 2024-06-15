<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Registration;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class EventControllerTest extends WebTestCase
{

    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/events');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'UPCOMING GRAND PRIX');
    }

    public function testNewEvent(): void
    {
        $client = static::createClient();

        $userRepository = $client->getContainer()->get('doctrine')->getRepository(User::class);
        $existingUser = $userRepository->findOneBy(['email' => 'testuser@example.com']);

        if (!$existingUser) {
            $user = new User();
            $user->setEmail('testuser@example.com');
            $user->setFirstName('John');
            $user->setLastName('Doe');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword('password1');

            $entityManager = $client->getContainer()->get('doctrine')->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        } else {
            $user = $existingUser;
        }

        $client->loginUser($user);


        $crawler = $client->request('GET', '/event/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('AJOUTER')->form();
        $form['event[title]'] = 'Test Event';
        $form['event[description]'] = 'This is a test event';
        $form['event[date]'] = '2024-06-30 10:00:00';
        $form['event[location]'] = 'Test Location';
        $form['event[country]'] = 'Test Country';
        $form['event[maxUser]'] = 50;
        $form['event[public]'] = true;

        $client->submit($form);
        $this->assertResponseRedirects('/events');
    }

    public function testRegisterEvent(): void
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine')->getRepository(User::class);
        $existingUser = $userRepository->findOneBy(['email' => 'testuser@example.com']);

        if (!$existingUser) {
            $user = new User();
            $user->setEmail('testuser@example.com');
            $user->setFirstName('John');
            $user->setLastName('Doe');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword('password1');

            $entityManager = $client->getContainer()->get('doctrine')->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        } else {
            $user = $existingUser;
        }

        $client->loginUser($user);

        $eventRepository = $client->getContainer()->get('doctrine')->getRepository(Event::class);
        $existingEvent = $eventRepository->findOneBy(['title' => 'Test Event']);

        if (!$existingEvent) {
            $event = new Event();
            $event->setTitle('Test Event');
            $event->setDescription('This is a test event');
            $event->setDate(new \DateTime('2024-06-30 10:00:00'));
            $event->setLocation('Test Location');
            $event->setCountry('Test Country');
            $event->setMaxUser(50);
            $event->setPublic(true);

            $entityManager = $client->getContainer()->get('doctrine')->getManager();
            $entityManager->persist($event);
            $entityManager->flush();
        } else {
            $event = $existingEvent;
        }
        $client->request('GET', '/event/register/' . $event->getId());

        $this->assertResponseRedirects('/events');
    }

    public function testUnregisterEvent(): void
    {
        $client = static::createClient();
        $userRepository = $client->getContainer()->get('doctrine')->getRepository(User::class);
        $existingUser = $userRepository->findOneBy(['email' => 'testuser@example.com']);

        if (!$existingUser) {
            $user = new User();
            $user->setEmail('testuser@example.com');
            $user->setFirstName('John');
            $user->setLastName('Doe');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword('password1');

            $entityManager = $client->getContainer()->get('doctrine')->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        } else {
            $user = $existingUser;
        }

        $client->loginUser($user);

        $eventRepository = $client->getContainer()->get('doctrine')->getRepository(Event::class);
        $existingEvent = $eventRepository->findOneBy(['title' => 'Test Event']);

        if (!$existingEvent) {
            $event = new Event();
            $event->setTitle('Test Event');
            $event->setDescription('This is a test event');
            $event->setDate(new \DateTime('2024-06-30 10:00:00'));
            $event->setLocation('Test Location');
            $event->setCountry('Test Country');
            $event->setMaxUser(50);
            $event->setPublic(true);

            $entityManager = $client->getContainer()->get('doctrine')->getManager();
            $entityManager->persist($event);
            $entityManager->flush();
        } else {
            $event = $existingEvent;
        }
        $client->request('GET', '/event/register/' . $event->getId());

        $this->assertResponseRedirects('/events');

        $client->request('GET', '/event/unregister/' . $event->getId());

        $this->assertResponseRedirects('/events');
    }
}
