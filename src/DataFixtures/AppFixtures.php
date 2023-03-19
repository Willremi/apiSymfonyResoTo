<?php

namespace App\DataFixtures;

use App\Entity\Groupes;
use App\Entity\Regions;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        
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
            $manager->persist($groupe);
        }

        $manager->flush();
    }
}
