<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Services\SlugHelper;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ApiProjectController extends AbstractController
{
    /**
     * @Route("/api/project", name="api_get_projects", methods={"GET"})
     */
    public function index(ProjectRepository $projectRepository)
    {
        return $this->json($projectRepository->findAll(), 200, [], ['groups' => 'get:project']);
    }

    /**
     * @Route("/api/project", name="api_post_project", methods={"POST"})
     */
    public function store(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer)
    {
        $json = $request->getContent(); 

        try {
            $project = $serializer->deserialize($json, Project::class, 'json');
    
            // Set Timestamp (TODO: Config this in SQL|Database|Entities)
            $project->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->setSlug(SlugHelper::slugify($project->getTitle()));

            $manager->persist($project);
            $manager->flush();   

            return $this->json($project, 201, [], ['groups' => 'get:project']);

        } catch(NotEncodableValueException $error) {

            return $this->json([
                'status' => 400,
                'message' => $error->getMessage()
            ]);
        }
    }

    /**
     * @Route("/api/project/{id}", name="api_delete_project", methods={"DELETE"})
     */
    public function delete(Project $project, EntityManagerInterface $manager)
    {
        $manager->remove($project);
        $manager->flush();

        return $this->json([
            'status' => 200,
            'message' => "Project deleted successfully"
        ]);
    }
}
