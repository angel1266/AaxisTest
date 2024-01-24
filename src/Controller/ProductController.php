<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTimeImmutable;

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/list/product', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $productos = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->json($productos);
    }
    
    #[Route('/api/find/product/{id}', methods: ['GET'])]
    public function show(Request $request, int $id): Response
    {
        $bearer_token = $request->headers->get('Authorization');
        $producto = $this->entityManager->getRepository(Product::class)->find($id);

        if (!$producto) {
            return $this->json(['error' => 'El producto no existe'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($producto);
    }
    

    public function createProducts(Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Handle multiple product creations (array of data)
            if (isset($data['products'])) {
                $createdProducts = [];

                if (count($data['products']) < 1) {
                        throw new \Exception('No hay productos para registrar');
                }

                $listCheckSku = [];

                foreach ($data['products'] as $productData) {
                    // Check if SKU is already taken
                    $listCheckSku[] = $productData['sku'];
                    $product = $productRepository->findOneBy(['sku' => $productData['sku']]);
                    if ($product) {
                        throw new \Exception('El SKU '.$productData['sku'].' ya existe, valide e intente guardar nuevamente.');
                    }

                    // Check if product_name exists
                    if (!(array_key_exists('product_name', $productData)) || ($productData['product_name'] == "")) {
                        throw new \Exception('El nombre del producto es requerido.');
                    }
                    
                    $product = new Product();
                    $product->setSku($productData['sku']);
                    $product->setProductName($productData['product_name']);
                    $product->setDescription($productData['description'] ?? '');
                    $product->setCreatedAt(new DateTimeImmutable(date("Y-m-d h:i:s")));
                    $product->setUpdateAt(new DateTimeImmutable(date("Y-m-d h:i:s")));
                    $entityManager->persist($product);
                    
                }
                $listCheckSku = array_unique($listCheckSku);
                if(count($listCheckSku) < count($data['products'])){
                  throw new \Exception('Hay productos con sku repetidos.');
                }
                $entityManager->flush();
                 

                return new JsonResponse(
                    [
                        'message' => 'Productos creado exitosamente!',
                    ],
                    JsonResponse::HTTP_CREATED
                );
            }

            throw new \Exception('Los datos no son válidos.');
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }    
    
     #[Route('/api/update/products', methods: ['POST'])]
    public function updateProducts(Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Handle multiple product creations (array of data)
            if (isset($data['products'])) {
                $createdProducts = [];

                if (count($data['products']) < 1) {
                        throw new \Exception('No hay productos para actualizar');
                }

                $listCheckSku = [];

                foreach ($data['products'] as $productData) {
                    // Check if SKU is already taken
                    if(!isset($productData['id'])){
                       throw new \Exception('El id es requerido.');
                    }

                    $searchProduct = $this->entityManager->getRepository(Product::class)->findOneBy(["id" => $productData['id']]);
                    
                    if(!$searchProduct){
                       throw new \Exception('El id '.$productData['id'].' no existe.');
                    }
                    
                    $listCheckSku[] = ($productData['sku'] ?? $productData['id']); 
                   
                    if(isset($productData['sku'])){
                       if($productData['sku'] ==""){
                         throw new \Exception('El SKU es requerido.');
                       } 
                      $product = $productRepository->createQueryBuilder('p')
                      ->where('p.sku = :sku')
                       ->andWhere('p.id <> :id')
                       ->setParameter('sku', $productData['sku'])
                       ->setParameter('id', $productData['id'])
                       ->getQuery()
                       ->getResult();
                      
                        if ($product) {
                            throw new \Exception('El SKU '.$productData['sku'].' ya existe, valide e intente guardar nuevamente.');
                        }
                    }

                    if((isset($productData['product_name'])) && ($productData['product_name'] == "")){
                      throw new \Exception('El nombre es requerido.');
                    }
                    

                    $searchProduct->setSku($productData['sku'] ?? $searchProduct->getSku());
                    $searchProduct->setProductName($productData['product_name'] ?? $searchProduct->getProductName());
                    $searchProduct->setDescription($productData['description'] ?? $searchProduct->getDescription());
                    $searchProduct->setUpdateAt(new DateTimeImmutable(date("Y-m-d h:i:s")));
                    $entityManager->persist($searchProduct);
                    
                }
                $listCheckSku = array_unique($listCheckSku);
                if(count($listCheckSku) < count($data['products'])){
                  throw new \Exception('Hay productos con sku repetidos.');
                }
                $entityManager->flush();
                 

                return new JsonResponse(
                    [
                        'message' => 'Productos actualizados exitosamente!',
                    ],
                    JsonResponse::HTTP_CREATED
                );
            }

            throw new \Exception('Los datos no son válidos.');
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }    

    function decodeJWTPayloadOnly($token){
        $tks = explode('.', $token);
        if (count($tks) != 3) {
            return null;
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        $input=$bodyb64;
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        $input = (base64_decode(strtr($input, '-_', '+/')));

        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            $obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            $max_int_length = strlen((string) PHP_INT_MAX) - 1;
            $json_without_bigints = preg_replace('/:\s*(-?\d{'.$max_int_length.',})/', ': "$1"', $input);
            $obj = json_decode($json_without_bigints);
        }
        return $obj->email;
}
}