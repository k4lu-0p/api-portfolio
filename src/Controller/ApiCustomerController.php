<?php
namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Repository\ProjectRepository;
use App\Services\UploadService;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Str;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiCustomerController extends AbstractController
{
    private $customerRepository;
    private $projectRepository;
    private $manager;
    private $validator;

    public function __construct(
        ProjectRepository $projectRepository,
        CustomerRepository $customerRepository,
        EntityManagerInterface $manager,
        ValidatorInterface $validator
    )
    {
        $this->projectRepository = $projectRepository;
        $this->customerRepository = $customerRepository;
        $this->manager = $manager;
        $this->validator = $validator;
    }

    /**
     * @Route("/api/customer", name="api_get_technologies", methods={"GET"})
     * @Route("/api/customer/{id}", name="api_get_customer", methods={"GET"})
     */
    public function index($id = null)
    {
        if ($id) {
            return $this->json($this->customerRepository->find($id), 200, [], ['groups' => 'get:customer']);
        } else {
            return $this->json($this->customerRepository->findAll(), 200, [], ['groups' => 'get:customer']);
        }
    }

    /**
     * @Route("/api/customer", name="api_post_customer", methods={"POST"})
     */
    public function store(Request $request, SerializerInterface $serializer)
    {
        if ($request->headers->get('Content-Type') === 'application/json') { // Si on reçoit du JSON.
            
            $json = $request->getContent(); // Body de la requête.
            
            try { // Tente de normalizer et d'encoder l'objet...

                $customer = $serializer->deserialize($json, Customer::class, 'json'); // Nouveau client.
                $customer->setCreatedAt(new \DateTime())
                ->setUpdatedAt(new \DateTime())
                ->setSlug(Str::slug($customer->getName(), '-'));

                // Decode, create, move et retourne le nom du fichier créé.
                $logo_decoded = UploadService::handle( // Voir dans Services
                    $customer->getLogo(),
                    $customer->getSlug(),
                    $this->getParameter('uploads_directory'),
                    "jpg"
                );

                $customer->setLogo("uploads/{$logo_decoded}"); // Ajout du chemin definitif de l'image uploadé.

            } catch(NotEncodableValueException $error) {
                return $this->json([
                    'status' => 400,
                    'message' => $error->getMessage()
                ]);
            }
        }
        
        // Validation avant enregistrement et renvoi.
        $violations = $this->validator->validate($customer);
        if($violations->count() > 0) {
            return $this->json($violations, 400);
        } else {
            $this->manager->persist($customer);
            $this->manager->flush(); 
            return $this->json($customer, 201, [], ['groups' => 'get:customer']); 
        }
    }

    /**
     * @Route("/api/customer/{id}", name="api_delete_customer", methods={"DELETE"})
     */
    public function delete($id)
    {
        $customer = $this->customerRepository->find($id);

        try {
            $this->manager->remove($customer);
            $this->manager->flush();
    
            return $this->json([
                'status' => 200,
                'message' => "Customer deleted successfully"
            ]);
            
        } catch(DBALException $error) {
            
            return $this->json([
                'status' => 400,
                'message' => "Error : Customer deleted successfully",
                'code' => $error->getPrevious()->getCode()
            ]);
        }

    }

    /**
     * @Route("/api/customer", name="api_update_customer", methods={"PUT"})
     */
    public function update(Request $request)
    {
        $json = json_decode($request->getContent()); 
        $customer = $this->customerRepository->find($json->id);

        // relation project si un ou plusieurs projects envoyé
        if(isset($json->projects_id)) {
            foreach ($json->projects_id as $project_id) {
                $project = $this->projectRepository->find($project_id);
                $customer->addProject($project);
            }
        }

        try {
            $customer->setUpdatedAt(new \DateTime())
                ->setName(isset($json->name) ? $json->name : $customer->getName())
                ->setSlug(isset($json->name) ? Str::slug($json->name) : $customer->getSlug())
                ->setLink(isset($json->link) ? $json->link : $customer->getLink())
                ->setDescription(isset($json->description) ? $json->description : $customer->getDescription());

            $this->manager->persist($customer);
            $this->manager->flush();
            return $this->json($customer, 201, [], ['groups' => 'get:customer']);

        } catch(NotEncodableValueException $error) {

            return $this->json([
                'status' => 400,
                'message' => $error->getMessage()
            ]);

        }
    }
}
