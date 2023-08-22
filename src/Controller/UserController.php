<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Loader\Configurator\validator;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'user', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $userList = $userRepository->findAll();

        $jsonUserList = $serializer->serialize($userList, 'json', ['groups' => 'getUser']);
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);                   
    }


    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getDetailUser(User $user, SerializerInterface $serializer)
    {            
            $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUser']);
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);                                      
    }

    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse    
    {       $em->remove($user);     
            $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);                                      
    }

    #[Route('/api/users', name: 'createUser', methods: ['POST'])]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ClientRepository $clientRepository, ValidatorInterface $validator)
    {    
           $user = $serializer->deserialize($request->getContent(), User::class, 'json') ;           

            // On vérifie les erreurs
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

            // Récupération de l'ensemble des données envoyées sous forme de tableau
            $content = $request->toArray();
            // Récupération de l'idClient. S'il n'est pas défini, alors on met -1 par défaut
            $idClient = $content['idClient'] ?? -1;

            // On cherche le client qui correspond et on l'assigne au user
            // Si "find" ne trouve pas le client, alors null sera retourné.
            $user->setClient($clientRepository->find($idClient));

            $em->persist($user);
            $em->flush();

            $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUser']);

            $location = $urlGenerator->generate('detailUser', ['id' => $user->getId(), UrlGeneratorInterface::ABSOLUTE_PATH]);

            return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);                                      
    }

    #[Route('/api/users/{id}', name: 'updateUser', methods: ['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ClientRepository $clientRepository, User $currentUser)
    { 

              $updatedUser = $serializer->deserialize($request->getContent(), User::class, 'json',
               
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);          

            // Récupération de l'ensemble des données envoyées sous forme de tableau
            $content = $request->toArray();
            // Récupération de l'idClient. S'il n'est pas défini, alors on met -1 par défaut
            $idClient = $content['idClient'] ?? -1;

            // On cherche le client qui correspond et on l'assigne au user
            // Si "find" ne trouve pas le client, alors null sera retourné.
            $updatedUser->setClient($clientRepository->find($idClient));

            $em->persist($updatedUser);
            $em->flush();
                        
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);                                      
    }

}
