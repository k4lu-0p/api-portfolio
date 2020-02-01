<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Illuminate\Support\Str;

use App\Entity\Project;
use App\Entity\Technology;
use App\Entity\Customer;
use App\Entity\Category;

class PortfolioFixtures extends Fixture
{
    /**
     * @var array - Add technologies here.
     */
    private $technologies = [
        [
            'name' => 'Tailwind'
        ], 
        [
            'name' => 'NuxtJS'
        ],
        [
            'name' => 'Laravel'
        ],
        [ 
            'name' => 'Symfony'
        ]
    ];

    /**
     * @var array - Add projects here.
     */
    private $projects = [
        [
            'title' => 'HSBC campagne F16',
            'link' => 'http://k-lu.fr'
        ], 
        [
            'title' => 'Itélios refonte',
            'link' => 'http://k-lu.fr'
        ],
        [
            'title' => 'Mon portfolio',
            'link' => 'http://k-lu.fr'
        ],
        [
            'title' => 'Hermes',
            'link' => 'http://k-lu.fr'
        ]
    ];

    /**
     * @var array - Add customers here.
     */
    private $customers = [
        [
            'name' => 'Lucas Robin',
            'link' => 'http://k-lu.fr'
        ],
        [
            'name' => 'HSBC',
            'link' => 'http://k-lu.fr'
        ], 
        [
            'name' => 'Itélios',
            'link' => 'http://k-lu.fr'
        ],
        [
            'name' => 'NetMediaGroup',
            'link' => 'http://k-lu.fr'
        ]
    ];

    /**
     * @var array - Add categories
     */
    private $categories = [
        [
            'label' => 'Archivé(s)'
        ],
        [
            'label' => 'Récent(s)'
        ],
        [
            'label' => 'Application(s) Mobile(s)',
        ],
        [
            'label' => 'Application(s) Web'
        ],
        [
            'label' => 'Site(s) Web'
        ]
    ];

    /**
     * @param ObjectManager $manager 
     */
    public function load(ObjectManager $manager)
    {
        // Use faker for generate fake data.
        $faker = Factory::create('fr_FR');

        // Create customers.
        foreach ($this->customers as $dataCustomer) {

            $customer = new Customer();

            $customer->setName($dataCustomer['name'])
                ->setSlug(Str::slug($dataCustomer['name'], '-'))
                ->setDescription($faker->text)
                ->setLink($dataCustomer['link'])
                ->setLogo($faker->imageUrl(640, 480))
                ->setCreatedAt(new \DateTime)
                ->setUpdatedAt(new \DateTime);

            $manager->persist($customer);
            $customers[] = $customer; // Save all new customers created
        }

        // Create categories.
        foreach ($this->categories as $dataCategory) {

            $category = new Category();

            $category->setLabel($dataCategory['label'])
                ->setSlug(Str::slug($dataCategory['label'], '-'))
                ->setCreatedAt(new \DateTime)
                ->setUpdatedAt(new \DateTime);

            $manager->persist($category);
            $categories[] = $category; // Save all technologies created.
        }

        // Create technologies.
        foreach ($this->technologies as $dataTechnology) {

            $technology = new Technology();

            $technology->setName($dataTechnology['name'])
                ->setSlug(Str::slug($dataTechnology['name'], '-'))
                ->setDescription($faker->text)
                ->setCreatedAt(new \DateTime)
                ->setUpdatedAt(new \DateTime);

            $manager->persist($technology);
            $technologies[] = $technology; // Save all technologies created.
        }

        // Create projects.
        foreach ($this->projects as $dataProject) {

            $project = new Project();

            $project->setTitle($dataProject['title'])
                ->setDescription($faker->text)
                ->setLink($dataProject['link'])
                ->setSlug(Str::slug($dataProject['title'], '-'))
                ->setThumbnail($faker->imageUrl(640, 480))
                ->setCreatedAt(new \DateTime)
                ->setUpdatedAt(new \DateTime);

            // Add technologies to current project.
            foreach ($technologies as $technology) {
                $project->addTechnology($technology);       
            }

            // Add categories to current project.
            foreach ($categories as $category) {
                $project->addCategory($category);
            }

            // Add one customer to projects (Lucas Robin).
            $project->setCustomer($customers[0]);

            $manager->persist($project);
        }

        $manager->flush();
    }

}
