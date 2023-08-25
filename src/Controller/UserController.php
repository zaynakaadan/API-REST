<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use JMS\Serializer\Serializer;
use App\Repository\UserRepository;


use App\Service\VersioningService;
use App\Repository\ClientRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use \Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\DependencyInjection\Loader\Configurator\validator;
use Hateoas\Serializer\SerializerInterface as SerializerSerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
//use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;


class UserController extends AbstractController
{
    /**
     * 
     * Cette méthode permet de récupérer l'ensemble des users.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des users",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Users")
     *
     * @param UserRepository $UserRepository
     * @param SerializerInterface $serializer
     * @param Security $security
     * @param Request $request
     * @param TagAwareCacheInterface $cache
     * @return JsonResponse
     */
    #[Route('/api/users', name: 'user', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer, Security $security,Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
    
        // Get the currently authenticated user
        /** @var UserInterface $currentUser */
        $currentUser = $security->getUser();
        
        // If the user is not a client, return an access denied response
        if (!$currentUser instanceof Client) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à consulter cette liste d\'utilisateurs.');
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        
        // Use caching for the user list
        $cacheKey = 'getAllUsers' . $currentUser->getId() . '_' . $page; 

        $cachedUserList = $cache->get($cacheKey, function (ItemInterface $item) use ($userRepository, $currentUser, $page, $limit, $serializer) {            
            echo("L'ELEMENT N'EST PAS ENCORE EN CACHE !\n");
            
        $item->tag("usersCache");
        
        // Retrieve the users associated with the current client
        $userList = $userRepository->findUsersByClientWithPagination($currentUser, $page, $limit);
       
        // Serialize the user list        
        $context = SerializationContext::create()->setGroups(['getUsers']);
      
        $jsonUserList = $serializer->serialize($userList, 'json', $context);
        
        // Cache the serialized user list for a certain period of time
        //$item->expiresAfter(3600); // Cache for 1 hour

            return $jsonUserList;
        });
        
        return new JsonResponse($cachedUserList, Response::HTTP_OK, [], true);

    }
    
    /**
     *
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get details of a user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returns the details of the user",
     *         @OA\JsonContent(
     *            type="object",
     *            @OA\Property(property="id", type="integer"),
     *            @OA\Property(property="username", type="string"),
     *            @OA\Property(property="firstname", type="string"),
     *            @OA\Property(property="lastname", type="string"),
     *            @OA\Property(property="email", type="string"),
     *            @OA\Property(property="createdAt", type="string", format="date-time")
     *            
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]   
    public function getDetailUser(User $requestedUser, SerializerInterface $serializer, Security $security, VersioningService $versioningService): JsonResponse
    {   
        // Get the currently authenticated user
        $currentUser = $security->getUser();

        // Make sure the $currentUser is a Client and $requestedUser is not null
        if (!$currentUser instanceof Client || $requestedUser === null) {
         throw new AccessDeniedHttpException('Vous n\'êtes pas autorisé à voir cet utilisateur.');
        }

        // Check if the requested user is associated with the current client
        if ($requestedUser->getClient() !== $currentUser) {
            throw new AccessDeniedHttpException('Vous n\'êtes pas autorisé à voir cet utilisateur.');
        }
        $version = $versioningService->getVersion();    
        $context = SerializationContext::create()->setGroups(['getUsers']);
        $context->setVersion($version);
        $jsonUser = $serializer->serialize($requestedUser, 'json', $context);
       
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    /**
     *
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $requestedUser, EntityManagerInterface $em, Security $security, TagAwareCacheInterface $cachePool, Request $request, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse    
    {      
        
        $currentUser = $security->getUser();

    // Check if the $currentUser is a Client and $requestedUser is not null to delete the user
    if (!$currentUser instanceof Client || $requestedUser === null) {
        throw new AccessDeniedHttpException('Vous n\'êtes pas autorisé de supprimer cet utilisateur.');
    }
    // Check if the requested user is associated with the current client
    if ($requestedUser->getClient() !== $currentUser) {
        throw new AccessDeniedHttpException('Vous n\'êtes pas autorisé de supprimer cet utilisateur.');
    }    

    $page = $request->get('page', 1); // Get the page from the request
    $cacheKey = 'getAllUsers' . $currentUser->getId() . '_' . $page; // Construct the cache key

    // Delete the cached data for the specific page
    $cachePool->delete($cacheKey);

    $em->remove($requestedUser);
    $em->flush();

    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     *
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         description="User object to be created",
     *         required=true,
     *         @OA\JsonContent(ref=@Model(type=User::class, groups={"createUser"}))
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             ref=@Model(type=User::class, groups={"getUsers"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/api/users', name: 'createUser', methods: ['POST'])]    
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator,  ValidatorInterface $validator, Security $security)
    {    
        // Deserialize the user data from the request content
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        // Check if the current user is a client
        $currentUser = $security->getUser();
        if (!$currentUser instanceof Client) {
            throw new AccessDeniedHttpException('Vous n\'êtes pas autorisé à créer un utilisateur.');
        }   

        // Set the current client as the user's client
        $user->setClient($currentUser);
        // Set the createdAt field
        $user->setCreatedAt(new \DateTimeImmutable());

        // Validate the user data
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Persist and flush the user entity
        $em->persist($user);
        $em->flush();

        // Check if the user has a valid ID before generating the URL
        if ($user->getId()) {
        // Serialize the user data and return the response
        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        
        return new JsonResponse($jsonUser, JsonResponse::HTTP_CREATED, ["Location" => $location], true);                           
        } else {
            return new JsonResponse('Error creating user', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update user details",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Updated user object",
     *         required=true,
     *         @OA\JsonContent(ref=@Model(type=User::class, groups={"updateUser"}))
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */   
    #[Route('/api/users/{id}', name: 'updateUser', methods: ['PUT'])]
    public function updateUser(User $requestedUser, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, User $currentUser, Security $security, ValidatorInterface $validator)        
    {   
        // Ensure the current user is a client
        $currentUser = $security->getUser();
        if (!$currentUser instanceof Client) {
            throw new AccessDeniedHttpException('Vous n\'êtes pas autorisé à mettre à jour cet utilisateur.');
        }

        // Check if the requested user is associated with the current client
        if ($requestedUser->getClient() !== $currentUser) {
            throw new AccessDeniedHttpException('Vous n\'êtes pas autorisé à mettre à jour cet utilisateur.');
        }

        // Deserialize the updated user data from the request content
        $updatedUser = $serializer->deserialize($request->getContent(), User::class, 'json');        
         
        $requestedUser->setFirstname($updatedUser->getFirstname());
        $requestedUser->setLastname($updatedUser->getLastname()); 
        
        // Validate the updated user data
        $errors = $validator->validate($updatedUser);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Persist and flush the updated user entity        
        $em->flush();
                    
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}