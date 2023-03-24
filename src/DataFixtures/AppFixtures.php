<?php

namespace App\DataFixtures;

use App\Entity\Groupes;
use App\Entity\Regions;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        // Création d'un user "normal"
        $user = new Users();
        $user->setEmail('user@reseau_to.fr');
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);

        // Création d'un user admin
        $userAdmin = new Users();
        $userAdmin->setEmail('admin@reseau_to.fr');
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);
        
        // Création des régions
        $listRegion = [];
        for ($i=0; $i < 10; $i++) { 
            $region = new Regions();
            $region->setName('Région ' . $i);
            $manager->persist($region);
            // Sauvegarde de l'auteur créé dans un tableau
            $listRegion[] = $region;
        }

        // Création d'une vingtaine de groupes
        for ($i=0; $i < 20; $i++) { 
            $groupe = new Groupes;
            $groupe->setName('Groupe n°' . $i);
            $groupe->setDescription('Description ' . $i);
            $groupe->setContact('Contact n°' . $i);
            // Liaison du groupe à une région pris au hasard dans le tableau des régions
            $groupe->setRegions($listRegion[array_rand($listRegion)]);
            $groupe->setEmail('Email n°' . $i);
            $manager->persist($groupe);
        }

        $manager->flush();
    }
}
