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

class PhoneController extends AbstractController
{
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

    #[Route('/api/phones/{id}', name: 'detailPhone', methods: ['GET'])]   
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer)
    {       
        $context = SerializationContext::create()->setGroups(['getPhone']);                    
        $jsonPhone = $serializer->serialize($phone, 'json', $context);
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }

}    