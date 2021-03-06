<?php

namespace BookRater\RaterBundle\Controller\Api;

use BookRater\RaterBundle\Entity\Message;
use BookRater\RaterBundle\Entity\Review;
use BookRater\RaterBundle\Entity\User;
use BookRater\RaterBundle\Pagination\PaginationFactory;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserController extends BaseApiController
{

    private const GROUPS = ['admin'];

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(PaginationFactory $paginationFactory, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($paginationFactory);

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param $identifier
     *
     * @param Request $request
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Rest\Get("/users/{identifier}", name="api_users_show")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @SWG\Get(
     *   tags={"Users"},
     *   summary="Retrieve a single user",
     *   description="Retrieves a representation of the user resource queried for.
                      <strong>Requires admin authorization.</strong>",
     *   produces={"application/hal+json", "application/hal+xml"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="identifier",
     *     type="string",
     *     description="A unique identifier for the user (id, email or username).",
     *   ),
     *   @SWG\Parameter(in="query", name="format", type="string", enum={"xml", "json"}),
     *   @SWG\Response(
     *     response=200,
     *     description="A representation of the user resource queried for.",
     *     @SWG\Schema(@Model(type=User::class, groups={"admin"},),),
     *   ),
     *   @SWG\Response(
     *     response=401,
     *     description="An 'Unauthorized' error response, if the user does not have admin authorization.",
     *   ),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the resource does not exist.",),
     * )
     *
     */
    public function showAction($identifier, Request $request)
    {
        $user = $this->getUserRepository()->findByIdentifier($identifier);
        if (!$user) {
            $this->throwUserNotFoundException($identifier);
        }

        $response = $this->createApiResponseUsingRequestedFormat($user, $request);
        $this->setLocationHeader($response, 'api_users_show', [
            'identifier' => $user->getId(),
        ]);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Rest\Get("/users", name="api_users_collection")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @SWG\Get(
     *   tags={"Users"},
     *   summary="List all users",
     *   description="Retrieves a collection of user resources.
                      <strong>Requires admin authorization.</strong>",
     *   produces={"application/hal+json", "application/hal+xml"},
     *   @SWG\Parameter(in="query", name="format", type="string", enum={"xml", "json"}),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="An optional page number for paginated results."
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="A collection of all user resources.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(
     *         property="items",
     *         type="array",
     *         @Model(type=User::class, groups={"admin"},),
     *       ),
     *       @SWG\Property(property="total", type="integer", description="The total number of items in the pagination.",),
     *       @SWG\Property(property="count", type="integer", description="The total number of items in the current page.",),
     *       @SWG\Property(
     *         property="_links",
     *         @SWG\Property(property="self", type="string", description="A relative url to the current page of the pagination.",),
     *         @SWG\Property(property="first", type="string", description="A relative url to the first page of the pagination.",),
     *         @SWG\Property(property="last", type="string", description="A relative url to the current page of the pagination.",),
     *         @SWG\Property(property="next", type="string", description="A relative url to the current page of the pagination.",),
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(
     *     response=401,
     *     description="An 'Unauthorized' error response, if the user does not have admin authorization.",
     *   ),
     * )
     */
    public function listAction(Request $request)
    {
        $qb = $this->getUserRepository()
            ->findAllQueryBuilder();
        $paginatedCollection = $this->paginationFactory
            ->createCollection($qb, $request, 'api_users_collection');

        return $this->createApiPaginationResponse($paginatedCollection, $request);
    }

    /**
     * @param int $id
     * @return Response
     *
     * @Rest\Delete("/users/{id}")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @SWG\Delete(
     *   tags={"Users"},
     *   summary="Remove a user.",
     *   description="Removes a user resource from the system.
                      <strong>Requires admin authorization.</strong>",
     *   produces={"text/html"},
     *   @SWG\Parameter(in="path", name="id", type="integer", description="The unique identifier of the book."),
     *   @SWG\Response(response=204, description="Indicates that the resource is not present on the system.",),
     * )
     */
    public function deleteAction(int $id)
    {
        $user = $this->getUserRepository()->find($id);
        if ($user) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->createApiResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param $identifier
     * @param Request $request
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @Rest\Get("/users/{identifier}/reviews", name="api_users_reviews_list")
     *
     * @SWG\Get(
     *   tags={"Users"},
     *   summary="Retrieves a user's reviews",
     *   description="Retrieves a collection of all reviews created by a user.",
     *   produces={"application/hal+json", "application/hal+xml"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="identifier",
     *     type="string",
     *     description="A unique identifier for the user (id, email or username).",
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="An optional page number for paginated results."
     *   ),
     *   @SWG\Parameter(in="query", name="format", type="string", enum={"xml", "json"}),
     *   @SWG\Response(
     *     response=200,
     *     description="A collection of all reviews created by the user.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="items", type="array", @Model(type=Review::class, groups={"admin"},),),
     *       @SWG\Property(property="total", type="integer", description="The total number of items in the pagination.",),
     *       @SWG\Property(property="count", type="integer", description="The total number of items in the current page.",),
     *       @SWG\Property(
     *         property="_links",
     *         @SWG\Property(property="self", type="string", description="A relative url to the current page of the pagination.",),
     *         @SWG\Property(property="first", type="string", description="A relative url to the first page of the pagination.",),
     *         @SWG\Property(property="last", type="string", description="A relative url to the current page of the pagination.",),
     *         @SWG\Property(property="next", type="string", description="A relative url to the current page of the pagination.",),
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the user resource does not exist.",),
     * )
     */
    public function reviewsListAction($identifier, Request $request)
    {
        $user = $this->getUserRepository()->findByIdentifier($identifier);
        if (!$user) {
            $this->throwUserNotFoundException($identifier);
        }

        $qb = $this->getReviewRepository()
            ->createQueryBuilderForUser($user);
        $paginatedCollection = $this->paginationFactory->createCollection(
            $qb,
            $request,
            'api_users_reviews_list',
            [
                'identifier' => $user->getId(),
            ]
        );

        return $this->createApiPaginationResponse($paginatedCollection, $request);
    }

    /**
     * @param User $user
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/users/{id}/messages", name="api_users_messages_list")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @SWG\Get(
     *   tags={"Users"},
     *   summary="Retrieves a user's messages",
     *   description="Retrieves a collection of all messages created by a user.
                      <strong>Requires admin authorization.</strong>",
     *   produces={"application/hal+json", "application/hal+xml"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="The unique id of a user.",
     *   ),
     *   @SWG\Parameter(in="query", name="format", type="string", enum={"xml", "json"}),
     *   @SWG\Response(
     *     response=200,
     *     description="A collection of all messages created by the user.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="items", type="array", @Model(type=Message::class, groups={"admin"},),),
     *       @SWG\Property(property="total", type="integer", description="The total number of items in the pagination.",),
     *       @SWG\Property(property="count", type="integer", description="The total number of items in the current page.",),
     *       @SWG\Property(
     *         property="_links",
     *         @SWG\Property(property="self", type="string", description="A relative url to the current page of the pagination.",),
     *         @SWG\Property(property="first", type="string", description="A relative url to the first page of the pagination.",),
     *         @SWG\Property(property="last", type="string", description="A relative url to the current page of the pagination.",),
     *         @SWG\Property(property="next", type="string", description="A relative url to the current page of the pagination.",),
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(
     *     response=401,
     *     description="An 'Unauthorized' error response, if the user does not have admin authorization.",
     *   ),
     *   @SWG\Response(response=404, description="A 'Not Found' error response, if the user resource does not exist.",),
     * )
     */
    public function messagesListAction(User $user, Request $request)
    {
        $qb = $this->getMessageRepository()
            ->createQueryBuilderForUser($user);
        $paginatedCollection = $this->paginationFactory->createCollection(
            $qb,
            $request,
            'api_users_messages_list',
            [
                'id' => $user->getId(),
            ]
        );

        return $this->createApiPaginationResponse($paginatedCollection, $request);
    }

    private function throwUserNotFoundException($identifier)
    {
        throw $this->createNotFoundException(sprintf('No user found with identifier: "%s"', $identifier));
    }

    protected function getGroups()
    {
        return self::GROUPS;
    }

}
