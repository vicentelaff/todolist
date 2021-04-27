<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Todo;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        # 1 Tableau de catégories
        $categories = ["Business", "Personal", "Important"];
        # Je stocke tous les objets créés dans la boucle dans le tableau suivant:
        $tabObjectsCategory = [];
        foreach ($categories as $c){
            $cat = new Category;
            $cat->setName($c);
            $manager->persist($cat);
            $tabObjectsCategory[] = $cat; # == array_push($tabObjectsCategory, $cat)
        }

        # 2 Créer autant d'objets de type Category qu'il y en a dans le tableau

        # 3 Créer une ou plusieurs TodoList.
        $todo = new Todo;
        $todo
            ->setTitle("Initialiser le projet")
            ->setContent("Un tas de trucs à dire la dessus, heh hoh")
            ->setDeadline(new \DateTime("Europe/Paris"))
            ->setCategory($tabObjectsCategory[0]);
        $manager->persist($todo);

        $manager->flush();
    }

}
