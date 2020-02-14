<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\CategoryRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProjectRepository;
use App\Repository\TechnologyRepository;
use App\Services\UploadService;
use Doctrine\DBAL\DBALException;
use Illuminate\Support\Str;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiProjectController extends AbstractController
{
    private $customerRepository;
    private $projectRepository;
    private $categoryRepository;
    private $technologyRepository;
    private $manager;
    private $validator;

    public function __construct(
        ProjectRepository $projectRepository,
        CustomerRepository $customerRepository,
        CategoryRepository $categoryRepository,
        TechnologyRepository $technologyRepository,
        EntityManagerInterface $manager,
        ValidatorInterface $validator
    )
    {
        $this->customerRepository = $customerRepository;
        $this->projectRepository = $projectRepository;
        $this->categoryRepository = $categoryRepository;
        $this->technologyRepository = $technologyRepository; 
        $this->manager = $manager;
        $this->validator = $validator;
    }

    /**
     * @Route("/api/project", name="api_get_projects", methods={"GET"})
     * @Route("/api/project/{id}", name="api_get_project", methods={"GET"})
     */
    public function index($id = null)
    {
        // dd($this->manager->getRepository(\App\Entity\AuthToken::class)->find(1)->getUser());

        if ($id) {
            return $this->json($this->projectRepository->find($id), 200, [], ['groups' => 'get:project']);
        } else {
            return $this->json($this->projectRepository->findAll(), 200, [], ['groups' => 'get:project']);
        }
    }

    /**
     * @Route("/api/project", name="api_post_project", methods={"POST"})
     */
    public function store(Request $request, SerializerInterface $serializer)
    {
        $json = $request->getContent(); 

        try {
            $project = $serializer->deserialize($json, Project::class, 'json');
            
            // Propriétés ajoutées automatiquements.
            $project->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->setSlug(Str::slug($project->getTitle(), '-'));
            // Decode, create, move et retourne le nom du fichier créé.
            $thumbnail_decoded = UploadService::handle( // Voir dans Services
                $project->getThumbnail(),
                $project->getSlug(),
                $this->getParameter('uploads_directory'),
                "jpg"
            );
            
            $project->setThumbnail("uploads/{$thumbnail_decoded}"); // Ajout du chemin definitif de l'image uploadé.

        } catch(NotEncodableValueException $error) {
            return $this->json([
                'status' => 400,
                'message' => $error->getMessage()
            ]);
        }

        // Validation avant enregistrement et renvoi.
        $violations = $this->validator->validate($project);
        if($violations->count() > 0) {
            return $this->json($violations, 400);
        } else {
            $this->manager->persist($project);
            $this->manager->flush(); 
            return $this->json($project, 201, [], ['groups' => 'get:customer']); 
        }
    }

    /**
     * @Route("/api/project/{id}", name="api_delete_project", methods={"DELETE"})
     */
    public function delete($id)
    {
        $project = $this->projectRepository->find($id);

        try {
            $this->manager->remove($project);
            $this->manager->flush();
    
            return $this->json([
                'status' => 200,
                'message' => "Project deleted successfully"
            ]);
            
        } catch(DBALException $error) {
            
            return $this->json([
                'status' => 400,
                'message' => "Error : Project deleted successfully",
                'code' => $error->getPrevious()->getCode()
            ]);
        }
    }

    /**
     * @Route("/api/project", name="api_update_project", methods={"PUT"})
     */
    public function update(Request $request)
    {

        $json = json_decode($request->getContent()); 
        $project = $this->projectRepository->find($json->id);

        // relation customer si customer envoyé
        if(isset($json->customer_id)) {
            $customer = $this->customerRepository->find($json->customer_id);
            $project->setCustomer($customer);
        }

        // relation technology si une ou plusieurs technologies envoyé
        if(isset($json->technologies_id)) {
            foreach ($json->technologies_id as $technology_id) {
                $technology = $this->technologyRepository->find($technology_id);
                $project->addTechnology($technology);
            }
        }

        // relation category si une ou plusieurs categories envoyé
        if(isset($json->categories_id)) {
            foreach ($json->categories_id as $category_id) {
                $category = $this->categoryRepository->find($category_id);
                $project->addCategory($category);
            }
        }
        
        try {
            $project->setUpdatedAt(new \DateTime())
                ->setTitle(isset($json->title) ? $json->title : $project->getTitle())
                ->setLink(isset($json->link) ? $json->link : $project->getLink())
                ->setSlug(isset($json->title) ? Str::slug($json->title) : $project->getSlug())
                ->setThumbnail(isset($json->thumbnail) ? $json->thumbnail : $project->getThumbnail())
                ->setDescription(isset($json->description) ? $json->description : $project->getDescription());

            $this->manager->persist($project);
            $this->manager->flush();
            return $this->json($project, 201, [], ['groups' => 'get:project']);

        } catch(NotEncodableValueException $error) {

            return $this->json([
                'status' => 400,
                'message' => $error->getMessage()
            ]);

        }
    }
}

