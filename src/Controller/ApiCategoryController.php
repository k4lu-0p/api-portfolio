<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Illuminate\Support\Str;


class ApiCategoryController extends AbstractController
{
    private $projectRepository;
    private $categoryRepository;
    private $manager;

    public function __construct(
        ProjectRepository $projectRepository,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $manager
    )
    {
        $this->categoryRepository = $categoryRepository;
        $this->projectRepository = $projectRepository;
        $this->manager = $manager;
    }

    /**
     * @Route("/api/category", name="api_get_categories", methods={"GET"})
     * @Route("/api/category/{id}", name="api_get_category", methods={"GET"})
     */
    public function index($id = null)
    {
        if ($id) {
            return $this->json($this->categoryRepository->find($id), 200, [], ['groups' => 'get:category']);
        } else {
            return $this->json($this->categoryRepository->findAll(), 200, [], ['groups' => 'get:category']);
        }
    }

    /**
     * @Route("/api/category", name="api_post_category", methods={"POST"})
     */
    public function store(Request $request, SerializerInterface $serializer) {

        $json = $request->getContent(); 

        try {
            $category = $serializer->deserialize($json, Category::class, 'json');
    
            // Set Timestamp (TODO: Config this in SQL|Database|Entities)
            $category->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->setSlug(Str::slug($category->getLabel(), '-'));

            $this->manager->persist($category);
            $this->manager->flush();   

            return $this->json($category, 201, [], ['groups' => 'get:category']);

        } catch(NotEncodableValueException $error) {

            return $this->json([
                'status' => 400,
                'message' => $error->getMessage()
            ]);
        }
    }

    /**
     * @Route("/api/category/{id}", name="api_category_project", methods={"DELETE"})
     */
    public function delete($id)
    {
        $category = $this->categoryRepository->find($id);

        try {
            $this->manager->remove($category);
            $this->manager->flush();
    
            return $this->json([
                'status' => 200,
                'message' => "Category deleted successfully"
            ]);
            
        } catch(DBALException $error) {
            
            return $this->json([
                'status' => 400,
                'message' => "Error : Category deleted successfully",
                'code' => $error->getPrevious()->getCode()
            ]);
        }
    }

    /**
     * @Route("/api/category", name="api_update_category", methods={"PUT"})
     */
    public function update(Request $request)
    {

        $json = json_decode($request->getContent()); 
        $category = $this->categoryRepository->find($json->id);

        // relation project si un ou plusieurs projects envoyé
        if(isset($json->projects_id)) {
            foreach ($json->projects_id as $project_id) {
                $project = $this->projectRepository->find($project_id);
                $category->addProject($project);
            }
        }
        
        try {
            $category->setUpdatedAt(new \DateTime())
                ->setLabel(isset($json->label) ? $json->label : $category->getLabel())
                ->setSlug(isset($json->label) ? Str::slug($json->label) : $category->getSlug());

            $this->manager->persist($category);
            $this->manager->flush();
            return $this->json($category, 201, [], ['groups' => 'get:category']);

        } catch(NotEncodableValueException $error) {

            return $this->json([
                'status' => 400,
                'message' => $error->getMessage()
            ]);

        }
    }
}
