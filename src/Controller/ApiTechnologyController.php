<?php
namespace App\Controller;

use App\Entity\Technology;
use App\Repository\TechnologyRepository;
use Doctrine\DBAL\DBALException;
use Illuminate\Support\Str;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ApiTechnologyController extends AbstractController
{
    private $technologyRepository;
    private $manager;

    public function __construct(
        TechnologyRepository $technologyRepository,
        EntityManagerInterface $manager
    )
    {
        $this->technologyRepository = $technologyRepository;
        $this->manager = $manager;
    }

    /**
     * @Route("/api/technology", name="api_get_technologies", methods={"GET"})
     * @Route("/api/technology/{id}", name="api_get_technology", methods={"GET"})
     */
    public function index($id = null)
    {
        if ($id) {
            return $this->json($this->technologyRepository->find($id), 200, [], ['groups' => 'get:technology']);
        } else {
            return $this->json($this->technologyRepository->findAll(), 200, [], ['groups' => 'get:technology']);
        }
    }

    /**
     * @Route("/api/technology", name="api_post_technology", methods={"POST"})
     */
    public function store(Request $request, SerializerInterface $serializer)
    {
        $json = $request->getContent(); 

        try {
            $technology = $serializer->deserialize($json, Technology::class, 'json');
    
            // Set Timestamp (TODO: Config this in SQL|Database|Entities)
            $technology->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->setSlug(Str::slug($technology->getName(), '-'));

            $this->manager->persist($technology);
            $this->manager->flush();   

            return $this->json($technology, 201, [], ['groups' => 'get:technology']);

        } catch(NotEncodableValueException $error) {
            return $this->json([
                'status' => 400,
                'message' => $error->getMessage()
            ]);
        }
    }

    /**
     * @Route("/api/technology/{id}", name="api_delete_technology", methods={"DELETE"})
     */
    public function delete($id)
    {
        $technology = $this->technologyRepository->find($id);

        try {
            $this->manager->remove($technology);
            $this->manager->flush();
    
            return $this->json([
                'status' => 200,
                'message' => "Technology deleted successfully"
            ]);
            
        } catch(DBALException $error) {
            
            return $this->json([
                'status' => 400,
                'message' => "Error : Technology deleted successfully",
                'code' => $error->getPrevious()->getCode()
            ]);
        }

    }

    /**
     * @Route("/api/technology", name="api_update_technology", methods={"PUT"})
     */
    public function update(Request $request)
    {
        $json = json_decode($request->getContent()); 
        $technology = $this->technologyRepository->find($json->id);

        try {
            $technology->setUpdatedAt(new \DateTime())
                ->setName($json->name ? $json->name : $technology->name)
                ->setSlug($json->name ? Str::slug($json->name) : $technology->slug)
                ->setDescription($json->description ? $json->description : $technology->description);

            $this->manager->persist($technology);
            $this->manager->flush();
            return $this->json($technology, 201, [], ['groups' => 'get:technology']);

        } catch(NotEncodableValueException $error) {

            return $this->json([
                'status' => 400,
                'message' => $error->getMessage()
            ]);

        }
    }
}
