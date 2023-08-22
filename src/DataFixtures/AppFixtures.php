<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i < 10; $i++) {
            $phone = new Phone();
            $phone->setName("Name " .$i);
            $phone->setDescription("Description :" .$i);
            // Create a new DateTimeImmutable instance
            $createdAt = new \DateTimeImmutable();
            $phone->setCreatedAt($createdAt);
            $manager->persist($phone);

        }

        $manager->flush();
    }
}
