<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use \Symfony\Bundle\SecurityBundle\Security;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'phone', methods: ['GET'])]
    public function getAllPhones(PhoneRepository $phoneRepository, SerializerInterface $serializer, Request $request): JsonResponse 
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 2);

        $phones = $phoneRepository->findPhonesPaginated($page, $limit);
        $jsonPhone = $serializer->serialize($phones, 'json', ['groups' => 'getPhone']);
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }

    #[Route('/api/phones/{id}', name: 'detailPhone', methods: ['GET'])]   
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer)
    {                           
        $jsonPhone = $serializer->serialize($phone, 'json', ['groups' => 'getPhone']);
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }

}    