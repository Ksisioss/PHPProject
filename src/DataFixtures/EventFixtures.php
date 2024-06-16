<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $currentUser = $this->getReference('admin-user');

        $events = [
            [
                'title' => 'Spanish Grand Prix',
                'description' => 'Spanish Grand Prix',
                'date' => new DateTime('2024-06-23 13:30:00'),
                'location' => 'Barcelona',
                'country' => 'Spain',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Austrian Grand Prix',
                'description' => 'Austrian Grand Prix',
                'date' => new DateTime('2024-06-30 12:30:00'),
                'location' => 'Spielberg',
                'country' => 'Austria',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'United Kingdom Grand Prix',
                'description' => 'United Kingdom Grand Prix',
                'date' => new DateTime('2024-07-07 13:30:00'),
                'location' => 'Silverstone',
                'country' => 'United Kingdom',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Hungarian Grand Prix',
                'description' => 'Hungarian Grand Prix',
                'date' => new DateTime('2024-07-21 13:30:00'),
                'location' => 'Budapest',
                'country' => 'Hungary',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Belgian Grand Prix',
                'description' => 'Belgian Grand Prix',
                'date' => new DateTime('2024-07-28 13:30:00'),
                'location' => 'Spa-Francorchamps',
                'country' => 'Belgium',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Dutch Grand Prix',
                'description' => 'Dutch Grand Prix',
                'date' => new DateTime('2024-08-25 13:30:00'),
                'location' => 'Zandvoort',
                'country' => 'Netherlands',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Italian Grand Prix',
                'description' => 'Italian Grand Prix',
                'date' => new DateTime('2024-09-01 11:30:00'),
                'location' => 'Monza',
                'country' => 'Italy',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Azerbaijan Grand Prix',
                'description' => 'Azerbaijan Grand Prix',
                'date' => new DateTime('2024-09-15 11:30:00'),
                'location' => 'Baku',
                'country' => 'Azerbaijan',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Singapore Grand Prix',
                'description' => 'Singapore Grand Prix',
                'date' => new DateTime('2024-09-22 13:30:00'),
                'location' => 'Marina Bay',
                'country' => 'Singapore',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'United States Grand Prix',
                'description' => 'United States Grand Prix',
                'date' => new DateTime('2024-10-20 19:30:00'),
                'location' => 'Austin',
                'country' => 'United States',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Mexican Grand Prix',
                'description' => 'Mexican Grand Prix',
                'date' => new DateTime('2024-10-27 20:30:00'),
                'location' => 'Mexico City',
                'country' => 'Mexico',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Brazilian Grand Prix',
                'description' => 'Brazilian Grand Prix',
                'date' => new DateTime('2024-11-03 15:30:00'),
                'location' => 'São Paulo',
                'country' => 'Brazil',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Las Vegas Grand Prix',
                'description' => 'Las Vegas Grand Prix',
                'date' => new DateTime('2024-11-23 03:30:00'),
                'location' => 'Las Vegas',
                'country' => 'United States',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Qatar Grand Prix',
                'description' => 'Qatar Grand Prix',
                'date' => new DateTime('2024-12-01 14:00:00'),
                'location' => 'Losail',
                'country' => 'Qatar',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
            [
                'title' => 'Abu Dhabi Grand Prix',
                'description' => 'Abu Dhabi Grand Prix',
                'date' => new DateTime('2024-12-08 10:30:00'),
                'location' => 'Yas Marina',
                'country' => 'United Arab Emirates',
                'maxUser' => 200,
                'public' => true,
                'createdBy' => $currentUser,
            ],
        ];

        foreach ($events as $eventData) {
            $event = new Event();
            $event->setTitle($eventData['title']);
            $event->setDescription($eventData['description']);
            $event->setDate($eventData['date']);
            $event->setLocation($eventData['location']);
            $event->setCountry($eventData['country']);
            $event->setMaxUser($eventData['maxUser']);
            $event->setPublic($eventData['public']);
            $event->setCreatedBy($eventData['createdBy']);
            $event->setImage('img/countries/' . strtolower(str_replace(' ', '-', $eventData['country'])) . '.png');
            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixture::class,
        ];
    }
}
