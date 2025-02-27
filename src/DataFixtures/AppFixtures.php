<?php

namespace App\DataFixtures;

use App\Entity\Community;
use App\Entity\MembreComunity;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $user= new User();
            $user->setUsername("user$i");
            $user->setPassword("User$i$i$i@");
            $user->setEmail("user$i@syncylinky.tn");
            $user->setRoles("ROLE_USER");
            $user->setFirstname("user$i");
            $user->setName("user$i");
            $user->setGender('autres');
            $user->setDateOB(new \DateTime('01-01-2000'));
            $manager->persist($user);
        }
        $manager->flush();
    }
    public function load2(ObjectManager $manager): void
    {
        $communities = $manager->getRepository(Community::class)->findAll();

        if (empty($communities)) {
            throw new \Exception("No communities found! Please create some communities first.");
        }

        $users = $manager->getRepository(User::class)->findBy([], null, 20);

        foreach ($users as $user) {
            $randomCommunity = $communities[array_rand($communities)];

            $membreComunity = new MembreComunity();
            $membreComunity->setIdUser($user);
            $membreComunity->setIdCommunity($randomCommunity);
            $membreComunity->setStatus("membre");
            $membreComunity->setDateAdhesion(new \DateTime());

            // Persist the new MembreComunity entity
            $manager->persist($membreComunity);
        }

        $manager->flush();
    }
}
