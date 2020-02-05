<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
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
    private $projectRepository;
    private $manager;
    private $validator;

    public function __construct(
        ProjectRepository $projectRepository,
        EntityManagerInterface $manager,
        ValidatorInterface $validator
    )
    {
        $this->projectRepository = $projectRepository;
        $this->manager = $manager;
        $this->validator = $validator;
    }

    /**
     * @Route("/api/project", name="api_get_projects", methods={"GET"})
     * @Route("/api/project/{id}", name="api_get_project", methods={"GET"})
     */
    public function index($id = null)
    {

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
        
        try {
            $project->setUpdatedAt(new \DateTime())
                ->setTitle($json->title ? $json->title : $project->title)
                ->setLink($json->link ? $json->link : $project->link)
                ->setSlug($json->title ? Str::slug($json->title) : $project->slug)
                ->setThumbnail($json->thumbnail ? $json->thumbnail : $project->thumbnail)
                ->setDescription($json->description ? $json->description : $project->description);

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
