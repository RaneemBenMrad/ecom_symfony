<?php

namespace App\DataFixtures;

use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker\Factory as FakerFactory;

class ProductsFixtures extends Fixture
{
    private $batchSize = 20; // Taille des lots pour les flush
    private $faker;

    public function __construct(private SluggerInterface $slugger)
    {
        $this->faker = FakerFactory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        for ($prod = 1; $prod <= 10; $prod++) {
            $product = new Products();
            $product->setName(substr($this->faker->text(15), 0, 15));  // Limiter à 15 caractères
            $product->setDescription($this->faker->text());
            $product->setSlug($this->slugger->slug($product->getName())->lower());
            $product->setPrice($this->faker->numberBetween(900, 150000));
            $product->setStock($this->faker->numberBetween(0, 10));

            // On va chercher une référence de catégorie existante
            $category = $this->getReference('cat-' . rand(1, 8));  // Assure-toi que les catégories sont créées
            $product->setCategories($category);

            $this->setReference('prod-' . $prod, $product);
            $manager->persist($product);

            if (($prod % $this->batchSize) === 0) {
                $manager->flush();
                $manager->clear(); // Libère la mémoire
            }
        }

        $manager->flush();  // Final flush pour le reste des entités
    }
}
