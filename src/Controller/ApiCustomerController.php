<?php
namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\DBAL\DBALException;
use Illuminate\Support\Str;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ApiCustomerController extends AbstractController
{
    private $customerRepository;
    private $manager;

    public function __construct(
        CustomerRepository $customerRepository,
        EntityManagerInterface $manager
    )
    {
        $this->customerRepository = $customerRepository;
        $this->manager = $manager;
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
        $json = $request->getContent(); 

        try {
            $customer = $serializer->deserialize($json, Customer::class, 'json');
    
            // Set Timestamp (TODO: Config this in SQL|Database|Entities)
            $customer->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->setSlug(Str::slug($customer->getName(), '-'));

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

        try {
            $customer->setUpdatedAt(new \DateTime())
                ->setName($json->name ? $json->name : $customer->name)
                ->setSlug($json->name ? Str::slug($json->name) : $customer->slug)
                ->setLink($json->link ? $json->link : $customer->slug)
                ->setDescription($json->description ? $json->description : $customer->description);

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
