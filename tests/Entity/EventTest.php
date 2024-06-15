<?php
// tests/Entity/EventTest.php

namespace App\Tests\Entity;

use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $event = new Event();

        $event->setId(1);
        $this->assertEquals(1, $event->getId());

        $event->setTitle('Test Event');
        $this->assertEquals('Test Event', $event->getTitle());

        $event->setDescription('Description of the event');
        $this->assertEquals('Description of the event', $event->getDescription());

        $date = new \DateTime();
        $event->setDate($date);
        $this->assertEquals($date, $event->getDate());

        $event->setLocation('Test Location');
        $this->assertEquals('Test Location', $event->getLocation());

        $event->setMaxUser(100);
        $this->assertEquals(100, $event->getMaxUser());

        $event->setCountry('Test Country');
        $this->assertEquals('Test Country', $event->getCountry());

        $event->setPublic(true);
        $this->assertTrue($event->isPublic());
    }

}
