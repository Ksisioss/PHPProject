<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    private $client;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $userRepository = $this->client->getContainer()->get('doctrine')->getRepository(User::class);
        $existingUser = $userRepository->findOneBy(['email' => 'testuser@example.com']);

        if (!$existingUser) {
            $user = new User();
            $user->setEmail('testuser@example.com');
            $user->setFirstName('John');
            $user->setLastName('Doe');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword('password1');

            $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->user = $user; // Set the created user for reuse
        } else {
            $this->user = $existingUser;
        }

        $this->client->loginUser($this->user);
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', '/events');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'UPCOMING GRAND PRIX');
    }

    public function testNewEvent(): void
    {
        $crawler = $this->client->request('GET', '/event/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('AJOUTER')->form();
        $form['event[title]'] = 'Test Event';
        $form['event[description]'] = 'This is a test event';
        $form['event[date]'] = '2024-06-30 10:00:00';
        $form['event[location]'] = 'Test Location';
        $form['event[country]'] = 'Test Country';
        $form['event[maxUser]'] = 50;
        $form['event[public]'] = true;

        $this->client->submit($form);
        $this->assertResponseRedirects('/events');
    }

    public function testRegisterEvent(): void
    {
        $event = $this->createEventIfNeeded('Test Event');

        $this->client->request('GET', '/event/register/' . $event->getId());
        $this->assertResponseRedirects('/events');
    }

    public function testUnregisterEvent(): void
    {
        $event = $this->createEventIfNeeded('Test Event');

        $this->client->request('GET', '/event/register/' . $event->getId());
        $this->assertResponseRedirects('/events');

        $this->client->request('GET', '/event/unregister/' . $event->getId());
        $this->assertResponseRedirects('/events');
    }

    private function createEventIfNeeded(string $title): Event
    {
        $eventRepository = $this->client->getContainer()->get('doctrine')->getRepository(Event::class);
        $existingEvent = $eventRepository->findOneBy(['title' => $title]);

        if (!$existingEvent) {
            $event = new Event();
            $event->setTitle($title);
            $event->setDescription('This is a test event');
            $event->setDate(new \DateTime('2024-06-30 10:00:00'));
            $event->setLocation('Test Location');
            $event->setCountry('Test Country');
            $event->setMaxUser(50);
            $event->setPublic(true);

            $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $event;
        }

        return $existingEvent;
    }
}
