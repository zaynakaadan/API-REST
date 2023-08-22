<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Phone;
use App\Entity\Client;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{   
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {  
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i=0; $i < 10; $i++) {
            $phone = new Phone();
            $phone->setName("Name " .$i);
            $phone->setDescription("Description :" .$i);
            // Create a new DateTimeImmutable instance
            $createdAt = new \DateTimeImmutable();
            $phone->setCreatedAt($createdAt);
            $manager->persist($phone);

        }

        for ($c=0; $c < 4; $c++) {
            $client = new Client();            
            $client->setEmail("client$c@bilemo.com");
            $client->setPassword($this->userPasswordHasher->hashPassword($client, "passwordClient$c"));
            $client->setName($faker->company());
            $datetime = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now'));
            $client->setCreatedAt($datetime);
            $clients[] = $client;
            $manager->persist($client);
        }

        for ($j=0; $j < 15; $j++) {
            $user = new User();
            $user->setFirstname($faker->firstname());
            $user->setLastname($faker->lastName());
            $user->setClient($faker->randomElement($clients));
            $createdAt = new \DateTimeImmutable();
            $user->setCreatedAt($createdAt);
            $manager->persist($user);

        }

        $manager->flush();
    }
}
