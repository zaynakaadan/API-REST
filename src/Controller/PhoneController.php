<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
//use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class PhoneController extends AbstractController
{
    /**
     * @Route("/api/phones", name="phone", methods={"GET"})
     *
     * @OA\Get(
     *     path="/api/phones",
     *     summary="Get list of phones",
     *     tags={"Phones"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of phones",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref=@Model(type=Phone::class, groups={"getPhone"}))
     *         )
     *     )
     * )
     */
    #[Route('/api/phones', name: 'phone', methods: ['GET'])]
    public function getAllPhones(PhoneRepository $phoneRepository, SerializerInterface $serializer, Request $request): JsonResponse 
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 2);

        $phones = $phoneRepository->findPhonesPaginated($page, $limit);
        $context = SerializationContext::create()->setGroups(['getPhone']);
        $jsonPhone = $serializer->serialize($phones, 'json', $context);
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }
    /**
     * @Route("/api/phones/{id}", name="detailPhone", methods={"GET"})
     *
     * @OA\Get(
     *     path="/api/phones/{id}",
     *     summary="Get details of a phone",
     *     tags={"Phones"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the phone",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the phone",
     *         @OA\JsonContent(ref=@Model(type=Phone::class, groups={"getPhone"}))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Phone not found"
     *     )
     * )
     */
    #[Route('/api/phones/{id}', name: 'detailPhone', methods: ['GET'])]   
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer)
    {       
        $context = SerializationContext::create()->setGroups(['getPhone']);                    
        $jsonPhone = $serializer->serialize($phone, 'json', $context);
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }

}    